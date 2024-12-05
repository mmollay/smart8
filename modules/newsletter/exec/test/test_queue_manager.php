<?php
// Definiere den Basispfad
define('BASE_PATH', dirname(__DIR__));

// Lade die erforderlichen Dateien
require_once BASE_PATH . '/n_config.php';
require_once BASE_PATH . '/classes/EmailQueueManager.php';

// Stelle sicher, dass das Logs-Verzeichnis existiert
$logDir = BASE_PATH . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

class EmailQueueManagerTest
{
    private $db;
    private $queueManager;
    private $testResults = [];
    private $testNewsletterId;
    private $testSenderId;
    private $debug = false;
    private $processIds = [];

    public function __construct($db, $debug = false)
    {
        $this->db = $db;
        $this->queueManager = new EmailQueueManager($db);
        $this->debug = $debug;
    }

    public function runTests()
    {
        try {
            $this->createTestSender();
            $this->setupTestData();

            // Führe Tests aus
            $this->testCreateQueue();
            $this->testQueueProcessing();
            $this->testMonitorProgress();
            $this->testErrorHandling();
            $this->testConcurrency();

            $this->cleanupTestData();
            $this->displayResults();
        } catch (Exception $e) {
            echo "Kritischer Fehler beim Testen: " . $e->getMessage() . "\n";
        } finally {
            // Cleanup: Beende alle gestarteten Prozesse
            $this->cleanupProcesses();
        }
    }

