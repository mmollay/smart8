<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);


// Test-Events
$testEvents = [
    [
        'MessageID' => '1152921531891689483',
        'event' => 'opened',
        'email' => 'office@ssi.at',
        'time' => time()
    ]
];

$url = 'https://mailjet:m41lj3t@developsmart8.ssi.at/modules/newsletter/exec/mailjet_event_handler.php';

// JSON kodieren
$jsonData = json_encode($testEvents);

debugLog("Sending data: " . $jsonData);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => $jsonData,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'User-Agent: MailjetWebhookTester/1.0',
        'Content-Length: ' . strlen($jsonData)
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_VERBOSE => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => false // Verhindert das Folgen der Weiterleitung
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Response Code: " . $httpCode . "\n";
echo "Response Body: " . $response . "\n";

curl_close($ch);

// Debug-Funktion
function debugLog($message)
{
    $logFile = __DIR__ . '/../logs/webhook_debug.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, $logFile);
}