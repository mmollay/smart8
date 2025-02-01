<?php
// collect_market_data.php
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../classes/BitgetTrading.php');
require_once(__DIR__ . '/../classes/MarketAnalysis.php');

// Logging-Funktion
function logMessage($message)
{
    $timestamp = date('Y-m-d H:i:s');
    echo "$timestamp - $message\n";
    error_log("$timestamp - $message");
}

try {
    // API-Credentials aus der Datenbank holen
    $query = "SELECT api_key, api_secret, api_passphrase FROM api_credentials 
              WHERE platform = 'Bitget' AND is_active = 1 
              ORDER BY id DESC LIMIT 1";
    $result = $db->query($query);

    if (!$result || $result->num_rows === 0) {
        throw new Exception('Keine aktiven API-Credentials gefunden');
    }

    $credentials = $result->fetch_assoc();

    // Trading-Instanz erstellen
    $trading = new BitgetTrading(
        $credentials['api_key'],
        $credentials['api_secret'],
        $credentials['api_passphrase']
    );

    // Symbol festlegen
    $symbol = 'ETHUSDT_UMCBL';

    // Marktdaten abrufen
    $klineData = $trading->getKlines($symbol);

    if (!isset($klineData['data']) || empty($klineData['data'])) {
        throw new Exception('Keine Kline-Daten empfangen');
    }

    // MarketAnalysis-Instanz erstellen
    $analysis = new MarketAnalysis($db, $trading);

    // Analyse durchführen
    $analysisResult = $analysis->analyze();

    // Prepared Statement für Market Data
    $stmt = $db->prepare("INSERT INTO market_data (
        symbol, timestamp, price, volume, rsi, ema20, ema50
    ) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception('Fehler beim Erstellen des Market Data Statements: ' . $db->error);
    }

    // Aktuelle Marktdaten speichern
    $currentPrice = $klineData['currentPrice'];
    $timestamp = time() * 1000; // Millisekunden
    $volume = 0; // Optional: Volumen aus API holen

    $stmt->bind_param(
        'siddddd',
        $symbol,
        $timestamp,
        $currentPrice,
        $volume,
        $analysisResult['analysis']['rsi'],
        $analysisResult['analysis']['ema20'],
        $analysisResult['analysis']['ema50']
    );

    if (!$stmt->execute()) {
        throw new Exception('Fehler beim Speichern der Marktdaten: ' . $stmt->error);
    }

    // Prepared Statement für Analyse-Signale
    $signalStmt = $db->prepare("INSERT INTO analysis_signals (
        symbol, timestamp, action, confidence, entry_price, tp_price, sl_price, reasoning
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$signalStmt) {
        throw new Exception('Fehler beim Erstellen des Signal Statements: ' . $db->error);
    }

    // Signal validieren und speichern
    $action = $analysisResult['action'];
    $validActions = ['buy', 'sell', 'hold'];
    if (!in_array($action, $validActions)) {
        $action = 'hold';
    }

    $reasoning = json_encode($analysisResult['reasoning']);

    $signalStmt->bind_param(
        'sisdddds',
        $symbol,
        $timestamp,
        $action,
        $analysisResult['confidence'],
        $analysisResult['entry_price'],
        $analysisResult['tp_price'],
        $analysisResult['sl_price'],
        $reasoning
    );

    if (!$signalStmt->execute()) {
        throw new Exception('Fehler beim Speichern des Signals: ' . $signalStmt->error);
    }

    logMessage('Marktdaten und Signale erfolgreich gespeichert');

} catch (Exception $e) {
    logMessage('Fehler bei der Datenerfassung: ' . $e->getMessage());
    exit(1);
}