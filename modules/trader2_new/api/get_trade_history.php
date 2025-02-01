<?php
require_once(__DIR__ . '/../config/t_config.php');

header('Content-Type: application/json');

try {
    // Hole die letzten 100 geschlossenen Trades
    $query = "SELECT 
                t.*,
                o.symbol,
                o.side,
                o.size,
                o.entry_price,
                o.leverage,
                t.exit_price,
                t.realized_pnl,
                t.closed_at
             FROM trades t
             JOIN orders o ON t.order_id = o.order_id
             WHERE t.status = 'closed'
             ORDER BY t.closed_at DESC
             LIMIT 100";
             
    $result = $db->query($query);
    
    if (!$result) {
        throw new Exception("Fehler beim Laden der Trading-Historie");
    }
    
    $trades = [];
    while ($row = $result->fetch_assoc()) {
        // Berechne zusätzliche Metriken
        $entryPrice = floatval($row['entry_price']);
        $exitPrice = floatval($row['exit_price']);
        $size = floatval($row['size']);
        $leverage = intval($row['leverage']);
        
        // Berechne ROE (Return on Equity)
        $roe = ($row['side'] === 'buy')
            ? ($exitPrice - $entryPrice) / $entryPrice * 100 * $leverage
            : ($entryPrice - $exitPrice) / $entryPrice * 100 * $leverage;
        
        $trades[] = [
            'symbol' => $row['symbol'],
            'side' => $row['side'],
            'size' => $size,
            'leverage' => $leverage,
            'entry_price' => $entryPrice,
            'exit_price' => $exitPrice,
            'realized_pnl' => floatval($row['realized_pnl']),
            'roe' => $roe,
            'closed_at' => $row['closed_at']
        ];
    }
    
    // Berechne Performance-Metriken
    $totalTrades = count($trades);
    $profitableTrades = 0;
    $totalPnl = 0;
    $winRate = 0;
    $averageRoe = 0;
    
    if ($totalTrades > 0) {
        foreach ($trades as $trade) {
            if ($trade['realized_pnl'] > 0) {
                $profitableTrades++;
            }
            $totalPnl += $trade['realized_pnl'];
            $averageRoe += $trade['roe'];
        }
        
        $winRate = ($profitableTrades / $totalTrades) * 100;
        $averageRoe = $averageRoe / $totalTrades;
    }
    
    echo json_encode([
        'success' => true,
        'trades' => $trades,
        'metrics' => [
            'total_trades' => $totalTrades,
            'profitable_trades' => $profitableTrades,
            'win_rate' => round($winRate, 2),
            'total_pnl' => round($totalPnl, 2),
            'average_roe' => round($averageRoe, 2)
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
