<?php
require_once(__DIR__ . '/../config/t_config.php');

header('Content-Type: application/json');

try {
    // Parameter validieren
    $symbol = $_GET['symbol'] ?? null;
    $userId = $_GET['user_id'] ?? null;
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $interval = $_GET['interval'] ?? '1h';
    
    // Validiere Interval
    $validIntervals = ['5m', '15m', '1h', '4h', '1d'];
    if (!in_array($interval, $validIntervals)) {
        throw new Exception("Ungültiges Interval");
    }
    
    // Baue Query für Position-Historie
    $query = "SELECT 
                p.id,
                p.symbol,
                p.side,
                p.size,
                p.entry_price,
                MIN(ph.mark_price) as min_price,
                MAX(ph.mark_price) as max_price,
                MIN(ph.unrealized_pnl) as min_pnl,
                MAX(ph.unrealized_pnl) as max_pnl,
                AVG(ph.margin_ratio) as avg_margin_ratio,
                p.leverage,
                p.status,
                p.created_at as opened_at,
                p.closed_at,
                COUNT(DISTINCT ph.id) as data_points
             FROM positions p
             LEFT JOIN position_history ph ON p.id = ph.position_id
             WHERE ph.timestamp BETWEEN ? AND ?";
             
    $params = [$startDate, $endDate];
    $types = "ss";
    
    if ($symbol) {
        $query .= " AND p.symbol = ?";
        $params[] = $symbol;
        $types .= "s";
    }
    
    if ($userId) {
        $query .= " AND p.user_id = ?";
        $params[] = $userId;
        $types .= "i";
    }
    
    // Gruppiere nach Interval
    $query .= " GROUP BY 
                p.id,
                FLOOR(UNIX_TIMESTAMP(ph.timestamp) / 
                CASE ? 
                    WHEN '5m' THEN 300
                    WHEN '15m' THEN 900
                    WHEN '1h' THEN 3600
                    WHEN '4h' THEN 14400
                    ELSE 86400
                END)";
                
    $params[] = $interval;
    $types .= "s";
    
    $query .= " ORDER BY ph.timestamp ASC";
    
    // Führe Query aus
    $stmt = $db->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Verarbeite Ergebnisse
    $positions = [];
    while ($row = $result->fetch_assoc()) {
        // Berechne zusätzliche Metriken
        $duration = strtotime($row['closed_at'] ?? 'now') - 
                   strtotime($row['opened_at']);
                   
        $maxDrawdown = 0;
        $peakValue = 0;
        $currentDrawdown = 0;
        
        if ($row['min_pnl'] !== null && $row['max_pnl'] !== null) {
            $peakValue = $row['max_pnl'];
            $currentDrawdown = ($peakValue - $row['min_pnl']) / abs($peakValue) * 100;
            $maxDrawdown = max($maxDrawdown, $currentDrawdown);
        }
        
        $positions[] = [
            'id' => $row['id'],
            'symbol' => $row['symbol'],
            'side' => $row['side'],
            'size' => floatval($row['size']),
            'entry_price' => floatval($row['entry_price']),
            'price_range' => [
                'min' => floatval($row['min_price']),
                'max' => floatval($row['max_price'])
            ],
            'pnl_range' => [
                'min' => floatval($row['min_pnl']),
                'max' => floatval($row['max_pnl'])
            ],
            'metrics' => [
                'max_drawdown' => round($maxDrawdown, 2),
                'avg_margin_ratio' => round($row['avg_margin_ratio'], 2),
                'duration_hours' => round($duration / 3600, 1),
                'data_points' => (int)$row['data_points']
            ],
            'leverage' => (int)$row['leverage'],
            'status' => $row['status'],
            'dates' => [
                'opened' => $row['opened_at'],
                'closed' => $row['closed_at']
            ]
        ];
    }
    
    // Berechne Zusammenfassung
    $summary = [
        'total_positions' => count($positions),
        'open_positions' => 0,
        'total_pnl' => 0,
        'avg_duration_hours' => 0,
        'max_drawdown' => 0
    ];
    
    foreach ($positions as $position) {
        if ($position['status'] === 'open') {
            $summary['open_positions']++;
        }
        
        $summary['total_pnl'] += $position['pnl_range']['max'];
        $summary['avg_duration_hours'] += $position['metrics']['duration_hours'];
        $summary['max_drawdown'] = max(
            $summary['max_drawdown'],
            $position['metrics']['max_drawdown']
        );
    }
    
    if ($summary['total_positions'] > 0) {
        $summary['avg_duration_hours'] /= $summary['total_positions'];
    }
    
    echo json_encode([
        'success' => true,
        'positions' => $positions,
        'summary' => $summary,
        'query_params' => [
            'symbol' => $symbol,
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'interval' => $interval
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
