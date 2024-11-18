<?php
/**
 * QueueProcessor Klasse für die Verarbeitung von Newsletter-Versand-Queues
 */
class QueueProcessor
{
    private $db;
    private $processId;
    private $emailService;
    private $placeholderService;
    private $batchSize = 50;  // Emails pro Durchlauf

    /**
     * Konstruktor
     * 
     * @param mysqli $db Datenbankverbindung
     * @param int $processId Prozess-ID für parallele Verarbeitung
     */
    public function __construct($db, $processId)
    {
        $this->db = $db;
        $this->processId = $processId;
        $this->emailService = new EmailService($db, $GLOBALS['apiKey'], $GLOBALS['apiSecret'], $GLOBALS['uploadBasePath']);
        $this->placeholderService = PlaceholderService::getInstance(); // Korrigiert: Verwendung der getInstance Methode
    }
    /**
     * Verarbeitet eine einzelne Queue
     * 
     * @param int $contentId Die ID des zu versendenden Newsletters
     * @return bool True wenn erfolgreich, False wenn keine E-Mails zu versenden
     */
    public function processNewsletter($contentId)
    {
        try {
            // Erstelle Queue für den Newsletter falls noch nicht vorhanden
            $queueManager = new EmailQueueManager($this->db);
            $queueManager->createQueue($contentId);

            // Hole verfügbare Queue
            $queue = $this->getNextQueue();
            if (!$queue) {
                return false;
            }

            // Verarbeite die Queue
            $this->processQueue($queue);
            return true;

        } catch (Exception $e) {
            if (isset($queue)) {
                $this->markQueueAsFailed($queue['id'], $e->getMessage());
            }
            throw $e;
        }
    }

