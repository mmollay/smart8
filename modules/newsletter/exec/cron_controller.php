<?php
namespace Newsletter;
define('BASE_PATH', dirname(__DIR__));

// Grundeinstellungen erweitert
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/cron_error.log');

// Konfigurierbare Parameter
const MAX_EXECUTION_TIME = 3600; // 1 Stunde
const MEMORY_LIMIT = '512M';
const BATCH_SIZE = 50;
const MAX_PROCESSES = 4;
const MAX_JOBS_WARNING = 10000;
const PROCESS_SLEEP_TIME = 2; // Sekunden zwischen Batch-Checks

// Zeit- und Speicherlimits
set_time_limit(MAX_EXECUTION_TIME);
ini_set('memory_limit', MEMORY_LIMIT);

// Erforderliche Dateien einbinden
require_once BASE_PATH . '/n_config.php';
require_once BASE_PATH . '/classes/BatchManager.php';

/**
 * Erweiterte Logging-Funktion mit Memory und Performance Tracking
 * @param string $message Die Log-Nachricht
 * @param string $type Log-Level (INFO, WARNING, ERROR, CRITICAL)
 * @param bool $includeMemory Ob Memory-Informationen geloggt werden sollen
 */

 
function writeLog($message, $type = 'INFO', $includeMemory = false)
{
    static $startTime;
    if (!isset($startTime)) {
        $startTime = microtime(true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $processId = getmypid();

    // Basis Log-Nachricht
    $logMessage = "[$timestamp][$type][PID:$processId] $message";

    // Memory Informationen hinzufügen wenn gewünscht
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

    // Log in Datei schreiben
    $logFile = BASE_PATH . '/logs/cron_controller.log';

    // Prüfe ob Logverzeichnis existiert
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    file_put_contents($logFile, $logMessage, FILE_APPEND);

    // Bei kritischen Fehlern auch in error_log schreiben
    if ($type === 'CRITICAL') {
        error_log($message);
    }
}

writeLog("Starte Newsletter-Versand Controller", 'INFO', true);

try {
    // Performance-Counter starten
    $startTime = microtime(true);

    // BatchManager mit konfigurierbaren Werten initialisieren
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

    // Vor dem bind_param
    $maxJobs = MAX_JOBS_WARNING;  // Variable erstellen
    $stmt->bind_param("i", $maxJobs);  // Variable übergeben
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
        writeLog("Starte Verarbeitung von Newsletter {$contentId}: {$newsletter['subject']}", 'INFO', true);

        // Hole ausstehende Jobs für diesen Newsletter
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

        // Verarbeite Batches solange noch Jobs vorhanden sind
        $processedJobs = 0;
        $startBatchTime = microtime(true);

        while ($totalJobs > 0) {
            // Prüfe ob neue Prozesse gestartet werden können
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

            // Warte kurz bevor der nächste Batch gestartet wird
            sleep(PROCESS_SLEEP_TIME);

            // Überprüfe auf abgebrochene oder fehlgeschlagene Prozesse
            $batchManager->checkAndResetStaleProcesses();
        }

        // Prüfe ob Newsletter komplett verarbeitet wurde
        $stmt = $db->prepare("
            SELECT COUNT(*) as remaining 
            FROM email_jobs ej
            JOIN recipients r ON ej.recipient_id = r.id
            WHERE ej.content_id = ? 
            AND ej.status IN ('pending', 'processing')
            AND r.unsubscribed = 0
        ");
        $stmt->bind_param("i", $contentId);
        $stmt->execute();
        $remaining = $stmt->get_result()->fetch_assoc()['remaining'];

        if ($remaining == 0) {
            // Setze Newsletter auf abgeschlossen
            $stmt = $db->prepare("
                UPDATE email_contents 
                SET send_status = 2,
                    completed_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $contentId);
            $stmt->execute();

            // Erstelle Zusammenfassungs-Log
            $stmt = $db->prepare("
                SELECT 
                    COUNT(CASE WHEN ej.status = 'send' THEN 1 END) as sent,
                    COUNT(CASE WHEN ej.status = 'failed' THEN 1 END) as failed,
                    COUNT(CASE WHEN ej.status = 'skipped' THEN 1 END) as skipped
                FROM email_jobs ej
                WHERE ej.content_id = ?
            ");
            $stmt->bind_param("i", $contentId);
            $stmt->execute();
            $stats = $stmt->get_result()->fetch_assoc();

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
    writeLog("Kritischer Fehler: " . $e->getMessage(), 'CRITICAL');
    if (isset($adminEmail)) {
        mail(
            $adminEmail,
            'Fehler im Newsletter-Versand Controller',
            "Zeitpunkt: " . date('Y-m-d H:i:s') . "\n\n" .
            "Fehler: " . $e->getMessage() . "\n\n" .
            "Stack Trace:\n" . $e->getTraceAsString()
        );
    }
    die("Kritischer Fehler: " . $e->getMessage() . "\n");
}