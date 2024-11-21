<?php
require_once(__DIR__ . '/vendor/autoload.php');
use \Mailjet\Client;
use \Mailjet\Resources;
function sendMail($to, $subject, $htmlContent, $attachments = [])
{
    $mj = new Client('452e5eca1f98da426a9a3542d1726c96', '55b277cd54eaa3f1d8188fdc76e06535', true, ['version' => 'v3.1']);

    $body = [
        'Messages' => [
            [
                'From' => [
                    'Email' => "office@ssi.at",
                    'Name' => "SSI Office"
                ],
                'To' => [
                    [
                        'Email' => $to,
                        'Name' => ""
                    ]
                ],
                'Subject' => $subject,
                'TextPart' => strip_tags($htmlContent),
                'HTMLPart' => $htmlContent
            ]
        ]
    ];

    // Wenn Anhänge vorhanden sind
    if (!empty($attachments)) {
        $body['Messages'][0]['Attachments'] = [];
        foreach ($attachments as $attachment) {
            if (file_exists($attachment['path'])) {
                $body['Messages'][0]['Attachments'][] = [
                    'ContentType' => mime_content_type($attachment['path']),
                    'Filename' => $attachment['name'],
                    'Base64Content' => base64_encode(file_get_contents($attachment['path']))
                ];
            }
        }
    }

    try {
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return [
            'success' => $response->success(),
            'data' => $response->getData()
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Beispiel für die Verwendung:

// 1. Einfache E-Mail
$result = sendMail(
    'martin@ssi.at',
    'Test E-Mail',
    '<h1>Hallo!</h1><p>Das ist eine Testnachricht.</p>'
);

// 2. E-Mail mit Anhang
$result = sendMail(
    'martin@ssi.at',
    'Test E-Mail mit Anhang',
    '<h1>Hallo!</h1><p>Diese E-Mail hat einen Anhang.</p>',
    [
        [
            'path' => '/pfad/zu/datei.pdf',
            'name' => 'Dokument.pdf'
        ]
    ]
);

// Ergebnis prüfen
if ($result['success']) {
    echo "E-Mail wurde gesendet!";
} else {
    echo "Fehler: " . ($result['error'] ?? 'Unbekannter Fehler');
}