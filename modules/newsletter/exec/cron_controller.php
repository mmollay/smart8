<?php
namespace Newsletter;
define('BASE_PATH', dirname(__DIR__));

// Grundeinstellungen
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/cron_error.log');

// Konfigurierbare Parameter
const MAX_EXECUTION_TIME = 3600; // 1 Stunde
const MEMORY_LIMIT = '256M';
const BATCH_SIZE = 30;
const MAX_PROCESSES = 4;
const MAX_JOBS_WARNING = 20000;
const PROCESS_SLEEP_TIME = 2; // Sekunden zwischen Batch-Checks

// Zeit- und Speicherlimits
set_time_limit(MAX_EXECUTION_TIME);
ini_set('memory_limit', MEMORY_LIMIT);

// Erforderliche Dateien einbinden
require_once BASE_PATH . '/n_config.php';
require_once BASE_PATH . '/classes/BatchManager.php';

/**
 * Erweiterte Logging-Funktion mit Memory und Performance Tracking
 */
function writeLog($message, $type = 'INFO', $includeMemory = false)
{
    static $startTime;
    if (!isset($startTime)) {
        $startTime = microtime(true);
    }

    $timestamp = date('Y-m.Y H:i:s');
    $processId = getmypid();
    $logMessage = "[$timestamp][$type][PID:$processId] $message";

    if ($includeMemory) {
        $memoryUsage = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        $runtime = round(microtime(true) - $startTime, 2);

        $logMessage .= sprintf(
            "\nMemory: %s MB | Peak: %s MB | Runtime: %s seconds",
            round($memoryUsage / 1024 / 1024, 2),
            round($peakMemory / 1024 / 1024, 2),
            $runtime
        );
    }

    $logMessage .= "\n";
    $logFile = BASE_PATH . '/logs/cron_controller.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    file_put_contents($logFile, $logMessage, FILE_APPEND);

    if ($type === 'CRITICAL') {
        error_log($message);
    }
}

writeLog("Starte Newsletter-Versand Controller", 'INFO', true);

// Statistik-Variablen
$totalProcessed = 0;
$successCount = 0;
$errorCount = 0;

