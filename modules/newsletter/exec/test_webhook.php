<?php
// Test-Events
$testEvents = [
    [
        'MessageID' => '12345',
        'event' => 'sent',
        'email' => 'test@example.com'
    ],
    [
        'MessageID' => '12345',
        'event' => 'opened',
        'email' => 'test@example.com'
    ],
    [
        'MessageID' => '12345',
        'event' => 'clicked',
        'email' => 'test@example.com'
    ]
];

// URL des Webhooks
$url = 'https://mailjet:m41lj3t@developsmart8.ssi.at/modules/newsletter/exec/mailjet_event_handler.php';

// Event senden
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testEvents));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Response Code: " . $httpCode . "\n";
echo "Response Body: " . $response . "\n";

curl_close($ch);