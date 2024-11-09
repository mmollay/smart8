<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');
use \Mailjet\Resources;

class EmailService
{
    private $db;
    private $mailjet;
    private $uploadBasePath;

    public function __construct($db, $apiKey, $apiSecret, $uploadBasePath)
    {
        $this->db = $db;
        $this->mailjet = new \Mailjet\Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);
        $this->uploadBasePath = $uploadBasePath;
    }

    /**
     * Gemeinsame Funktion zum Abrufen von Anhängen
     */
    public function getAttachments($contentId)
    {
        $attachments = [];
        $directory = $this->uploadBasePath . $contentId . '/';

        if (!is_dir($directory)) {
            return $attachments;
        }

        $files = scandir($directory);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $full_path = $directory . $file;
                if (is_file($full_path)) {
                    $attachments[] = [
                        'ContentType' => mime_content_type($full_path),
                        'Filename' => $file,
                        'Base64Content' => base64_encode(file_get_contents($full_path))
                    ];
                }
            }
        }

        return $attachments;
    }

    /**
     * Gemeinsame Funktion zum Erstellen der E-Mail-Struktur
     */
    private function createEmailStructure($sender, $recipient, $subject, $message, $jobId, $attachments = [], $isTest = false)
    {
        return [
            'From' => [
                'Email' => $sender['email'],
                'Name' => $sender['name']
            ],
            'To' => [
                [
                    'Email' => $recipient['email'],
                    'Name' => $recipient['name']
                ]
            ],
            'Subject' => $isTest ? '[TEST] ' . $subject : $subject,
            'TextPart' => $message,
            'HTMLPart' => nl2br($message),
            'CustomID' => ($isTest ? "test_" : "") . "email_job_" . $jobId,
            'Attachments' => $attachments
        ];
    }

    /**
     * Gemeinsame Funktion zum Protokollieren des E-Mail-Status
     */
    private function logEmailStatus($jobId, $status, $response)
    {
        $stmt = $this->db->prepare("UPDATE email_jobs SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $jobId);
        $stmt->execute();

        $stmt = $this->db->prepare("INSERT INTO email_logs (job_id, status, response) VALUES (?, ?, ?)");
        $logResponse = json_encode($response);
        $stmt->bind_param("iss", $jobId, $status, $logResponse);
        $stmt->execute();
    }

    /**
     * Funktion zum Senden einer einzelnen E-Mail
     */
    /**
     * Funktion zum Senden einer einzelnen E-Mail
     */
    public function sendSingleEmail($contentId, $sender, $recipient, $subject, $message, $jobId, $isTest = false)
    {
        $attachments = $this->getAttachments($contentId);

        // Platzhalter ersetzen
        $placeholders = [
            '{{anrede}}' => $recipient['gender'] == 'male' ? 'Sehr geehrter Herr' : 'Sehr geehrte Frau',
            '{{titel}}' => $recipient['title'],
            '{{vorname}}' => $recipient['first_name'],
            '{{nachname}}' => $recipient['last_name'],
            '{{firma}}' => $recipient['company'],
            '{{email}}' => $recipient['email'],
            '{{datum}}' => date('d.m.Y'),
            '{{uhrzeit}}' => date('H:i')
        ];

        foreach ($placeholders as $placeholder => $value) {
            $subject = str_replace($placeholder, $value, $subject);
            $message = str_replace($placeholder, $value, $message);
        }

        $emailData = $this->createEmailStructure(
            $sender,
            $recipient,
            $subject,
            $message,
            $jobId,
            $attachments,
            $isTest
        );

        $response = $this->mailjet->post(Resources::$Email, ['body' => ['Messages' => [$emailData]]]);

        if ($response->success()) {
            $this->logEmailStatus($jobId, 'success', $response->getBody());
            return ['success' => true, 'response' => $response->getBody()];
        } else {
            $this->logEmailStatus($jobId, 'failed', $response->getBody());
            return ['success' => false, 'error' => $response->getBody()];
        }
    }
    /**
     * Funktion zum Vorbereiten eines Test-Empfängers
     */
    public function prepareTestRecipient($testEmail, $senderId)
    {
        $stmt = $this->db->prepare("SELECT id FROM recipients WHERE email = ?");
        $stmt->bind_param("s", $testEmail);
        $stmt->execute();
        $recipient = $stmt->get_result()->fetch_assoc();

        if (!$recipient) {
            $stmt = $this->db->prepare("
                INSERT INTO recipients (email, first_name, last_name, comment)
                VALUES (?, 'Test', 'Empfänger', 'Automatisch erstellt für Test-Mails')
            ");
            $stmt->bind_param("s", $testEmail);
            $stmt->execute();
            $recipientId = $this->db->insert_id;

            // Verknüpfe mit Sender in test_recipients
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO test_recipients (sender_id, recipient_id)
                VALUES (?, ?)
            ");
            $stmt->bind_param("ii", $senderId, $recipientId);
            $stmt->execute();

            return $recipientId;
        }

        return $recipient['id'];
    }

    /**
     * Funktion zum Erstellen von E-Mail-Jobs für einen Newsletter
     */
    public function createNewsletterJobs($contentId)
    {
        $stmt = $this->db->prepare("
            INSERT INTO email_jobs (content_id, sender_id, recipient_id, status)
            SELECT DISTINCT ec.id, ec.sender_id, rg.recipient_id, 'pending'
            FROM email_contents ec
            JOIN email_content_groups ecg ON ec.id = ecg.email_content_id
            JOIN recipient_group rg ON ecg.group_id = rg.group_id
            WHERE ec.id = ?
            AND NOT EXISTS (
                SELECT 1 FROM email_jobs ej
                WHERE ej.content_id = ec.id
                AND ej.sender_id = ec.sender_id
                AND ej.recipient_id = rg.recipient_id
            )
        ");

        $stmt->bind_param("i", $contentId);
        $stmt->execute();
        return $stmt->affected_rows;
    }

    /**
     * Funktion zum Aktualisieren des Newsletter-Status
     */
    public function updateNewsletterStatus($contentId, $status)
    {
        $stmt = $this->db->prepare("UPDATE email_contents SET send_status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $contentId);
        return $stmt->execute();
    }
}