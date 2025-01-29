<?php
require_once(__DIR__ . '/t_config.php');

// Letzte Orders abrufen
$stmt = $db->prepare("
    SELECT o.*, u.username 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.id DESC 
    LIMIT 5
");
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// HTML Ausgabe
?>
<!DOCTYPE html>
<html>
<head>
    <title>Letzte Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
</head>
<body>
    <div class="ui container" style="padding: 20px;">
        <h2 class="ui header">Letzte Orders</h2>
        
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Symbol</th>
                    <th>Side</th>
                    <th>Size</th>
                    <th>Entry Price</th>
                    <th>Status</th>
                    <th>BitGet Order ID</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']) ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td><?= htmlspecialchars($order['symbol']) ?></td>
                    <td><?= htmlspecialchars($order['side']) ?></td>
                    <td><?= htmlspecialchars($order['position_size']) ?></td>
                    <td><?= htmlspecialchars($order['entry_price']) ?></td>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                    <td><?= htmlspecialchars($order['bitget_order_id']) ?></td>
                    <td><?= htmlspecialchars($order['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
