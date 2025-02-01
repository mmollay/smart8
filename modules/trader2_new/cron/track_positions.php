<?php
require_once(__DIR__ . '/../config/t_config.php');
require_once(__DIR__ . '/../classes/PositionTracker.php');
require_once(__DIR__ . '/../classes/BitgetTrading.php');

try {
    // Hole aktive API Credentials
    $query = "SELECT * FROM api_credentials 
              WHERE platform = 'bitget' 
              AND is_active = 1";
              
    $result = $db->query($query);
    
    if (!$result) {
        throw new Exception("Fehler beim Laden der API Credentials");
    }
    
    while ($credentials = $result->fetch_assoc()) {
        try {
            // Initialisiere BitGet API
            $bitget = new BitgetTrading(
                $credentials['api_key'],
                $credentials['api_secret'],
                $credentials['api_passphrase']
            );
            
            // Initialisiere Position Tracker
            $tracker = new PositionTracker($db, $bitget);
            
            // Tracke Positionen
            $tracker->trackPositions($credentials['user_id']);
            
            echo "Positionen für User {$credentials['user_id']} aktualisiert\n";
            
        } catch (Exception $e) {
            logError("Fehler beim Position-Tracking", [
                'error' => $e->getMessage(),
                'user_id' => $credentials['user_id']
            ]);
            
            echo "Fehler bei User {$credentials['user_id']}: " . 
                 $e->getMessage() . "\n";
            continue;
        }
    }
    
} catch (Exception $e) {
    logError("Kritischer Fehler beim Position-Tracking", [
        'error' => $e->getMessage()
    ]);
    
    echo "Kritischer Fehler: " . $e->getMessage() . "\n";
    exit(1);
}
