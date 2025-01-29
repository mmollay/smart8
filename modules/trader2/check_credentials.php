<?php
require_once(__DIR__ . '/t_config.php');

try {
    echo "Aktuelle Datenbank: " . $_ENV['BINANCE_DB_NAME'] . "<br><br>";
    
    // ssi_trader2 Datenbank prüfen
    $db->select_db('ssi_trader2');
    echo "Checking ssi_trader2 database:<br>";
    
    $stmt = $db->prepare("SELECT * FROM api_credentials WHERE platform = 'bitget' AND is_active = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "Found credential:<br>";
            echo "ID: " . $row['id'] . "<br>";
            echo "User ID: " . $row['user_id'] . "<br>";
            echo "Platform: " . $row['platform'] . "<br>";
            echo "API Key: " . substr($row['api_key'], 0, 5) . "...<br>";
            echo "Is Active: " . $row['is_active'] . "<br>";
            echo "Description: " . ($row['description'] ?? 'None') . "<br>";
            echo "Created At: " . $row['created_at'] . "<br>";
            echo "<hr>";
        }
    } else {
        echo "No active BitGet credentials found in ssi_trader2<br>";
    }
    
    // Zurück zur ursprünglichen Datenbank
    $db->select_db($_ENV['BINANCE_DB_NAME']);
    echo "<br>Checking " . $_ENV['BINANCE_DB_NAME'] . " database:<br>";
    
    $stmt = $db->prepare("SELECT * FROM api_credentials WHERE platform = 'bitget' AND is_active = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "Found credential:<br>";
            echo "ID: " . $row['id'] . "<br>";
            echo "User ID: " . $row['user_id'] . "<br>";
            echo "Platform: " . $row['platform'] . "<br>";
            echo "API Key: " . substr($row['api_key'], 0, 5) . "...<br>";
            echo "Is Active: " . $row['is_active'] . "<br>";
            echo "Description: " . ($row['description'] ?? 'None') . "<br>";
            echo "Created At: " . $row['created_at'] . "<br>";
            echo "<hr>";
        }
    } else {
        echo "No active BitGet credentials found in " . $_ENV['BINANCE_DB_NAME'] . "<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
