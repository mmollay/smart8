<?php
// api/get_analysis.php
header('Content-Type: application/json');
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../classes/BitgetTrading.php');
require_once(__DIR__ . '/../classes/MarketAnalysis.php');

try {
    // API credentials holen
    $query = "SELECT api_key, api_secret, api_passphrase FROM api_credentials 
              WHERE platform = 'Bitget' AND is_active = 1 
              ORDER BY id DESC LIMIT 1";
    $result = $db->query($query);

    if (!$result || $result->num_rows === 0) {
        throw new Exception('No API credentials found');
    }

    $credentials = $result->fetch_assoc();

    // Trading instance erstellen
    $trading = new BitgetTrading(
        $credentials['api_key'],
        $credentials['api_secret'],
        $credentials['api_passphrase']
    );

    // Market Analysis instance erstellen
    $analysis = new MarketAnalysis($db, $trading);

    // Analyse durchführen
    $recommendation = $analysis->analyze();

    // Letzte Signale holen
    $lastSignals = $analysis->getLastSignals(5);

    echo json_encode([
        'success' => true,
        'recommendation' => $recommendation,
        'historical_signals' => $lastSignals
    ]);

} catch (Exception $e) {
    error_log('Error in get_analysis.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}