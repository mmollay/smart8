<?php
session_start();
require 't_config.php';

if (!isset($_SESSION['user_id'])) {
    die("Nicht eingeloggt");
}

// Test-Trade in die DB einfügen
$sql = "
INSERT INTO orders (
    user_id, 
    parameter_model_id,
    symbol,
    side,
    position_size,
    entry_price,
    take_profit,
    stop_loss,
    leverage,
    status,
    created_at
) VALUES (
    ?,
    6,
    'BTCUSDT',
    'buy',
    0.001,
    42000,
    43000,
    41000,
    5,
    'pending',
    NOW()
)";

$stmt = $db->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);

if ($stmt->execute()) {
    echo "Test-Trade erstellt für User " . $_SESSION['user_id'] . "\n";
    echo "Öffnen Sie die Monitor-Seite um den Trade zu sehen\n";
} else {
    echo "Fehler beim Erstellen des Test-Trades: " . $db->error . "\n";
}
