<?php
require_once(__DIR__ . "/../t_config.php");

// Überprüfen, ob die Verbindung erfolgreich war
if ($conn->connect_error) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
}

// Funktion, um Einträge in "orders" zu aktualisieren
function updateOrdersFromStrategyAssignments($conn)
{
    // Alle Einträge aus "strategy_assignments" auslesen
    $sql_strategy_assignments = "SELECT * FROM strategy_assignments ORDER BY timestamp ASC";
    $result_strategy_assignments = $conn->query($sql_strategy_assignments);

    if ($result_strategy_assignments->num_rows > 0) {
        while ($row_strategy_assignments = $result_strategy_assignments->fetch_assoc()) {
            $strategy = $row_strategy_assignments["strategy"];
            $server_id = $row_strategy_assignments["server_id"];
            $timestamp = $row_strategy_assignments["timestamp"];

            // Überprüfe, ob für den Server und Zeitstempel bereits Einträge in "orders" vorhanden sind
            $sql_check_orders = "SELECT COUNT(*) AS count 
                                 FROM orders
                                 WHERE server_id = $server_id
                                 AND time < UNIX_TIMESTAMP('$timestamp')
                                 AND strategy IS NOT NULL";
            $result_check_orders = $conn->query($sql_check_orders);
            $row_check_orders = $result_check_orders->fetch_assoc();

            // Nur wenn für den Server und Zeitstempel noch keine Einträge in "orders" vorhanden sind, aktualisieren
            if ($row_check_orders["count"] == 0) {
                // Alle Einträge aus "orders" mit älterem Zeitstempel und gleicher server_id finden
                $sql_orders = "UPDATE orders
                               SET strategy = '$strategy'
                               WHERE server_id = $server_id
                               AND time < UNIX_TIMESTAMP('$timestamp')
                               AND strategy IS NULL";
                $result_orders = $conn->query($sql_orders);

                if ($result_orders === TRUE) {
                    //echo "$strategy Einträge in 'orders' für server_id $server_id und Timestamp '$timestamp' aktualisiert.\n<br>";
                } else {
                    echo "Fehler beim Aktualisieren von Einträgen in 'orders': " . $conn->error . "\n";
                }
            } else {
                //echo "Für server_id $server_id und Timestamp '$timestamp' wurden bereits Einträge in 'orders' aktualisiert, überspringen.\n<br>";
            }
        }
    } else {
        //echo "Keine Einträge in 'strategy_assignments' gefunden.\n";
    }
}

// Funktion aufrufen
updateOrdersFromStrategyAssignments($conn);

// Datenbankverbindung schließen
$conn->close();
// Funktion aufrufen
updateOrdersFromStrategyAssignments($conn);

// Datenbankverbindung schließen
$conn->close();
