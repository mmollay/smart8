<?php
namespace Newsletter;
define('BASE_PATH', dirname(__DIR__));

// Grundeinstellungen
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/cron_error.log');

// Zeit- und Speicherlimits
set_time_limit(0); // Kein Zeitlimit für den Controller
ini_set('memory_limit', '256M');

require_once BASE_PATH . '/n_config.php';
require_once BASE_PATH . '/classes/BatchManager.php';

// Logging-Funktion
function writeLog($message, $type = 'INFO')
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp][$type] $message\n";
    file_put_contents(
        BASE_PATH . '/logs/cron_controller.log',
        $logMessage,
        FILE_APPEND
    );
}

writeLog("Starte Newsletter-Versand Controller");

try {
    // BatchManager initialisieren
    $batchManager = new BatchManager($db, BASE_PATH . '/logs');
    $batchManager->setBatchSize(50)         // 50 Emails pro Batch
        ->setMaxProcesses(4);       // 4 parallele Prozesse

    // Hole alle aktiven Newsletter
    $stmt = $db->prepare("
        SELECT DISTINCT ec.id, ec.subject
        FROM email_contents ec
        JOIN email_jobs ej ON ec.id = ej.content_id
        WHERE ej.status = 'pending'
        AND ec.send_status = 1  -- 1 = Versand gestartet
        ORDER BY ec.created_at ASC
    ");

    $stmt->execute();
    $newsletters = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($newsletters)) {
        writeLog("Keine aktiven Newsletter gefunden");
        exit(0);
    }

    writeLog("Gefundene Newsletter: " . count($newsletters));

    // Verarbeite jeden Newsletter
    foreach ($newsletters as $newsletter) {
        $contentId = $newsletter['id'];
        writeLog("Verarbeite Newsletter {$contentId}: {$newsletter['subject']}");

        // Hole Gesamtanzahl der pending Jobs für diesen Newsletter
        $stmt = $db->prepare("
            SELECT COUNT(*) as total 
            FROM email_jobs 
            WHERE content_id = ? 
            AND status = 'pending'
        ");
        $stmt->bind_param("i", $contentId);
        $stmt->execute();
        $totalJobs = $stmt->get_result()->fetch_assoc()['total'];

        writeLog("Ausstehende Emails für Newsletter $contentId: $totalJobs");

        // Verarbeite Batches solange noch Jobs vorhanden sind
        while ($totalJobs > 0) {
            // Prüfe ob neue Prozesse gestartet werden können
            if ($batchManager->checkProcesses()) {
                // Hole nächsten Batch
                $batch = $batchManager->getNewBatch($contentId);
                if (empty($batch)) {
                    break;
                }

                // Starte Batch-Prozess
                $pid = $batchManager->startBatchProcess($contentId, $batch);
                if ($pid) {
                    writeLog("Batch-Prozess gestartet für Newsletter $contentId mit PID: $pid");
                    $totalJobs -= count($batch);
                }
            }

            // Warte kurz bevor der nächste Batch gestartet wird
            sleep(2);
        }

        // Prüfe ob Newsletter komplett verarbeitet wurde
        $stmt = $db->prepare("
            SELECT COUNT(*) as remaining 
            FROM email_jobs 
            WHERE content_id = ? 
            AND status = 'pending'
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
            writeLog("Newsletter $contentId vollständig verarbeitet");
        }
    }

    writeLog("Controller-Durchlauf abgeschlossen");

} catch (Exception $e) {
    writeLog("Kritischer Fehler: " . $e->getMessage(), 'CRITICAL');
    if (isset($adminEmail)) {
        mail(
            $adminEmail,
            'Fehler im Newsletter-Versand Controller',
            "Zeitpunkt: " . date('Y-m-d H:i:s') . "\n\nFehler: " . $e->getMessage()
        );
    }
    die("Kritischer Fehler: " . $e->getMessage() . "\n");
}