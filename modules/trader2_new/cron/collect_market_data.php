<?php
require_once(__DIR__ . '/../config/t_config.php');
require_once(__DIR__ . '/../classes/MarketData.php');
require_once(__DIR__ . '/../classes/BitgetTrading.php');

try {
    // Hole aktive Symbole
    $query = "SELECT DISTINCT symbol 
             FROM trading_symbols 
             WHERE is_active = 1";
             
    $result = $db->query($query);
    
    if (!$result) {
        throw new Exception("Fehler beim Laden der Symbole");
    }
    
    // Hole API Credentials
    $credentialsQuery = "SELECT * 
                        FROM api_credentials 
                        WHERE platform = 'bitget' 
                        AND is_active = 1 
                        ORDER BY last_used DESC 
                        LIMIT 1";
                        
    $credentialsResult = $db->query($credentialsQuery);
    
    if (!$credentialsResult || $credentialsResult->num_rows === 0) {
        throw new Exception("Keine aktiven API-Zugangsdaten gefunden");
    }
    
    $credentials = $credentialsResult->fetch_assoc();
    
    // Initialisiere BitGet API
    $bitget = new BitgetTrading(
        $credentials['api_key'],
        $credentials['api_secret'],
        $credentials['api_passphrase']
    );
    
    // Initialisiere MarketData
    $marketData = new MarketData($db, $bitget);
    
    // Sammle Daten für jedes Symbol
    while ($row = $result->fetch_assoc()) {
        try {
            $symbol = $row['symbol'];
            $marketData->collectData($symbol);
            
            echo "Marktdaten für {$symbol} erfolgreich gesammelt\n";
            
        } catch (Exception $e) {
            logError("Fehler bei der Datensammlung für Symbol", [
                'symbol' => $symbol,
                'error' => $e->getMessage()
            ]);
            
            echo "Fehler bei {$symbol}: " . $e->getMessage() . "\n";
            continue;
        }
    }
    
    // Aktualisiere last_used Timestamp
    $updateQuery = "UPDATE api_credentials 
                   SET last_used = NOW() 
                   WHERE id = ?";
                   
    $stmt = $db->prepare($updateQuery);
    $stmt->bind_param('i', $credentials['id']);
    $stmt->execute();
    
} catch (Exception $e) {
    logError("Kritischer Fehler bei der Marktdatensammlung", [
        'error' => $e->getMessage()
    ]);
    
    echo "Kritischer Fehler: " . $e->getMessage() . "\n";
    exit(1);
}
