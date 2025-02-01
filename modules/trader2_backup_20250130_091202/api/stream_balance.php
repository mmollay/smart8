<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');

include(__DIR__ . '/../t_config.php');
require_once('BitgetTrading.php');

// Fehlerbehandlung aktivieren
set_error_handler(function ($errno, $errstr) {
    error_log("Stream Error: [$errno] $errstr");
    echo "data: " . json_encode(['error' => $errstr]) . "\n\n";
    flush();
});

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
    $trading = new BitgetTrading(
        $credentials['api_key'],
        $credentials['api_secret'],
        $credentials['api_passphrase']
    );

    // Hauptschleife
    while (true) {
        try {
            // Account Info abrufen
            $accountInfo = $trading->getAccountInfo('ETHUSDT_UMCBL');

            if (isset($accountInfo['data'])) {
                $balanceData = [
                    'crossWalletBalance' => $accountInfo['data']['equity'],
                    'availableBalance' => $accountInfo['data']['available'],
                    'crossUnPnl' => $accountInfo['data']['unrealizedPL'],
                    'timestamp' => time() * 1000
                ];

                echo "data: " . json_encode($balanceData) . "\n\n";
            } else {
                throw new Exception('Ungültige API-Antwort');
            }

        } catch (Exception $e) {
            error_log("Stream Fehler: " . $e->getMessage());
            echo "data: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
        }

        // Buffer leeren und Daten senden
        ob_flush();
        flush();

        // 2 Sekunden warten
        sleep(2);
    }

} catch (Exception $e) {
    error_log("Kritischer Fehler: " . $e->getMessage());
    echo "data: " . json_encode(['error' => 'Kritischer Fehler: ' . $e->getMessage()]) . "\n\n";
}

// Fehlerbehandlung zurücksetzen
restore_error_handler();