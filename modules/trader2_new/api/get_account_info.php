<?php
require_once(__DIR__ . '/../config/t_config.php');
require_once(__DIR__ . '/../classes/BitgetTrading.php');

header('Content-Type: application/json');

try {
    // Hole API Credentials
    $query = "SELECT * FROM api_credentials 
              WHERE platform = 'bitget' 
              AND is_active = 1 
              ORDER BY last_used DESC 
              LIMIT 1";
              
    $result = $db->query($query);
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception('Keine aktiven API-Zugangsdaten gefunden');
    }
    
    $credentials = $result->fetch_assoc();
    
    // Initialisiere BitGet API
    $bitget = new BitgetTrading(
        $credentials['api_key'],
        $credentials['api_secret'],
        $credentials['api_passphrase']
    );
    
    // Hole Kontoinformationen
    $accountInfo = $bitget->getAccountInfo();
    
    echo json_encode([
        'success' => true,
        'balance' => $accountInfo['totalEquity'],
        'available_balance' => $accountInfo['availableBalance'],
        'unrealized_pnl' => $accountInfo['unrealizedPnL'],
        'margin_ratio' => $accountInfo['marginRatio'],
        'maintenance_margin' => $accountInfo['maintMargin']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
