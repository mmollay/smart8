<?php
require __DIR__ . '/../../../vendor/autoload.php'; // Überprüfe den Pfad

$host = 'localhost';
$dbname = 'ssi_newsletter';
$username = 'smart';
$password = 'Eiddswwenph21;';

$db = new mysqli($host, $username, $password, $dbname);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

use \Mailjet\Resources;

// Suche nach pending Jobs in der Datenbank
$result = $db->query("SELECT * FROM email_jobs WHERE status = 'pending'");

while ($row = $result->fetch_assoc()) {
    $file = $row['data_file'];
    $emailData = json_decode(file_get_contents($file), true);

    // Holen der E-Mail-Inhalte
    $contentResult = $db->query("SELECT * FROM email_contents WHERE id = " . $row['content_id']);
    $contentRow = $contentResult->fetch_assoc();

    // Holen des Absenders
    $senderResult = $db->query("SELECT * FROM senders WHERE id = " . $row['sender_id']);
    $senderRow = $senderResult->fetch_assoc();

    // Holen des Empfängers
    $recipientResult = $db->query("SELECT * FROM recipients WHERE id = " . $row['recipient_id']);
    $recipientRow = $recipientResult->fetch_assoc();

    $apiKey = '452e5eca1f98da426a9a3542d1726c96';
    $apiSecret = '55b277cd54eaa3f1d8188fdc76e06535';

    $mj = new \Mailjet\Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);

    $messages = [];

    $email = [
        'From' => [
            'Email' => $senderRow['email'],
            'Name' => $senderRow['first_name'] . ' ' . $senderRow['last_name']
        ],
        'To' => [
            [
                'Email' => $recipientRow['email'],
                'Name' => $recipientRow['first_name'] . ' ' . $recipientRow['last_name']
            ]
        ],
        'Subject' => $contentRow['subject'],
        'TextPart' => $contentRow['message'],
        'HTMLPart' => nl2br($contentRow['message']),
        'CustomID' => "email_job_" . $row['id']
    ];

    if (isset($emailData['attachments']) && count($emailData['attachments']) > 0) {
        $email['Attachments'] = [];
        foreach ($emailData['attachments'] as $attachment) {
            $email['Attachments'][] = [
                'ContentType' => $attachment['type'],
                'Filename' => $attachment['name'],
                'Base64Content' => base64_encode(file_get_contents($attachment['path']))
            ];
        }
    }

    $messages[] = $email;

    $response = $mj->post(Resources::$Email, ['body' => ['Messages' => $messages]]);

    if ($response->success()) {
        $status = 'success';
        $db->query("UPDATE email_jobs SET status = 'success' WHERE id = " . $row['id']);
        unlink($file); // Lösche die temporäre Datei nach dem erfolgreichen Versand
    } else {
        $status = 'failed';
        $db->query("UPDATE email_jobs SET status = 'failed' WHERE id = " . $row['id']);
    }

    // Log-Eintrag erstellen
    $logStmt = $db->prepare("INSERT INTO email_logs (job_id, status, response) VALUES (?, ?, ?)");
    $logResponse = json_encode($response->getBody());
    $logStmt->bind_param("iss", $row['id'], $status, $logResponse);
    $logStmt->execute();
    $logStmt->close();
}

$db->close();
