<?php
// api/get_ws_config.php
header('Content-Type: application/json');
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../websocket/BitgetWebsocket.php');

try {
    // API credentials holen
    $query = "SELECT api_key, api_secret, api_passphrase FROM api_credentials 
              WHERE platform = 'Bitget' AND is_active = 1 
              ORDER BY id DESC LIMIT 1";
    $result = $db->query($query);

    if (!$result || $result->num_rows === 0) {
        throw new Exception('Keine API-Credentials gefunden');
    }

    $credentials = $result->fetch_assoc();

    // BitgetWebsocket Instanz erstellen
    $wsHandler = new BitgetWebsocket(
        $credentials['api_key'],
        $credentials['api_secret'],
        $credentials['api_passphrase']
    );

    // Konfiguration generieren
    $config = $wsHandler->getConfig();

    echo json_encode([
        'success' => true,
        'config' => $config
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}