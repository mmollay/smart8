<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../n_config.php');

class MailjetEventHandlerTest
{
    private $db;
    private $testRecipients = [
        [
            'email' => 'martin@ssi.at',
            'job_id' => 84,
            'message_id' => 'test_message_84'
        ],
        [
            'email' => 'max@example.com',
            'job_id' => 85,
            'message_id' => 'test_message_85'
        ],
        [
            'email' => 'anna.schmidt@technik.com',
            'job_id' => 86,
            'message_id' => 'test_message_86'
        ],
        [
            'email' => 'm.bauer@design-studio.de',
            'job_id' => 88,
            'message_id' => 'test_message_88'
        ],
        [
            'email' => 'klaus.fischer@handel.de',
            'job_id' => 89,
            'message_id' => 'test_message_89'
        ]
    ];

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function runTests()
    {
        foreach ($this->testRecipients as $recipient) {
            $this->testEventSequence($recipient);
        }
    }

    private function testEventSequence($recipient)
    {
        echo "\nTesting event sequence for {$recipient['email']} (Job ID: {$recipient['job_id']})\n";
        echo str_repeat('-', 80) . "\n";

        // Test-Events in chronologischer Reihenfolge
        $events = [
            [
                'event' => 'sent',
                'time' => time(),
                'MessageID' => $recipient['message_id'],
                'email' => $recipient['email']
            ],
            [
                'event' => 'delivered',
                'time' => time() + 5,
                'MessageID' => $recipient['message_id'],
                'email' => $recipient['email']
            ],
            [
                'event' => 'open',
                'time' => time() + 300, // 5 Minuten später
                'MessageID' => $recipient['message_id'],
                'email' => $recipient['email']
            ],
            [
                'event' => 'click',
                'time' => time() + 305, // 5 Sekunden nach dem Öffnen
                'MessageID' => $recipient['message_id'],
                'email' => $recipient['email']
            ]
        ];

        foreach ($events as $event) {
            $this->processAndVerifyEvent($event);
        }
    }

    private function processAndVerifyEvent($event)
    {
        echo "\nProcessing {$event['event']} event for {$event['email']}\n";

        // Event an Handler senden
        $this->simulateWebhook($event);

        // Status in der Datenbank überprüfen
        $this->verifyDatabaseStatus($event);
    }

    private function simulateWebhook($event)
    {
        $json = json_encode($event);

        // Webhook-URL (lokaler Pfad zum Event-Handler)
        $url = 'http://localhost/smart8/modules/newsletter/exec/mailjet_event_handler.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        echo "HTTP Response Code: $httpCode\n";
        echo "Response: $response\n";

        curl_close($ch);
    }

    private function verifyDatabaseStatus($event)
    {
        // Prüfe status_log Eintrag
        $query = "SELECT * FROM status_log WHERE message_id = ? AND event = ? ORDER BY timestamp DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ss", $event['MessageID'], $event['event']);
        $stmt->execute();
        $logResult = $stmt->get_result()->fetch_assoc();

        if ($logResult) {
            echo "✓ Status log entry created successfully\n";
        } else {
            echo "✗ Failed to create status log entry\n";
        }

        // Prüfe email_jobs Status
        $query = "SELECT status FROM email_jobs WHERE message_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $event['MessageID']);
        $stmt->execute();
        $jobResult = $stmt->get_result()->fetch_assoc();

        if ($jobResult) {
            echo "✓ Job status updated to: {$jobResult['status']}\n";
        } else {
            echo "✗ Failed to update job status\n";
        }
    }
}

// Test ausführen
$tester = new MailjetEventHandlerTest($db);
$tester->runTests();