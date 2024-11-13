<?php
require_once(__DIR__ . '/../n_config.php');

function testStatusTransition($messageId, $fromStatus, $toStatus)
{
    global $db;

    // Set initial status
    $db->query("UPDATE email_jobs SET status = '$fromStatus' WHERE message_id = '$messageId'");

    // Create event
    $event = [
        'event' => $toStatus,
        'MessageID' => $messageId,
        'email' => 'test@example.com',
        'time' => time()
    ];

    // Send to handler
    $ch = curl_init('http://localhost/smart8/modules/newsletter/exec/mailjet_event_handler.php);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($event));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check new status
    $result = $db->query("SELECT status FROM email_jobs WHERE message_id = '$messageId'");
    $newStatus = $result->fetch_assoc()['status'];

    echo "Testing transition: $fromStatus -> $toStatus\n";
    echo "Result: " . ($newStatus === $toStatus ? "SUCCESS" : "FAILED (got $newStatus)") . "\n\n";
}

// Test all transitions
$transitions = [
    ['send', 'delivered'],
    ['delivered', 'open'],
    ['open', 'click'],
    ['click', 'open'],  // Sollte nicht ändern
    ['delivered', 'bounce'],
    ['open', 'spam']
];

foreach ($transitions as $transition) {
    testStatusTransition('test_message_1', $transition[0], $transition[1]);
}