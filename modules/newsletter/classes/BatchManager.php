<?php
namespace Newsletter;

class BatchManager
{
    private $db;
    private $batchSize = 50;
    private $maxProcesses = 4;
    private $retryLimit = 3;
    private $timeout = 300;         // 5 Minuten Timeout
    private $runningProcesses = [];
    private $logDir;
    private $lastCheck = 0;
    private $staleTimeout = 1800;   // 30 Minuten für "stale" Prozesse

    public function __construct($db, $logDir)
    {
        $this->db = $db;
        $this->logDir = $logDir;

        // Prüfe und erstelle Log-Verzeichnis
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Setzt die Batch-Größe
     * @param int $size Anzahl der E-Mails pro Batch
     */
    public function setBatchSize($size)
    {
        $this->batchSize = max(1, (int) $size);
        return $this;
    }

    /**
     * Gibt die aktuelle Batch-Größe zurück
     */
    public function getBatchSize()
    {
        return $this->batchSize;
    }

    /**
     * Setzt die maximale Anzahl paralleler Prozesse
     */
    public function setMaxProcesses($max)
    {
        $this->maxProcesses = max(1, (int) $max);
        return $this;
    }

    /**
     * Setzt das Retry-Limit für fehlgeschlagene Jobs
     */
    public function setRetryLimit($limit)
    {
        $this->retryLimit = max(1, (int) $limit);
        return $this;
    }

    /**
     * Setzt den Timeout für Batch-Prozesse
     */
    public function setTimeout($seconds)
    {
        $this->timeout = max(60, (int) $seconds);
        return $this;
    }

    /**
     * Prüft laufende Prozesse und deren Status
     */
    public function checkProcesses()
    {
        // Prüfe nur alle 5 Sekunden
        if (time() - $this->lastCheck < 5) {
            return count($this->runningProcesses) < $this->maxProcesses;
        }

        $this->lastCheck = time();
        $currentTime = time();

        foreach ($this->runningProcesses as $pid => $info) {
            // Prüfe ob Prozess noch läuft
            if (!$this->isProcessRunning($pid)) {
                $this->logProcessEnd($pid);
                unset($this->runningProcesses[$pid]);
                continue;
            }

            // Prüfe auf Timeout
            if (($currentTime - $info['start_time']) > $this->timeout) {
                $this->killProcess($pid);
                $this->logProcessTimeout($pid);
                unset($this->runningProcesses[$pid]);
            }
        }

        return count($this->runningProcesses) < $this->maxProcesses;
    }

    /**
     * Startet einen neuen Batch-Prozess
     */
    public function startBatchProcess($contentId, array $jobIds)
    {
        if (empty($jobIds)) {
            return false;
        }

        // Erstelle eindeutigen Log-Dateinamen
        $timestamp = date('Y-m-d_H-i-s');
        $logFile = $this->logDir . "/batch_{$contentId}_{$timestamp}.log";

        // Erstelle Kommandozeilen-Befehl
        $jobIdsStr = implode(',', $jobIds);
        $cmd = sprintf(
            'php %s/exec/process_batch.php --content-id=%d --job-ids=%s >> %s 2>&1 & echo $!',
            dirname($this->logDir),
            $contentId,
            escapeshellarg($jobIdsStr),
            escapeshellarg($logFile)
        );

        // Führe Kommando aus und hole PID
        $pid = trim(shell_exec($cmd));

        // Prüfe ob ein gültiger Prozess gestartet wurde
        if ($pid && is_numeric($pid) && $pid > 0) {
            // Speichere Prozess-Informationen
            $this->runningProcesses[$pid] = [
                'content_id' => $contentId,
                'job_ids' => $jobIds,
                'start_time' => time(),
                'log_file' => $logFile,
                'retry_count' => 0
            ];

            $this->logProcessStart($pid, $contentId, count($jobIds));
            return $pid;
        }

        return false;
    }

    /**
     * Prüft und setzt hängengebliebene Prozesse zurück
     */
    public function checkAndResetStaleProcesses()
    {
        $currentTime = time();

        // Hole alle "processing" Jobs die länger als staleTimeout laufen
        $stmt = $this->db->prepare("
            SELECT 
                ej.id,
                ej.content_id,
                ej.status,
                UNIX_TIMESTAMP(ej.updated_at) as last_update
            FROM email_jobs ej
            WHERE ej.status = 'processing'
            AND UNIX_TIMESTAMP(ej.updated_at) < ?
        ");

        $checkTime = $currentTime - $this->staleTimeout;
        $stmt->bind_param("i", $checkTime);
        $stmt->execute();
        $result = $stmt->get_result();

        $resetCount = 0;
        while ($job = $result->fetch_assoc()) {
            // Setze Job-Status zurück
            $updateStmt = $this->db->prepare("
                UPDATE email_jobs 
                SET status = 'pending',
                    error_message = 'Reset after stale process detection',
                    updated_at = NOW()
                WHERE id = ?
                AND status = 'processing'
            ");
            $updateStmt->bind_param("i", $job['id']);
            $updateStmt->execute();

            if ($updateStmt->affected_rows > 0) {
                $resetCount++;
                $this->logStaleProcessReset($job);
            }
        }

        if ($resetCount > 0) {
            $this->logMessage(
                sprintf("Reset %d stale processes", $resetCount),
                'WARNING'
            );
        }

        return $resetCount;
    }

    /**
     * Prüft ob ein Prozess noch läuft
     */
    private function isProcessRunning($pid)
    {
        if (PHP_OS === 'Linux') {
            exec("ps -p $pid", $output, $returnCode);
            return $returnCode === 0;
        }
        return file_exists("/proc/$pid");
    }

    /**
     * Beendet einen Prozess
     */
    private function killProcess($pid)
    {
        if (PHP_OS === 'Linux') {
            exec("kill -9 $pid");
        }
    }

    /**
     * Logging-Methoden
     */
    private function logMessage($message, $type = 'INFO')
    {
        $logMessage = sprintf(
            "[%s][%s] %s\n",
            date('Y-m-d H:i:s'),
            $type,
            $message
        );
        file_put_contents(
            $this->logDir . '/batch_manager.log',
            $logMessage,
            FILE_APPEND
        );
    }

    private function logProcessStart($pid, $contentId, $jobCount)
    {
        $this->logMessage(
            sprintf(
                "Process started - PID: %d, Content: %d, Jobs: %d",
                $pid,
                $contentId,
                $jobCount
            )
        );
    }

    private function logProcessEnd($pid)
    {
        if (isset($this->runningProcesses[$pid])) {
            $duration = time() - $this->runningProcesses[$pid]['start_time'];
            $this->logMessage(
                sprintf(
                    "Process %d ended after %ds",
                    $pid,
                    $duration
                )
            );
        }
    }

    private function logProcessTimeout($pid)
    {
        if (isset($this->runningProcesses[$pid])) {
            $contentId = $this->runningProcesses[$pid]['content_id'];
            $this->logMessage(
                sprintf(
                    "Process %d (Content: %d) terminated due to timeout",
                    $pid,
                    $contentId
                ),
                'WARNING'
            );
        }
    }

    private function logStaleProcessReset($job)
    {
        $staleDuration = time() - $job['last_update'];
        $this->logMessage(
            sprintf(
                "Reset stale job %d (Content: %d) after %ds",
                $job['id'],
                $job['content_id'],
                $staleDuration
            ),
            'WARNING'
        );
    }
}