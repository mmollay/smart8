<?php
require_once(__DIR__ . '/t_config.php');
require_once(__DIR__ . '/bitget/bitget_api.php');

// API Credentials holen
$stmt = $db->prepare("
    SELECT * FROM api_credentials 
    WHERE platform = 'bitget' 
    AND is_active = 1 
    ORDER BY last_used DESC 
    LIMIT 1
");
$stmt->execute();
$cred = $stmt->get_result()->fetch_assoc();

if (!$cred) {
    die("Keine aktiven API Credentials gefunden");
}

// Debug: API Credentials anzeigen (nur die ersten paar Zeichen)
echo "Using Credentials:\n";
echo "API Key: " . substr($cred['api_key'], 0, 5) . "...\n";
echo "Secret: " . substr($cred['api_secret'], 0, 5) . "...\n";
echo "Passphrase: " . substr($cred['api_passphrase'], 0, 5) . "...\n\n";

try {
    // BitGet API initialisieren
    $bitget = new BitGetAPI($cred['api_key'], $cred['api_secret'], $cred['api_passphrase']);
    
    // API Verbindung testen
    echo "Testing API connection...\n";
    $result = $bitget->testConnection();
    
    echo "API Response:\n";
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Debug Log checken
    echo "\nCheck error_log for details:\n";
    $log = shell_exec('tail -n 50 /Applications/XAMPP/xamppfiles/logs/php_error.log');
    echo $log;
}
