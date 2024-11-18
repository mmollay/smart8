<?php
class EmailQueueManager
{
    private $db;
    private $batchSize = 1000;      // Emails pro Batch
    private $maxProcesses = 4;       // Maximale parallele Prozesse
    private $maxAttempts = 3;        // Maximale Versuchte pro Email

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Erstellt Queue-Einträge für einen Newsletter
     * @param int $contentId ID des Newsletters
     * @return bool true bei Erfolg
     * @throws Exception bei Fehlern
     */
    public function createQueue($contentId)
    {
        try {
            $this->db->begin_transaction();

            // Lösche eventuell vorhandene alte Queue-Einträge
            $stmt = $this->db->prepare("
                DELETE qi FROM email_queue_items qi
                JOIN email_queue q ON qi.queue_id = q.id
                WHERE q.content_id = ?
            ");
            $stmt->bind_param("i", $contentId);
            $stmt->execute();

            $stmt = $this->db->prepare("
                DELETE FROM email_queue 
                WHERE content_id = ?
            ");
            $stmt->bind_param("i", $contentId);
            $stmt->execute();

            // Zähle verfügbare Email-Jobs
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM email_jobs 
                WHERE content_id = ? AND status = 'pending'
            ");
            $stmt->bind_param("i", $contentId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $totalEmails = $result['total'];

            if ($totalEmails === 0) {
                throw new Exception("Keine ausstehenden E-Mails gefunden");
            }

            // Erstelle eine einzelne Queue für alle Jobs
            $stmt = $this->db->prepare("
                INSERT INTO email_queue (content_id, batch_id, total_emails)
                VALUES (?, 0, ?)
            ");
            $stmt->bind_param("ii", $contentId, $totalEmails);
            $stmt->execute();
            $queueId = $this->db->insert_id;

            // Füge alle Email-Jobs zur Queue hinzu
            $stmt = $this->db->prepare("
                INSERT INTO email_queue_items (queue_id, email_job_id)
                SELECT ?, id
                FROM email_jobs
                WHERE content_id = ? 
                AND status = 'pending'
            ");
            $stmt->bind_param("ii", $queueId, $contentId);
            $stmt->execute();

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Prüft den Status der Queue
     * @param int $contentId ID des Newsletters
     * @return array Status-Informationen
     */
    public function checkStatus($contentId)
    {
        $stmt = $this->db->prepare("
            WITH QueueStats AS (
                SELECT 
                    q.id,
                    COUNT(qi.id) as item_count,
                    SUM(CASE WHEN qi.status = 'sent' THEN 1 ELSE 0 END) as sent_count,
                    SUM(CASE WHEN qi.status = 'failed' THEN 1 ELSE 0 END) as failed_count
                FROM email_queue q
                LEFT JOIN email_queue_items qi ON q.id = qi.queue_id
                WHERE q.content_id = ?
                GROUP BY q.id
            )
            SELECT 
                COUNT(DISTINCT q.id) as total_queues,
                SUM(CASE WHEN q.status = 'completed' THEN 1 ELSE 0 END) as completed_queues,
                SUM(CASE WHEN q.status = 'failed' THEN 1 ELSE 0 END) as failed_queues,
                SUM(qs.item_count) as total_emails,
                SUM(qs.sent_count) as processed_emails,
                SUM(qs.failed_count) as failed_emails
            FROM email_queue q
            JOIN QueueStats qs ON q.id = qs.id
            WHERE q.content_id = ?
        ");
        $stmt->bind_param("ii", $contentId, $contentId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $result['is_completed'] = ($result['completed_queues'] == $result['total_queues']);
        $result['percentage'] = $result['total_emails'] > 0
            ? round(($result['processed_emails'] / $result['total_emails']) * 100, 2)
            : 0;

        return $result;
    }

    /**
     * Startet die Verarbeitung der Queue
     */
    public function startProcessing()
    {
        for ($i = 0; $i < $this->maxProcesses; $i++) {
            $this->startProcessor($i);
        }
    }

    /**
     * Startet einen einzelnen Processor
     */
    private function startProcessor($processId)
    {
        $processQueuePath = dirname(__DIR__) . '/exec/process_queue.php';
        $command = sprintf(
            'php %s --process-id=%d > %s/logs/queue_process_%d.log 2>&1 &',
            $processQueuePath,
            $processId,
            dirname(__DIR__),
            $processId
        );
        exec($command);
    }

    /**
     * Bereinigt abgeschlossene Queues
     * @param string $olderThan Zeitraum, z.B. '24 hours'
     * @return bool true bei Erfolg
     */
    public function cleanup($olderThan = '24 hours')
    {
        $stmt = $this->db->prepare("
            DELETE q, qi 
            FROM email_queue q
            LEFT JOIN email_queue_items qi ON q.id = qi.queue_id
            WHERE q.status IN ('completed', 'failed')
            AND q.completed_at < DATE_SUB(NOW(), INTERVAL ?)
        ");
        $stmt->bind_param("s", $olderThan);
        return $stmt->execute();
    }
}