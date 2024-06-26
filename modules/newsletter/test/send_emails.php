<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../../vendor/autoload.php'; // Überprüfe den Pfad

use \Mailjet\Resources;

$apiKey = '452e5eca1f98da426a9a3542d1726c96';
$apiSecret = '55b277cd54eaa3f1d8188fdc76e06535';

$mj = new \Mailjet\Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);

$recipients = [
    ["Email" => "martin@ssi.at", "Name" => "Martin Mollay"],
    ["Email" => "mm@ssi.at", "Name" => "Martin 2"]
];

$subject = $_POST['subject'] ?? 'Kein Betreff';
$message = $_POST['message'] ?? 'Keine Nachricht';
$attachments = $_FILES['attachments'] ?? null;

$emailData = [
    'Messages' => []
];

foreach ($recipients as $recipient) {
    $email = [
        'From' => [
            'Email' => "office@ssi.at",
            'Name' => "Martin Office"
        ],
        'To' => [
            [
                'Email' => $recipient['Email'],
                'Name' => $recipient['Name']
            ]
        ],
        'Subject' => $subject,
        'TextPart' => $message,
        'HTMLPart' => nl2br($message),
    ];

    if ($attachments && $attachments['size'][0] > 0) {
        $email['Attachments'] = [];
        for ($i = 0; $i < count($attachments['name']); $i++) {
            if ($attachments['size'][$i] > 0) {
                $email['Attachments'][] = [
                    'ContentType' => $attachments['type'][$i],
                    'Filename' => $attachments['name'][$i],
                    'Base64Content' => base64_encode(file_get_contents($attachments['tmp_name'][$i]))
                ];
            }
        }
    }

    $emailData['Messages'][] = $email;
}

$response = $mj->post(Resources::$Email, ['body' => $emailData]);

if ($response->success()) {
    echo "E-Mails wurden erfolgreich gesendet!";
} else {
    http_response_code(500);
    echo "Fehler beim Senden der E-Mails: " . $response->getStatus();
}
?>