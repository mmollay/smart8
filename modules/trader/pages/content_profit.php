<?
include (__DIR__ . "/../t_config.php");

// Verbindungsparameter
$host = 'localhost';
$dbname = 'ssi_trader';
$username = 'smart';
$password = 'Eiddswwenph21;';

// Erstelle PDO-Verbindung
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<div class='ui negative message'>Verbindung fehlgeschlagen: " . $e->getMessage() . "</div>";
    exit;
}


// Alle Clients abrufen
$queryClients = "SELECT c.server_id, c.client_id, c.first_name, c.last_name, s.name AS server_name, s.url AS server_ip 
                 FROM clients c
                 JOIN servers s ON c.server_id = s.server_id
                 ORDER BY c.first_name ";
$stmtClients = $pdo->prepare($queryClients);
$stmtClients->execute();
$clients = $stmtClients->fetchAll(PDO::FETCH_ASSOC);

foreach ($clients as $client) {
    $client_id = $client['client_id'];
    $fullName = htmlspecialchars($client['first_name'] . " " . $client['last_name']);
    $serverName = $client['server_name'];
    $serverIP = $client['server_ip'];

    // Gesamteinzahlung des Clients ermitteln
    $queryDeposits = "SELECT SUM(amount) AS total_deposit FROM deposits WHERE client_id = :client_id";
    $stmt = $pdo->prepare($queryDeposits);
    $stmt->execute(['client_id' => $client_id]);
    $totalDeposit = $stmt->fetchColumn();

    // Gesamtprofit des Clients ermitteln
    $queryProfit = "SELECT SUM(profit) AS total_profit FROM orders WHERE server_id IN (SELECT server_id FROM clients WHERE client_id = :client_id)";
    $stmt = $pdo->prepare($queryProfit);
    $stmt->execute(['client_id' => $client_id]);
    $totalProfit = $stmt->fetchColumn();

    // Gewinnanteil (%) des Clients ermitteln
    $queryProfitShare = "SELECT profit_percentage FROM profit_shares WHERE client_id = :client_id";
    $stmt = $pdo->prepare($queryProfitShare);
    $stmt->execute(['client_id' => $client_id]);
    $profitPercentage = $stmt->fetchColumn();

    // Zeitraum ermitteln: Datum der ersten Order basierend auf UNIX-Zeit
    $queryFirstOrder = "SELECT MIN(FROM_UNIXTIME(o.time)) AS first_order_time 
FROM orders o
WHERE o.server_id = :server_id";
    $stmtFirstOrder = $pdo->prepare($queryFirstOrder);
    $stmtFirstOrder->execute(['server_id' => $client['server_id']]);
    $firstOrderTime = $stmtFirstOrder->fetchColumn();
    $firstOrderDate = $firstOrderTime ? date("Y-m-d", strtotime($firstOrderTime)) : "0000-00-00";

    // Gewinnanteil und verbleibender Gewinn berechnen
    $actualProfitShare = ($totalProfit * $profitPercentage) / 100;
    $remainingProfit = $totalProfit - $actualProfitShare;

    //segment soll keinen rahmen haben  und 800px breit sein
    echo "<div class='ui  message' style='width:800px'>";
    echo "<h3 class='ui header'>$fullName</h3>";
    echo "<table class='ui celled table'>";
    echo "<tbody>";
    echo "<tr><td><strong>Zeitraum der Orders:</strong></td><td>Ab $firstOrderDate</td></tr>";
    echo "<tr><td><strong>Server:</strong></td><td>$serverName ($serverIP)</td></tr>";
    echo "<tr><td><strong>Gesamteinzahlung:</strong></td><td>" . number_format($totalDeposit, 2) . " €</td></tr>";
    echo "<tr><td><strong>Gesamtprofit:</strong></td><td>" . number_format($totalProfit, 2) . " €</td></tr>";
    echo "<tr><td><strong>Gewinnanteil (%):</strong></td><td><b>" . number_format($profitPercentage, 2) . "%</b></td></tr>";
    echo "<tr><td><strong>Tatsächlicher Gewinnanteil:</strong></td><td><b>" . number_format($actualProfitShare, 2) . " €</b></td></tr>";
    echo "<tr><td><strong>Verbleibender Gewinn:</strong></td><td>" . number_format($remainingProfit, 2) . " €</td></tr>";
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
}