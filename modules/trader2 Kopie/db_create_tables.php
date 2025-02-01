<?php
require 't_config.php';

// market_prices Tabelle erstellen
$sql = "
CREATE TABLE IF NOT EXISTS market_prices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    symbol VARCHAR(20) NOT NULL,
    price DECIMAL(20,8) NOT NULL,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_symbol_timestamp (symbol, timestamp)
)";

if ($db->query($sql)) {
    echo "market_prices Tabelle erstellt\n";
} else {
    echo "Fehler beim Erstellen der market_prices Tabelle: " . $db->error . "\n";
}

// Test-Trade platzieren
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
    1,
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

if ($db->query($sql)) {
    echo "Test-Trade erstellt\n";
} else {
    echo "Fehler beim Erstellen des Test-Trades: " . $db->error . "\n";
}
