<?php
require(__DIR__ . '/t_config.php');

// Sessions Tabelle erstellen
$sql = "
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) NOT NULL,
    client_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_session (session_id)
)";

if ($db->query($sql)) {
    echo "Sessions Tabelle erstellt\n";
} else {
    echo "Fehler beim Erstellen der Sessions Tabelle: " . $db->error . "\n";
}
