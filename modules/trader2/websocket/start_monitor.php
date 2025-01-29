<?php
require __DIR__ . '/order_monitor.php';

if ($argc < 3) {
    die("Usage: php start_monitor.php <user_id> <order_id>\n");
}

$user_id = (int)$argv[1];
$order_id = (int)$argv[2];

// Order Details laden
$stmt = $GLOBALS['db']->prepare("
    SELECT bitget_order_id, tp_order_id, sl_order_id 
    FROM orders 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Order nicht gefunden\n");
}

// Monitor starten
try {
    $monitor = new OrderMonitor($user_id);
    $monitor->monitor_order(
        $order['bitget_order_id'],
        $order['tp_order_id'],
        $order['sl_order_id']
    );
    
    // PID in temporärer Datei speichern
    $pid = getmypid();
    file_put_contents(
        sys_get_temp_dir() . "/bitget_monitor_{$order_id}.pid",
        $pid
    );
    
    // Monitor starten
    $monitor->start();
    
} catch (Exception $e) {
    error_log("Monitor Error: " . $e->getMessage());
    die("Monitor Error: " . $e->getMessage() . "\n");
}
