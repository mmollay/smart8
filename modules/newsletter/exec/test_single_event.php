<?php
// Beispiel für einen einzelnen Test-Event
$testEvent = [
    'event' => 'open',
    //'event' => 'click',
    //'event' => 'delivered',
    'time' => time(),
    'MessageID' => '288230403010247047',
    'email' => 'martin@ssi.at'
];

$webhookUrl = 'http://localhost/smart8/modules/newsletter/exec/mailjet_event_handler.php';

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testEvent));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Testing event: {$testEvent['event']} for {$testEvent['email']}\n";
echo "HTTP Response Code: $httpCode\n";
echo "Response: $response\n";

curl_close($ch);
?>