    private function getNextQueue()
    {
        $this->db->begin_transaction();

        try {
            $stmt = $this->db->prepare("
                SELECT id, content_id 
                FROM email_queue 
                WHERE status = 'pending'
                LIMIT 1 
                FOR UPDATE
            ");
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result) {
                $stmt = $this->db->prepare("
                    UPDATE email_queue 
                    SET status = 'processing', 
                        started_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $result['id']);
                $stmt->execute();
            }

            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    private function processQueue($queue)
    {
        do {
            $items = $this->getNextBatch($queue['id']);
            if (empty($items)) {
                break;
            }

            foreach ($items as $item) {
                try {
                    $this->processEmail($item);
                    $this->updateQueueProgress($queue['id'], true);
                } catch (Exception $e) {
                    $this->markItemAsFailed($item['id'], $e->getMessage());
                    $this->updateQueueProgress($queue['id'], false);
                }

                usleep(100000); // 100ms Pause zwischen E-Mails
            }

        } while (!empty($items));

        $this->completeQueue($queue['id']);
    }

    private function getNextBatch($queueId)
    {
        $stmt = $this->db->prepare("
            SELECT qi.*, ej.*, ec.subject, ec.message,
                   s.email as sender_email, s.first_name as sender_first_name,
                   s.last_name as sender_last_name, r.email as recipient_email,
                   r.first_name as recipient_first_name, r.last_name as recipient_last_name,
                   r.company as recipient_company, r.gender as recipient_gender,
                   r.title as recipient_title
            FROM email_queue_items qi
            JOIN email_jobs ej ON qi.email_job_id = ej.id
            JOIN email_contents ec ON ej.content_id = ec.id
            JOIN senders s ON ej.sender_id = s.id
            JOIN recipients r ON ej.recipient_id = r.id
            WHERE qi.queue_id = ? 
            AND qi.status = 'pending'
            LIMIT ?
        ");
        $stmt->bind_param("ii", $queueId, $this->batchSize);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function processEmail($item)
    {
        $this->markItemAsProcessing($item['id']);

        // Erstelle Platzhalter
        $placeholders = [
            'first_name' => $item['recipient_first_name'],
            'last_name' => $item['recipient_last_name'],
            'email' => $item['recipient_email'],
            'company' => $item['recipient_company'],
            'gender' => $item['recipient_gender'],
            'title' => $item['recipient_title']
        ];

        // Ersetze Platzhalter
        $subject = $this->placeholderService->replacePlaceholders($item['subject'], $placeholders);
        $message = $this->placeholderService->replacePlaceholders($item['message'], $placeholders);

        // Füge Abmelde-Link hinzu
        $unsubscribeUrl = $this->generateUnsubscribeUrl($item);
        $message .= $this->generateUnsubscribeFooter($unsubscribeUrl);

        $sender = [
            'email' => $item['sender_email'],
            'name' => trim("{$item['sender_first_name']} {$item['sender_last_name']}")
        ];

        $recipient = [
            'email' => $item['recipient_email'],
            'name' => trim("{$item['recipient_first_name']} {$item['recipient_last_name']}")
        ];

        $result = $this->emailService->sendSingleEmail(
            $item['content_id'],
            $sender,
            $recipient,
            $subject,
            $message,
            $item['email_job_id']
        );

        if ($result['success']) {
            $this->markItemAsCompleted($item['id'], $result['message_id']);
        } else {
            throw new Exception($result['error'] ?? 'Unbekannter Fehler');
        }
    }

    private function generateUnsubscribeUrl($item)
    {
        return sprintf(
            "https://%s/unsubscribe.php?email=%s&id=%s",
            $_SERVER['HTTP_HOST'] ?? 'localhost',
            urlencode($item['recipient_email']),
            $item['email_job_id']
        );
    }

    private function generateUnsubscribeFooter($unsubscribeUrl)
    {
        return "
            <br><br><hr>
            <p style='font-size: 12px; color: #666;'>
                Falls Sie keine weiteren E-Mails erhalten möchten, 
                können Sie sich hier <a href='{$unsubscribeUrl}'>abmelden</a>.
            </p>
        ";
    }

    private function markItemAsProcessing($itemId)
    {
        $stmt = $this->db->prepare("
            UPDATE email_queue_items 
            SET status = 'processing', 
                attempts = attempts + 1
            WHERE id = ?
        ");
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
    }

    private function markItemAsCompleted($itemId, $messageId)
    {
        try {
            $this->db->begin_transaction();

            $stmt = $this->db->prepare("
                UPDATE email_queue_items 
                SET status = 'sent',
                    processed_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("i", $itemId);
            $stmt->execute();

            $stmt = $this->db->prepare("
                UPDATE email_jobs ej
                JOIN email_queue_items qi ON ej.id = qi.email_job_id
                SET ej.status = 'send',
                    ej.sent_at = NOW(),
                    ej.message_id = ?
                WHERE qi.id = ?
            ");
            $stmt->bind_param("si", $messageId, $itemId);
            $stmt->execute();

            $stmt = $this->db->prepare("
                INSERT INTO status_log (event, timestamp, message_id, email)
                SELECT 'send', NOW(), ?, r.email
                FROM email_queue_items qi
                JOIN email_jobs ej ON qi.email_job_id = ej.id
                JOIN recipients r ON ej.recipient_id = r.id
                WHERE qi.id = ?
            ");
            $stmt->bind_param("si", $messageId, $itemId);
            $stmt->execute();

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    private function markItemAsFailed($itemId, $error)
    {
        $stmt = $this->db->prepare("
            UPDATE email_queue_items 
            SET status = CASE 
                    WHEN attempts >= 3 THEN 'failed'
                    ELSE 'pending'
                END,
                error_message = ?
            WHERE id = ?
        ");
        $stmt->bind_param("si", $error, $itemId);
        $stmt->execute();

        if ($this->db->affected_rows > 0) {
            $stmt = $this->db->prepare("
                UPDATE email_jobs ej
                JOIN email_queue_items qi ON ej.id = qi.email_job_id
                SET ej.status = 'failed',
                    ej.error_message = ?
                WHERE qi.id = ? AND qi.attempts >= 3
            ");
            $stmt->bind_param("si", $error, $itemId);
            $stmt->execute();
        }
    }

    private function updateQueueProgress($queueId, $success = true)
    {
        $field = $success ? 'processed_emails' : 'failed_emails';
        $stmt = $this->db->prepare("
            UPDATE email_queue 
            SET $field = $field + 1
            WHERE id = ?
        ");
        $stmt->bind_param("i", $queueId);
        $stmt->execute();
    }

    private function completeQueue($queueId)
    {
        $stmt = $this->db->prepare("
            UPDATE email_queue 
            SET status = 'completed',
                completed_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("i", $queueId);
        $stmt->execute();
    }

    private function markQueueAsFailed($queueId, $error)
    {
        $stmt = $this->db->prepare("
            UPDATE email_queue 
            SET status = 'failed',
                error_message = ?,
                completed_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("si", $error, $queueId);
        $stmt->execute();
    }
}