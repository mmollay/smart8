<?php
require_once(__DIR__ . '/t_config.php');

try {
    // Prüfen ob die Tabelle existiert
    $result = $db->query("SHOW TABLES LIKE 'api_credentials'");
    if ($result->num_rows == 0) {
        // Tabelle existiert bereits, da wir in ssi_trader2 sind
        echo "Tabelle api_credentials existiert bereits<br>";
    }

    // Credentials aus der Datenbank holen
    $stmt = $db->prepare("
        SELECT * FROM api_credentials 
        WHERE platform = 'bitget' 
        AND is_active = 1 
        ORDER BY last_used DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $creds = $stmt->get_result()->fetch_assoc();

    if (!$creds) {
        throw new Exception("Keine aktiven BitGet Credentials gefunden");
    }

    echo "API Credentials gefunden:<br>";
    echo "Platform: " . $creds['platform'] . "<br>";
    echo "User ID: " . $creds['user_id'] . "<br>";
    echo "API Key: " . substr($creds['api_key'], 0, 5) . "...<br>";
    echo "Description: " . ($creds['description'] ?? 'Keine') . "<br>";
    echo "Active: " . ($creds['is_active'] ? 'Ja' : 'Nein') . "<br>";
    
    echo "<br>Setup abgeschlossen!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
