<?php
require_once(__DIR__ . '/../config/t_config.php');
require_once(__DIR__ . '/../classes/SignalGenerator.php');
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
    
    // Initialisiere MarketData
    $marketData = new MarketData($db);
    
    // Initialisiere SignalGenerator
    $signalGenerator = new SignalGenerator($db, $marketData);
    
    // Generiere Signale für jedes Symbol
    while ($row = $result->fetch_assoc()) {
        try {
            $symbol = $row['symbol'];
            $signals = $signalGenerator->generateSignals($symbol);
            
            echo "Signale für {$symbol} generiert:\n";
            foreach ($signals as $signal) {
                echo "- {$signal['model_name']}: {$signal['action']} " .
                     "({$signal['confidence']}%)\n";
                     
                // Wenn Auto-Trading aktiviert ist, führe Trades aus
                if ($signal['confidence'] >= 80) {
                    try {
                        // Hole API Credentials für das Modell
                        $credQuery = "SELECT ac.*
                                    FROM api_credentials ac
                                    JOIN user_trading_models utm 
                                        ON ac.user_id = utm.user_id
                                    WHERE utm.model_id = ?
                                    AND ac.is_active = 1
                                    AND utm.auto_trade = 1";
                                    
                        $credStmt = $db->prepare($credQuery);
                        $credStmt->bind_param('i', $signal['model_id']);
                        $credStmt->execute();
                        $credResult = $credStmt->get_result();
                        
                        while ($cred = $credResult->fetch_assoc()) {
                            try {
                                // Initialisiere BitGet API
                                $bitget = new BitgetTrading(
                                    $cred['api_key'],
                                    $cred['api_secret'],
                                    $cred['api_passphrase']
                                );
                                
                                // Platziere Order
                                $orderParams = [
                                    'symbol' => $symbol,
                                    'side' => $signal['action'],
                                    'size' => $signal['parameters']['position_size'],
                                    'leverage' => $signal['parameters']['leverage'],
                                    'take_profit_percent' => $signal['parameters']['take_profit_percent'],
                                    'stop_loss_percent' => $signal['parameters']['stop_loss_percent']
                                ];
                                
                                $order = $bitget->placeOrder($orderParams);
                                
                                echo "  Auto-Trade ausgeführt für User {$cred['user_id']}: " .
                                     "Order ID {$order['orderId']}\n";
                                     
                            } catch (Exception $e) {
                                logError("Fehler beim Auto-Trading", [
                                    'error' => $e->getMessage(),
                                    'user_id' => $cred['user_id'],
                                    'signal' => $signal
                                ]);
                                
                                echo "  Fehler beim Auto-Trading für User " .
                                     "{$cred['user_id']}: {$e->getMessage()}\n";
                                continue;
                            }
                        }
                    } catch (Exception $e) {
                        logError("Fehler beim Laden der API Credentials", [
                            'error' => $e->getMessage(),
                            'model_id' => $signal['model_id']
                        ]);
                        continue;
                    }
                }
            }
            
        } catch (Exception $e) {
            logError("Fehler bei der Signalgenerierung", [
                'error' => $e->getMessage(),
                'symbol' => $symbol
            ]);
            
            echo "Fehler bei {$symbol}: " . $e->getMessage() . "\n";
            continue;
        }
    }
    
} catch (Exception $e) {
    logError("Kritischer Fehler bei der Signalgenerierung", [
        'error' => $e->getMessage()
    ]);
    
    echo "Kritischer Fehler: " . $e->getMessage() . "\n";
    exit(1);
}