try {
    // Performance-Counter starten
    $startTime = microtime(true);

    // BatchManager initialisieren
    $batchManager = new BatchManager($db, BASE_PATH . '/logs');
    $batchManager
        ->setBatchSize(BATCH_SIZE)
        ->setMaxProcesses(MAX_PROCESSES)
        ->setRetryLimit(3)
        ->setTimeout(300);

    writeLog(sprintf(
        "BatchManager initialisiert (Batch-Größe: %d, Max. Prozesse: %d)",
        BATCH_SIZE,
        MAX_PROCESSES
    ));

    // Prüfe System-Ressourcen
    $loadAvg = sys_getloadavg();
    if ($loadAvg[0] > 0.8) {
        writeLog("Warnung: Hohe Systemlast detected: " . $loadAvg[0], 'WARNING');
    }

    // Hole aktive Newsletter mit Statistiken
    writeLog("Suche aktive Newsletter...");

    $stmt = $db->prepare("
        WITH NewsletterStats AS (
            SELECT 
                ec.id,
                ec.subject,
                ec.created_at,
                COUNT(ej.id) as total_jobs,
                SUM(CASE WHEN ej.status = 'pending' THEN 1 ELSE 0 END) as pending_jobs,
                SUM(CASE WHEN r.unsubscribed = 1 THEN 1 ELSE 0 END) as unsubscribed_count
            FROM email_contents ec
            JOIN email_jobs ej ON ec.id = ej.content_id
            JOIN recipients r ON ej.recipient_id = r.id
            WHERE ec.send_status = 1
            GROUP BY ec.id, ec.subject, ec.created_at
            HAVING pending_jobs > 0
        )
        SELECT 
            id,
            subject,
            total_jobs,
            pending_jobs,
            unsubscribed_count,
            ROUND((pending_jobs / total_jobs) * 100, 2) as pending_percentage
        FROM NewsletterStats
        WHERE pending_jobs <= ?
        ORDER BY created_at ASC
    ");

    $maxJobs = MAX_JOBS_WARNING;
    $stmt->bind_param("i", $maxJobs);
    $stmt->execute();
    $newsletters = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($newsletters)) {
        writeLog("Keine aktiven Newsletter gefunden");
        exit(0);
    }

    // Log Newsletter-Statistiken
    foreach ($newsletters as $newsletter) {
        writeLog(sprintf(
            "Newsletter ID %d: %s (Total: %d, Pending: %d, Unsubscribed: %d, Pending: %.2f%%)",
            $newsletter['id'],
            $newsletter['subject'],
            $newsletter['total_jobs'],
            $newsletter['pending_jobs'],
            $newsletter['unsubscribed_count'],
            $newsletter['pending_percentage']
        ));
    }

    // Verarbeite jeden Newsletter
    foreach ($newsletters as $newsletter) {
        $contentId = $newsletter['id'];

        // Neuen Cron-Lauf für diesen Newsletter registrieren
        $stmt = $db->prepare("
            INSERT INTO cron_status 
            (start_time, status, content_id) 
            VALUES (NOW(), 'running', ?)
        ");
        $stmt->bind_param("i", $contentId);
        $stmt->execute();
        $cronId = $db->insert_id;

        // Prüfe auf andere laufende Cron-Prozesse für diesen Newsletter
        $stmt = $db->prepare("
            SELECT id 
            FROM cron_status 
            WHERE status = 'running' 
            AND id != ? 
            AND content_id = ?
            AND start_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->bind_param("ii", $cronId, $contentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            writeLog("Es existiert noch ein laufender Cron-Prozess für Newsletter $contentId", 'WARNING');
            continue;
        }

        writeLog("Starte Verarbeitung von Newsletter {$contentId}: {$newsletter['subject']}", 'INFO', true);

        // Setze hängengebliebene Jobs zurück
        $stmt = $db->prepare("
            UPDATE email_jobs 
            SET status = 'pending',
                error_message = 'Reset after cron abort'
            WHERE content_id = ?
            AND status = 'processing'
            AND updated_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ");
        $stmt->bind_param("i", $contentId);
        $stmt->execute();
        $resetCount = $stmt->affected_rows;

        if ($resetCount > 0) {
            writeLog("$resetCount hängengebliebene Jobs wurden zurückgesetzt", 'INFO');
        }

        // Hole ausstehende Jobs
        $stmt = $db->prepare("
            SELECT COUNT(*) as total 
            FROM email_jobs ej
            JOIN recipients r ON ej.recipient_id = r.id
            WHERE ej.content_id = ? 
            AND ej.status = 'pending'
            AND r.unsubscribed = 0
        ");
        $stmt->bind_param("i", $contentId);
        $stmt->execute();
        $totalJobs = $stmt->get_result()->fetch_assoc()['total'];

        writeLog("Ausstehende Emails für Newsletter $contentId: $totalJobs");

        // Verarbeite Batches
        $processedJobs = 0;
        $startBatchTime = microtime(true);
        $newsletterSuccessCount = 0;
        $newsletterErrorCount = 0;

        while ($totalJobs > 0) {
            if ($batchManager->checkProcesses()) {
                // Hole nächsten Batch
                $stmt = $db->prepare("
                    SELECT ej.id 
                    FROM email_jobs ej
                    JOIN recipients r ON ej.recipient_id = r.id
                    WHERE ej.content_id = ? 
                    AND ej.status = 'pending'
                    AND r.unsubscribed = 0
                    LIMIT ?
                ");
                $batchSize = $batchManager->getBatchSize();
                $stmt->bind_param("ii", $contentId, $batchSize);
                $stmt->execute();
                $batch = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                if (empty($batch)) {
                    break;
                }

                // Markiere Jobs als in Bearbeitung
                $jobIds = array_column($batch, 'id');
                $jobIdList = implode(',', $jobIds);
                $db->query("
                    UPDATE email_jobs 
                    SET status = 'processing',
                        updated_at = NOW() 
                    WHERE id IN ($jobIdList)
                ");

                // Starte Batch-Prozess
                $pid = $batchManager->startBatchProcess($contentId, $jobIds);
                if ($pid) {
                    $processedJobs += count($batch);
                    $totalJobs -= count($batch);
                    $totalProcessed += count($batch);

                    // Aktualisiere die Status-Information
                    $stmt = $db->prepare("
                        UPDATE cron_status 
                        SET processed_emails = processed_emails + ?,
                            updated_at = NOW()
                        WHERE id = ?
                    ");
                    $batchSize = count($batch);
                    $stmt->bind_param("ii", $batchSize, $cronId);
                    $stmt->execute();

                    // Log Fortschritt
                    $progress = round(($processedJobs / $newsletter['pending_jobs']) * 100, 2);
                    writeLog(
                        sprintf(
                            "Newsletter %d: %d/%d Jobs verarbeitet (%.2f%%) - PID: %d",
                            $contentId,
                            $processedJobs,
                            $newsletter['pending_jobs'],
                            $progress,
                            $pid
                        )
                    );
                }
            }

            sleep(PROCESS_SLEEP_TIME);
            $batchManager->checkAndResetStaleProcesses();
        }

        // Prüfe ob Newsletter komplett verarbeitet wurde
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as remaining,
                SUM(CASE WHEN status = 'send' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'skipped' THEN 1 ELSE 0 END) as skipped
            FROM email_jobs 
            WHERE content_id = ?
        ");
        $stmt->bind_param("i", $contentId);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();

        $newsletterSuccessCount = $stats['sent'];
        $newsletterErrorCount = $stats['failed'];

        $successCount += $newsletterSuccessCount;
        $errorCount += $newsletterErrorCount;

        // Aktualisiere den Cron-Status
        $stmt = $db->prepare("
            UPDATE cron_status 
            SET end_time = NOW(),
                processed_emails = ?,
                success_count = ?,
                error_count = ?,
                status = 'completed'
            WHERE id = ?
        ");
        $stmt->bind_param("iiii", $processedJobs, $newsletterSuccessCount, $newsletterErrorCount, $cronId);
        $stmt->execute();

        if ($stats['remaining'] == 0) {
            // Setze Newsletter auf abgeschlossen
            $stmt = $db->prepare("
                UPDATE email_contents 
                SET send_status = 2,
                    completed_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $contentId);
            $stmt->execute();

            $duration = round(microtime(true) - $startBatchTime, 2);
            writeLog(
                sprintf(
                    "Newsletter %d abgeschlossen in %.2fs\nStatistik: Gesendet: %d, Fehlgeschlagen: %d, Übersprungen: %d",
                    $contentId,
                    $duration,
                    $stats['sent'],
                    $stats['failed'],
                    $stats['skipped']
                ),
                'INFO',
                true
            );
        }
    }

    $totalDuration = round(microtime(true) - $startTime, 2);
    writeLog("Controller-Durchlauf abgeschlossen in {$totalDuration}s", 'INFO', true);

} catch (Exception $e) {
    // Fehlerbehandlung
    if (isset($cronId)) {
        $errorMsg = $e->getMessage();
        $stmt = $db->prepare("
            UPDATE cron_status 
            SET end_time = NOW(),
                status = 'error',
                error_messages = ?,
                processed_emails = ?,
                success_count = ?,
                error_count = ?
            WHERE id = ?
        ");
        $stmt->bind_param("siiii", $errorMsg, $totalProcessed, $successCount, $errorCount, $cronId);
        $stmt->execute();
    }

    writeLog("Kritischer Fehler: " . $e->getMessage(), 'CRITICAL');
    if (isset($adminEmail)) {
        mail(
            $adminEmail,
            'Fehler im Newsletter-Versand Controller',
            "Zeitpunkt: " . date('Y-m.Y H:i:s') . "\n\n" .
            "Fehler: " . $e->getMessage() . "\n\n" .
            "Stack Trace:\n" . $e->getTraceAsString()
        );
    }
    die("Kritischer Fehler: " . $e->getMessage() . "\n");
}