    private function createTestSender()
    {
        $this->logTest('Setup: Test-Absender erstellen');

        try {
            // Prüfe ob Test-Absender bereits existiert
            $stmt = $this->db->prepare("
                SELECT id FROM senders 
                WHERE email = 'test@example.com'
                LIMIT 1
            ");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $this->testSenderId = $row['id'];
            } else {
                // Erstelle neuen Test-Absender
                $stmt = $this->db->prepare("
                    INSERT INTO senders (
                        email, 
                        first_name, 
                        last_name
                    ) VALUES (
                        'test@example.com', 
                        'Test', 
                        'Sender'
                    )
                ");
                $stmt->execute();
                $this->testSenderId = $this->db->insert_id;
            }

            $this->logResult('Test-Absender erstellen', true, 'Test-Absender ID: ' . $this->testSenderId);
        } catch (Exception $e) {
            $this->logResult('Test-Absender erstellen', false, 'Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    private function setupTestData()
    {
        $this->logTest('Setup: Testdaten erstellen');

        try {
            // Newsletter erstellen
            $stmt = $this->db->prepare("
                INSERT INTO email_contents (
                    subject, 
                    message, 
                    send_status, 
                    sender_id,
                    created_at
                ) VALUES (?, ?, 0, ?, NOW())
            ");
            $subject = 'Test Newsletter ' . date('Y-m-d H:i:s');
            $message = 'Test Message';
            $stmt->bind_param("ssi", $subject, $message, $this->testSenderId);
            $stmt->execute();
            $this->testNewsletterId = $this->db->insert_id;

            // Test-Empfänger erstellen
            for ($i = 1; $i <= 5; $i++) {
                // Erstelle Empfänger
                $stmt = $this->db->prepare("
                    INSERT INTO recipients (
                        email, 
                        first_name, 
                        last_name,
                        created_at
                    ) VALUES (
                        ?, ?, ?, NOW()
                    )
                ");
                $email = "test{$i}@example.com";
                $firstName = "Test{$i}";
                $lastName = "User{$i}";
                $stmt->bind_param("sss", $email, $firstName, $lastName);
                $stmt->execute();
                $recipientId = $this->db->insert_id;

                // Erstelle Email-Job für diesen Empfänger
                $stmt = $this->db->prepare("
                    INSERT INTO email_jobs (
                        content_id, 
                        recipient_id, 
                        sender_id, 
                        status,
                        created_at
                    ) VALUES (
                        ?, ?, ?, 'pending', NOW()
                    )
                ");
                $stmt->bind_param("iii", $this->testNewsletterId, $recipientId, $this->testSenderId);
                $stmt->execute();
            }

            $this->logResult('Testdaten erstellen', true, sprintf(
                'Testdaten erfolgreich erstellt. Newsletter ID: %d mit 5 Empfängern',
                $this->testNewsletterId
            ));
        } catch (Exception $e) {
            $this->logResult('Testdaten erstellen', false, 'Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    private function testCreateQueue()
    {
        $this->logTest('Test: Queue erstellen');

        try {
            $result = $this->queueManager->createQueue($this->testNewsletterId);

            // Prüfe exakte Anzahl der erstellten Items
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT q.id) as queue_count,
                    COUNT(qi.id) as items_count,
                    (SELECT COUNT(*) FROM email_jobs WHERE content_id = ?) as expected_count
                FROM email_queue q 
                LEFT JOIN email_queue_items qi ON q.id = qi.queue_id
                WHERE q.content_id = ?
            ");
            $stmt->bind_param("ii", $this->testNewsletterId, $this->testNewsletterId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            $success = $row['items_count'] === $row['expected_count'];
            $this->logResult(
                'Queue erstellen',
                $success,
                sprintf(
                    "Queue erstellt mit %d Queue(s) und %d/%d Items",
                    $row['queue_count'],
                    $row['items_count'],
                    $row['expected_count']
                )
            );
        } catch (Exception $e) {
            $this->logResult('Queue erstellen', false, 'Exception: ' . $e->getMessage());
        }
    }

    private function startProcessor($processId)
    {
        $processQueuePath = BASE_PATH . '/exec/process_queue.php';
        $logPath = BASE_PATH . '/logs/queue_process_' . $processId . '.log';

        $this->logDebug("Starte Queue-Processor $processId");
        $this->logDebug("Pfad: $processQueuePath");
        $this->logDebug("Log: $logPath");

        if (!file_exists($processQueuePath)) {
            throw new Exception("process_queue.php nicht gefunden in: $processQueuePath");
        }

        $command = sprintf(
            'php %s --process-id=%d --content-id=%d > %s 2>&1 & echo $!',
            $processQueuePath,
            $processId,
            $this->testNewsletterId,
            $logPath
        );

        $this->logDebug("Ausführe: $command");
        exec($command, $output);

        if (empty($output[0])) {
            throw new Exception("Konnte Process ID nicht ermitteln");
        }

        $pid = (int) $output[0];
        $this->processIds[] = $pid;
        $this->logDebug("Processor gestartet mit PID: $pid");

        return $pid;
    }

    private function isProcessRunning($pid)
    {
        try {
            $result = shell_exec(sprintf('ps %d 2>&1', $pid));
            return (bool) preg_match('/\b' . $pid . '\b/', $result);
        } catch (Exception $e) {
            return false;
        }
    }

    private function cleanupProcesses()
    {
        foreach ($this->processIds as $pid) {
            if ($this->isProcessRunning($pid)) {
                posix_kill($pid, SIGTERM);
                $this->logDebug("Process $pid beendet");
            }
        }
        $this->processIds = [];
    }

    private function logDebug($message)
    {
        if ($this->debug) {
            $timestamp = date('Y-m-d H:i:s');
            $debugMessage = "[$timestamp] DEBUG: $message\n";

            echo $debugMessage;

            $logFile = BASE_PATH . '/logs/test_debug.log';
            file_put_contents($logFile, $debugMessage, FILE_APPEND);
        }
    }

    // ... [Vorherige Methoden bleiben gleich] ...

    private function testConcurrency()
    {
        $this->logTest('Test: Parallelverarbeitung');

        try {
            $maxProcesses = 2;
            $pids = [];

            // Starte Prozesse
            for ($i = 0; $i < $maxProcesses; $i++) {
                try {
                    $pids[$i] = $this->startProcessor($i);
                } catch (Exception $e) {
                    $this->logDebug("Fehler beim Starten von Processor $i: " . $e->getMessage());
                }
            }

            // Warte und überwache
            sleep(2);

            // Zähle aktive Prozesse
            $activeProcesses = 0;
            foreach ($pids as $pid) {
                if ($this->isProcessRunning($pid)) {
                    $activeProcesses++;
                }
            }

            // Prüfe Queue-Items
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as processing_count,
                    GROUP_CONCAT(DISTINCT status) as status_list
                FROM email_queue_items 
                WHERE queue_id IN (
                    SELECT id FROM email_queue 
                    WHERE content_id = ?
                )
            ");
            $stmt->bind_param("i", $this->testNewsletterId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            $success = $result['processing_count'] > 0;
            $this->logResult(
                'Parallelverarbeitung',
                $success,
                sprintf(
                    "Aktive Prozesse: %d/%d, Queue-Status: %s",
                    $activeProcesses,
                    $maxProcesses,
                    $result['status_list']
                )
            );

        } catch (Exception $e) {
            $this->logResult('Parallelverarbeitung', false, 'Exception: ' . $e->getMessage());
        }
    }


    private function testQueueProcessing()
    {
        $this->logTest('Test: Queue Verarbeitung');

        try {
            // Starte Verarbeitung als separaten Prozess
            $pid = $this->startProcessor(1);
            $this->logDebug("Queue-Processor gestartet mit PID: $pid");

            // Warte kurz und prüfe Status
            sleep(2);

            // Prüfe Verarbeitungsstatus
            $status = $this->queueManager->checkStatus($this->testNewsletterId);

            $this->logResult(
                'Queue Verarbeitung',
                isset($status['processed_emails']),
                sprintf(
                    "Verarbeitete E-Mails: %d, Gesamt: %d",
                    $status['processed_emails'] ?? 0,
                    $status['total_emails'] ?? 0
                )
            );
        } catch (Exception $e) {
            $this->logResult('Queue Verarbeitung', false, 'Exception: ' . $e->getMessage());
        }
    }

    private function testMonitorProgress()
    {
        $this->logTest('Test: Fortschritt überwachen');

        try {
            $startStatus = $this->queueManager->checkStatus($this->testNewsletterId);
            $this->logDebug("Start-Status: " . json_encode($startStatus));

            sleep(2);

            $endStatus = $this->queueManager->checkStatus($this->testNewsletterId);
            $this->logDebug("End-Status: " . json_encode($endStatus));

            if (!$startStatus || !$endStatus) {
                throw new Exception("Konnte Status nicht abrufen");
            }

            $progressMade = $endStatus['processed_emails'] >= $startStatus['processed_emails'];
            $this->logResult(
                'Fortschritt überwachen',
                $progressMade,
                sprintf(
                    "Start: %d, Ende: %d verarbeitet",
                    $startStatus['processed_emails'] ?? 0,
                    $endStatus['processed_emails'] ?? 0
                )
            );
        } catch (Exception $e) {
            $this->logResult('Fortschritt überwachen', false, 'Exception: ' . $e->getMessage());
        }
    }
    private function testErrorHandling()
    {
        $this->logTest('Test: Fehlerbehandlung');

        try {
            // Aktualisiere zuerst die email_jobs Tabelle
            $stmt = $this->db->prepare("
                UPDATE email_jobs 
                SET status = 'failed',
                    error_message = 'Simulierter Testfehler'
                WHERE content_id = ? 
                LIMIT 1
            ");
            $stmt->bind_param("i", $this->testNewsletterId);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Konnte keinen Email-Job zum Testen finden");
            }

            // Aktualisiere dann die Queue-Items
            $stmt = $this->db->prepare("
                UPDATE email_queue_items qi
                JOIN email_queue q ON qi.queue_id = q.id
                JOIN email_jobs ej ON qi.email_job_id = ej.id
                SET qi.status = 'failed',
                    qi.error_message = 'Simulierter Testfehler',
                    qi.attempts = 3,
                    q.failed_emails = q.failed_emails + 1
                WHERE q.content_id = ?
                AND ej.status = 'failed'
            ");
            $stmt->bind_param("i", $this->testNewsletterId);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Konnte keine Queue-Items aktualisieren");
            }

            // Warte kurz, damit die Änderungen wirksam werden
            sleep(1);

            // Prüfe Status nach Fehlersimulation
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_items,
                    SUM(CASE WHEN qi.status = 'failed' THEN 1 ELSE 0 END) as failed_items,
                    GROUP_CONCAT(DISTINCT qi.status) as status_list
                FROM email_queue q
                JOIN email_queue_items qi ON qi.queue_id = q.id
                WHERE q.content_id = ?
            ");
            $stmt->bind_param("i", $this->testNewsletterId);
            $stmt->execute();
            $queueStatus = $stmt->get_result()->fetch_assoc();

            $this->logDebug("Queue Items Status: " . json_encode($queueStatus));

            // Prüfe Status der Email-Jobs
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_jobs,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_jobs
                FROM email_jobs
                WHERE content_id = ?
            ");
            $stmt->bind_param("i", $this->testNewsletterId);
            $stmt->execute();
            $jobStatus = $stmt->get_result()->fetch_assoc();

            $this->logDebug("Email Jobs Status: " . json_encode($jobStatus));

            // Prüfe Gesamtstatus
            $status = $this->queueManager->checkStatus($this->testNewsletterId);
            $this->logDebug("Queue Manager Status: " . json_encode($status));

            $success = $status['failed_emails'] > 0;
            $this->logResult(
                'Fehlerbehandlung',
                $success,
                sprintf(
                    "Fehlgeschlagene E-Mails: %d/%d (Queue Status: %s, Jobs fehlgeschlagen: %d)",
                    $status['failed_emails'],
                    $status['total_emails'],
                    $queueStatus['status_list'] ?? 'unbekannt',
                    $jobStatus['failed_jobs']
                )
            );
        } catch (Exception $e) {
            $this->logResult('Fehlerbehandlung', false, 'Exception: ' . $e->getMessage());
        }
    }


    private function cleanupTestData()
    {
        $this->logTest('Cleanup: Testdaten entfernen');

        try {
            // Lösche alle Test-Prozesse
            $this->cleanupProcesses();

            // Lösche Test-Queue-Einträge
            $this->db->query("
                DELETE FROM email_queue_items 
                WHERE queue_id IN (
                    SELECT id FROM email_queue 
                    WHERE content_id = {$this->testNewsletterId}
                )
            ");

            $this->db->query("
                DELETE FROM email_queue 
                WHERE content_id = {$this->testNewsletterId}
            ");

            // Lösche Email-Jobs
            $stmt = $this->db->prepare("
                DELETE FROM email_jobs 
                WHERE content_id = ?
            ");
            $stmt->bind_param("i", $this->testNewsletterId);
            $stmt->execute();

            // Lösche Test-Newsletter
            $stmt = $this->db->prepare("
                DELETE FROM email_contents 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $this->testNewsletterId);
            $stmt->execute();

            // Lösche Test-Empfänger (die in diesem Test erstellt wurden)
            $this->db->query("
                DELETE FROM recipients 
                WHERE email LIKE 'test%@example.com'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ");

            $this->logResult('Testdaten entfernen', true, 'Aufräumen erfolgreich');
        } catch (Exception $e) {
            $this->logResult('Testdaten entfernen', false, 'Exception: ' . $e->getMessage());
        }
    }

    private function logTest($message)
    {
        echo "\n=== $message ===\n";
    }

    private function logResult($test, $success, $message)
    {
        $this->testResults[] = [
            'test' => $test,
            'success' => $success,
            'message' => $message
        ];

        // Direkte Ausgabe des Ergebnisses
        $status = $success ? "\033[32m✓\033[0m" : "\033[31m✗\033[0m";
        echo "$status $test: $message\n";
    }

    private function displayResults()
    {
        echo "\n=== Testergebnisse ===\n";
        $totalTests = count($this->testResults);
        $successfulTests = 0;
        $failedTests = [];

        foreach ($this->testResults as $result) {
            if ($result['success']) {
                $successfulTests++;
            } else {
                $failedTests[] = $result;
            }
        }

        echo "\nZusammenfassung: $successfulTests/$totalTests Tests erfolgreich\n";

        if (!empty($failedTests)) {
            echo "\nFehlgeschlagene Tests:\n";
            foreach ($failedTests as $test) {
                echo "\033[31m✗ {$test['test']}: {$test['message']}\033[0m\n";
            }
        }

        // Schreibe Zusammenfassung in Log-Datei
        $logMessage = sprintf(
            "[%s] Testergebnis: %d/%d Tests erfolgreich\n",
            date('Y-m-d H:i:s'),
            $successfulTests,
            $totalTests
        );
        file_put_contents(BASE_PATH . '/logs/test_summary.log', $logMessage, FILE_APPEND);
    }
}




// Debug aktivieren
define('DEBUG', true);

// Tests ausführen
try {
    $tester = new EmailQueueManagerTest($db, true);
    $tester->runTests();
} catch (Exception $e) {
    $errorMessage = "Fehler beim Initialisieren der Tests: " . $e->getMessage() . "\n";
    error_log($errorMessage, 3, $logDir . '/test_error.log');
    die($errorMessage);
}
