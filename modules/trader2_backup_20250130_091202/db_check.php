<?php
require 't_config.php';

// Tabellen überprüfen
$tables = ['orders', 'market_prices', 'api_credentials', 'users'];

foreach ($tables as $table) {
    $result = $db->query("SHOW TABLES LIKE '$table'");
    echo "$table: " . ($result->num_rows > 0 ? "existiert" : "fehlt") . "\n";
    
    if ($result->num_rows > 0) {
        $count = $db->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
        echo "Anzahl Einträge: $count\n";
        
        if ($count > 0) {
            $sample = $db->query("SELECT * FROM $table LIMIT 1")->fetch_assoc();
            echo "Beispiel-Eintrag:\n";
            print_r($sample);
        }
    }
    echo "\n";
}

// Session überprüfen
session_start();
echo "\nSession:\n";
print_r($_SESSION);

$result = $db->query("DESCRIBE users");
while ($row = $result->fetch_assoc()) {
    print_r($row);
    echo "\n";
}
