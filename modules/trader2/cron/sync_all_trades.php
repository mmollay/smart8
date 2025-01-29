<?php
require_once(__DIR__ . '/../t_config.php');

// Logging-Funktion
function logMessage($message, $data = null)
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}";

    if ($data !== null) {
        $logMessage .= "\nData: " . print_r($data, true);
    }

    $logMessage .= "\n";
    error_log($logMessage);

    if (php_sapi_name() === 'cli') {
        echo $logMessage;
    }
}

try {
    logMessage("Sync-Job gestartet");

    // API Credentials holen
    $stmt = $db->prepare("
        SELECT c.*, u.id as user_id 
        FROM api_credentials c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.is_active = 1
    ");
    $stmt->execute();
    $credentials = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($credentials)) {
        throw new Exception("Keine aktiven API Credentials gefunden");
    }

    logMessage("API Credentials geladen", ['count' => count($credentials)]);

    // Zu synchronisierende Symbole
    $symbols = ['ETHUSDT', 'BTCUSDT'];

    // sync_trades.php für jedes Symbol aufrufen
    foreach ($symbols as $symbol) {
        logMessage("Starte Sync für Symbol {$symbol}");

        // URL für sync_trades.php erstellen
        $url = $_ENV['APP_URL'] . "/modules/trader2/ajax/sync_trades.php?symbol={$symbol}";

        // cURL-Anfrage ausführen
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if ($result['success']) {
                logMessage("Sync erfolgreich für {$symbol}", $result);
            } else {
                logMessage("Sync fehlgeschlagen für {$symbol}", $result);
            }
        } else {
            logMessage("HTTP-Fehler beim Sync von {$symbol}: {$httpCode}");
        }

        // 5 Sekunden warten zwischen den Symbolen
        sleep(5);
    }

    logMessage("Sync-Job abgeschlossen");

} catch (Exception $e) {
    logMessage("Kritischer Fehler: " . $e->getMessage());
    throw $e;
}
