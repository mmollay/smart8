<?php
// Test-Datei für den Event Handler
$url = 'http://localhost/smart8/modules/newsletter/exec/mailjet_event_handler.php';

// Test Events
$testEvents = [
    [
        "event" => "opened",
        "time" => time(),
        "MessageID" => "576460779645409236", // Hier eine echte Message-ID aus deiner DB verwenden
        "email" => "test@example.com",
        "ip" => "1.2.3.4",
        "user_agent" => "Mozilla/5.0"
    ],
    [
        "event" => "clicked",
        "time" => time(),
        "MessageID" => "12345678901234567", // Gleiche Message-ID
        "url" => "http://example.com/link",
        "email" => "test@example.com",
        "ip" => "1.2.3.4",
        "user_agent" => "Mozilla/5.0"
    ]
];

// CURL Request
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testEvents));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_USERPWD, "mailjet:m41lj3t");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Status: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

curl_close($ch);