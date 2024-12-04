<?php
namespace Newsletter;

class BatchManager
{
    private $db;
    private $batchSize = 50;
    private $maxProcesses = 4;
    private $runningProcesses = [];
    private $logDir;

    public function __construct($db, $logDir)
    {
        $this->db = $db;
        $this->logDir = $logDir;
    }

    public function setBatchSize($size)
    {
        $this->batchSize = $size;
        return $this;
    }

    // Neue Methode
    public function getBatchSize()
    {
        return $this->batchSize;
    }

    public function setMaxProcesses($max)
    {
        $this->maxProcesses = $max;
        return $this;
    }

    public function getNewBatch($contentId)
    {
        $stmt = $this->db->prepare("
            SELECT id
            FROM email_jobs
            WHERE content_id = ?
            AND status = 'pending'
            LIMIT ?
        ");
        $stmt->bind_param("ii", $contentId, $this->batchSize);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function startBatchProcess($contentId, $jobIds)
    {
        if (empty($jobIds)) {
            return false;
        }

        $jobIdsStr = implode(',', array_column($jobIds, 'id'));
        $logFile = $this->logDir . "/batch_{$contentId}_" . time() . ".log";

        // Starte Prozess im Hintergrund
        $cmd = sprintf(
            'php %s/exec/process_batch.php --content-id=%d --job-ids=%s >> %s 2>&1 & echo $!',
            BASE_PATH,
            $contentId,
            escapeshellarg($jobIdsStr),
            escapeshellarg($logFile)
        );

        $pid = trim(shell_exec($cmd));
        if ($pid) {
            $this->runningProcesses[$pid] = [
                'content_id' => $contentId,
                'start_time' => time(),
                'log_file' => $logFile
            ];
            return $pid;
        }
        return false;
    }

    public function checkProcesses()
    {
        foreach ($this->runningProcesses as $pid => $info) {
            if (!$this->isProcessRunning($pid)) {
                unset($this->runningProcesses[$pid]);
            }
        }
        return count($this->runningProcesses) < $this->maxProcesses;
    }

    private function isProcessRunning($pid)
    {
        return file_exists("/proc/$pid");
    }

    public function getRunningProcessCount()
    {
        return count($this->runningProcesses);
    }
}