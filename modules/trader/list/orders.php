<?php

// Initialize the array with the "Current Year" filter
$array_filter_time_periods = [
    'YEAR(FROM_UNIXTIME(time)) = ' . date('Y') => 'Current Year',
];
// Existing filters for specific time periods
$array_filter_time_periods += [
    'DATE(FROM_UNIXTIME(time)) = CURDATE()' => 'Today',
    'DATE(FROM_UNIXTIME(time)) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)' => 'Yesterday',
    'DATE(FROM_UNIXTIME(time)) = DATE_SUB(CURDATE(), INTERVAL 2 DAY)' => 'Day Before Yesterday',
    'YEARWEEK(FROM_UNIXTIME(time), 1) = YEARWEEK(CURDATE(), 1) - 1' => 'Last Week',
    'MONTH(FROM_UNIXTIME(time)) = MONTH(CURDATE()) - 1 AND YEAR(FROM_UNIXTIME(time)) = YEAR(CURDATE())' => 'Last Month',
];

// Add filters for the last six months, including the year
for ($i = 1; $i <= 6; $i++) {
    $monthYear = date('F Y', strtotime("-$i month"));
    $month = date('m', strtotime("-$i month"));
    $year = date('Y', strtotime("-$i month"));

    // Generate the SQL condition
    $condition = "MONTH(FROM_UNIXTIME(time)) = $month AND YEAR(FROM_UNIXTIME(time)) = $year";

    // Add month and year to the display text
    $array_filter_time_periods[$condition] = $monthYear;
}

$arr['mysql'] = [
    'table' => 'ssi_trader.orders AS o 
        LEFT JOIN ssi_trader.symbols AS s ON o.symbol_id = s.symbol_id
        LEFT JOIN ssi_trader.broker AS b ON o.account = b.user
        LEFT JOIN ssi_trader.clients AS c ON o.account = c.account
        ', // Alias 'o' für die Tabelle orders
    'field' => "o.ticket, o.order_id, o.time, o.time_msc, o.type type,  o.magic, o.position_id, o.reason, lotgroup_id, o.server_id, o.account, b.title AS broker_name,
                CONCAT(c.first_name, ' ', c.last_name) AS client_name,
                    CASE 
                    WHEN COUNT(*) = 1 THEN ROUND(o.volume, 2)
                    WHEN COUNT(*) = 2 THEN ROUND(o.volume, 2)
                    ELSE ROUND(SUM(o.volume) / 2, 2)
                END AS volume,
                MAX(o.entry) entry,
                CEIL(COUNT(*) / 2) AS level,
                MIN(o.price) AS min_price, 
                MAX(o.price) AS max_price, 
                o.commission, o.swap, SUM(o.profit) profit, o.fee, o.symbol_id, 
                MIN(FROM_UNIXTIME(o.time)) AS entry_time, 
                MAX(FROM_UNIXTIME(o.time)) AS exit_time, 
                FROM_UNIXTIME(o.time) AS readable_time, 
                s.symbol, trash, o.strategy", // Hinzufügen von s.symbol_name zur Feldliste
    'order' => 'o.time DESC', // Sortierung nach 'time' in orders
    'like' => 'lotgroup_id,position_id, o.account',
    'limit' => 100,
    'group' => 'o.lotgroup_id', // Gruppierung nach 'ticket' in orders
    //'group' => 'o.order_id', // Gruppierung nach 'order_id' in orders

    //'group' => "DATE_FORMAT(FROM_UNIXTIME(o.time), '%Y-%m')"
    // 'debug' => true,
    'where' => "AND trash = '' AND $exclusionClause " // Hinzufügen einer Bedingung für 'entry' in orders
];


$array_filter_broker = getBrokerClientList($connection);

//$arr['filter']['select_year'] = array('type' => 'dropdown', 'array' => $yearsDropdownArray, 'placeholder' => 'Alle Jahre', 'query' => "{value}");
//$arr['filter']['select_month'] = array('type' => 'dropdown', 'query' => "{value}", 'array' => $array_filter_month, 'placeholder' => '--Alle Monate--');
$arr['filter']['select_day'] = array('type' => 'dropdown', 'query' => "{value}", 'array' => $array_filter_time_periods, 'placeholder' => '--Timer period--', 'default_value' => 'YEAR(FROM_UNIXTIME(time)) = ' . date('Y'));
//$arr['filter']['server_id'] = array('type' => 'text', 'placeholder' => 'All Servers', 'array' => $array_filter_server);
//broker_id
$arr['filter']['account'] = array('type' => 'text', 'placeholder' => 'Clients/Brokers', 'array' => $array_filter_broker, 'table' => 'o');

$arr['order'] = array('default' => 'time desc', 'array' => array('order_id desc' => 'Order ID', 'position_id desc, order_id desc' => 'Position', 'lotgroup_id desc, position_id desc, order_id desc' => 'Lot Group desc', 'time desc' => 'Time desc', 'profit desc' => 'Profit desc', 'level desc' => 'Hegde Count'));
//group
$arr['group'] = array(
    'default' => 'order_id',
    'array' => array(
        'order_id' => 'Group by oder_id',
        'position_id' => 'Position',
        'strategy' => 'Strategy',
        'lotgroup_id' => 'Hedges',
        'profit' => 'Profit',
        'o.account' => 'Accounts',
        'server_id' => 'Server',
        // 'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y-%m-%d")' => 'Days',
        // 'YEARWEEK(FROM_UNIXTIME(o.time), 1)' => 'Weeks',
        // 'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y-%m")' => 'Months',
        // 'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y")' => 'Years',
    ),
    'default_value' => 'lotgroup_id'
);

$arr['list'] = [
    'id' => 'orders',
    'width' => '1300px',
    'size' => 'small',
    'class' => 'compact celled striped'
];

// Hinzufügen von Spaltenüberschriften basierend auf der Tabelle `orders`
$arr['th']['order_id'] = ['title' => "Order ID", 'width' => '100px'];
//$arr['th']['position_id'] = ['title' => "Position ID", 'width' => '100px'];
//$arr['th']['symbol'] = ['title' => "Symbol ID", 'width' => '100px'];
//$arr['th']['server_id'] = ['title' => "Server_ID", 'width' => '30px'];
$arr['th']['min_price'] = ['title' => "Min Price", 'format' => 'number_color'];
$arr['th']['max_price'] = ['title' => "Max Price", 'format' => 'number_color'];
$arr['th']['type'] = ['title' => "Type", 'replace' => array('default' => '', '1' => "<span class='ui blue text'>buy</span>", '0' => "<span class='ui red text'>sell</span>"), 'align' => 'center', 'width' => '50px'];
$arr['th']['entry'] = ['title' => "Entry", 'format' => 'number', 'align' => 'right', 'width' => '50px'];
$arr['th']['volume'] = ['title' => "Volume"];
$arr['th']['account'] = ['title' => "Account",];
$arr['th']['broker_name'] = ['title' => "Broker"];
//$arr['th']['client_name'] = ['title' => "Client"];
$arr['th']['strategy'] = ['title' => "Strategy"];
//$arr['th']['entry_time'] = ['title' => "Entry Time"];
$arr['th']['level'] = ['title' => "Hedge Count"];
$arr['th']['time'] = ['title' => "Readable Time"];
$arr['th']['exit_time'] = ['title' => "Exit Time", 'width' => '150px'];
//$arr['th']['lotgroup_id'] = ['title' => "Lotgroup ID", 'width' => '100px'];
$arr['th']['profit'] = ['title' => "Profit", 'format' => 'number_redblue', 'align' => 'right', 'total' => true, 'width' => '120px'];
//$arr['th']['trash'] = ['title' => "Trash"];

// $arr['th']['readable_time_msc'] = ['title' => "Readable Time (msc)", 'align' => 'center'];
// $arr['th']['time'] = ['title' => "Time", 'align' => 'center'];
// $arr['th']['time_msc'] = ['title' => "Time (msc)", 'align' => 'center'];
// $arr['th']['magic'] = ['title' => "Magic"];
// $arr['th']['reason'] = ['title' => "Reason"];
// $arr['th']['commission'] = ['title' => "Commission"];
//$arr['th']['swap'] = ['title' => "Swap"];
//$arr['th']['ticket'] = ['title' => "Ticket"];
// $arr['th']['fee'] = ['title' => "Fee"];

// $arr['checkbox'] = array('title' => 'ID', 'label' => '', 'align' => 'center');
// $arr['checkbox']['buttons'] = array('class' => 'tiny');
// $arr['checkbox']['button']['delete'] = array('title' => 'Delete', 'icon' => 'delete', 'class' => 'red mini');

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '', 'icon' => 'list', 'class' => 'blue', 'popup' => 'Details');

$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash', 'popup' => 'Delete', 'class' => '');

$arr['modal']['modal_form'] = array('title' => 'Edit', 'class' => '', 'url' => 'form_edit.php');
$arr['modal']['modal_form']['button']['cancel'] = array('title' => 'Schließen', 'color' => 'grey', 'icon' => 'close');

$arr['modal']['modal_form_delete'] = array('title' => 'Remove', 'class' => 'small', 'url' => 'form_delete.php');

function getUniqueYearsForDropdown($connection)
{
    $yearsArray = [];

    // SQL-Abfrage vorbereiten
    $query = "SELECT DISTINCT YEAR(FROM_UNIXTIME(time)) AS year FROM orders ORDER BY year DESC";

    // Führe die Abfrage aus
    $result = $connection->query($query);

    // Überprüfe, ob die Abfrage erfolgreich war
    if ($result) {
        // Hole alle einzigartigen Jahre und fülle das Array
        while ($row = $result->fetch_assoc()) {
            // Der Schlüssel und Wert sind hier gleich, da keine spezifische Bedingung benötigt wird
            $yearsArray["YEAR(FROM_UNIXTIME(time)) = " . $row['year']] = $row['year'];
        }

        // Gib das Ergebnis frei
        $result->free();
    } else {
        // Fehlerbehandlung
        echo "Fehler bei der Abfrage: " . $connection->error;
    }

    return $yearsArray;
}

function getWeeksArray()
{
    $weeksArray = [];
    $currentTimestamp = time(); // Aktueller Zeitpunkt

    for ($i = 0; $i <= 5; $i++) {
        // Berechne den Start- und Endzeitpunkt jeder Woche
        $startOfWeek = strtotime("Monday this week - $i week");
        $endOfWeek = strtotime("Sunday this week - $i week");

        // Formatierung für die Anzeige
        $display = date('Y-m-d', $startOfWeek) . " bis " . date('Y-m-d', $endOfWeek);

        // Formatierung für die SQL-Abfrage
        $condition = "time >= " . $startOfWeek . " AND time <= " . $endOfWeek;

        // Füge den Eintrag zum Array hinzu
        $weeksArray[$condition] = $display;
    }

    return $weeksArray;
}

function getWeeksFilterArray()
{
    $weeksArray = [];
    $currentWeekNumber = date('W');
    $currentYear = date('Y');

    for ($i = 0; $i <= 6; $i++) {
        // Berechne die Woche und das Jahr, falls die Woche ins vorherige Jahr fällt
        $weekNumber = $currentWeekNumber - $i;
        $year = $currentYear;
        if ($weekNumber <= 0) {
            $weekNumber += 52;
            $year -= 1;
        }

        // Erstelle die SQL-Bedingung
        $condition = sprintf("YEAR(FROM_UNIXTIME(time)) = %d AND WEEK(FROM_UNIXTIME(time), 1) = %d", $year, $weekNumber);

        // Berechne den lesbaren Zeitraum für die Anzeige
        $startOfWeek = strtotime($year . "W" . str_pad($weekNumber, 2, '0', STR_PAD_LEFT));
        $endOfWeek = strtotime("+6 days", $startOfWeek);
        $display = "Woche vom " . date('Y-m-d', $startOfWeek) . " bis " . date('Y-m-d', $endOfWeek);

        // Füge den Eintrag zum Array hinzu
        $weeksArray[$condition] = $display;
    }

    return $weeksArray;
}

function getServerFilterArray($connection)
{
    $serverArray = [];

    // SQL-Abfrage vorbereiten mit einem JOIN zu ssi_trader.orders
    // Annahme: Es gibt eine Spalte in ssi_trader.orders, die server_id heißt und auf servers.server_id verweist.
    $query = "SELECT DISTINCT servers.server_id, servers.name 
              FROM servers 
              JOIN ssi_trader.orders ON servers.server_id = ssi_trader.orders.server_id 
              ORDER BY servers.name";

    // Führe die Abfrage aus
    $result = $connection->query($query);

    // Überprüfe, ob die Abfrage erfolgreich war
    if ($result) {
        // Hole alle Server und fülle das Array
        while ($row = $result->fetch_assoc()) {
            $serverArray[$row['server_id']] = $row['name'];
        }

        // Gib das Ergebnis frei
        $result->free();
    } else {
        // Fehlerbehandlung
        echo "Fehler bei der Abfrage: " . $connection->error;
    }

    return $serverArray;
}

function getBrokerFilterArray($connection)
{
    $brokerArray = [];

    // SQL-Abfrage vorbereiten mit einem JOIN zu ssi_trader.orders
    // Annahme: Es gibt eine Spalte in ssi_trader.orders, die broker_id heißt und auf broker.broker_id verweist.
    $query = "SELECT DISTINCT broker.broker_id, CONCAT (broker.broker_server, ' - ', broker.title) AS name
              FROM broker 
              JOIN ssi_trader.orders ON broker.broker_id = ssi_trader.orders.broker_id 
              ORDER BY broker.title";

    // Führe die Abfrage aus
    $result = $connection->query($query);

    // Überprüfe, ob die Abfrage erfolgreich war
    if ($result) {
        // Hole alle Broker und fülle das Array
        while ($row = $result->fetch_assoc()) {
            $brokerArray[$row['broker_id']] = $row['name'];
        }

        // Gib das Ergebnis frei
        $result->free();
    } else {
        // Fehlerbehandlung
        echo "Fehler bei der Abfrage: " . $connection->error;
    }

    return $brokerArray;
}

function getBrokerClientList($connection)
{
    $brokerArray = [];

    // Adjusted query to include logic for 'real_account' check
    $query = "SELECT 
                broker.user, 
                CONCAT(IF(broker.real_account = 1, 'Live - ', 'Demo - '),
                broker.user,' - ',broker.title) AS broker_name,    
                CONCAT(clients.first_name, ' ', clients.last_name) AS client_name
              FROM 
                broker 
              LEFT JOIN 
                clients ON broker.user = clients.account
              ORDER BY 
                broker.real_account, broker.title, clients.first_name, clients.last_name";

    // Execute the query
    $result = $connection->query($query);

    // Check if the query was successful
    if ($result) {
        // Fetch all entries and populate the array
        while ($row = $result->fetch_assoc()) {
            // Use 'user' as the key and combine 'broker_name' with 'client_name' (if available) as the value
            $name = $row['broker_name'];
            if (!empty($row['client_name'])) {
                $name .= ' - ' . $row['client_name'];
            }
            $brokerArray[$row['user']] = $name;
        }

        // Free the result
        $result->free();
    } else {
        // Error handling
        echo "Fehler bei der Abfrage: " . $connection->error;
    }

    return $brokerArray;
}


function getClientFilterArray($connection)
{
    $clientArray = [];

    // SQL-Abfrage vorbereiten mit einem JOIN zu ssi_trader.orders
    // Die Verbindung erfolgt über das Feld 'account' in beiden Tabellen.
    $query = "SELECT DISTINCT c.account, CONCAT(c.first_name, ' ', c.last_name) AS name
              FROM ssi_trader.clients c
              JOIN ssi_trader.orders o ON c.account = o.account 
              ORDER BY c.first_name, c.last_name";

    // Führe die Abfrage aus
    $result = $connection->query($query);

    // Überprüfe, ob die Abfrage erfolgreich war
    if ($result) {
        // Hole alle Clients und fülle das Array, wobei `account` als Schlüssel verwendet wird und der zusammengesetzte Name als Wert
        while ($row = $result->fetch_assoc()) {
            $clientArray[$row['account']] = $row['name'];
        }

        // Gib das Ergebnis frei
        $result->free();
    } else {
        // Fehlerbehandlung
        echo "Fehler bei der Abfrage: " . $connection->error;
    }

    return $clientArray;
}

function mergeFilterArrays($array1, $array2)
{
    $mergedArray = [];

    // Schleife durch das erste Array und füge es zum Ergebnis hinzu
    foreach ($array1 as $key => $value) {
        if (!isset($mergedArray[$key])) {
            // Wenn der Schlüssel im Ergebnisarray noch nicht existiert, einfach hinzufügen
            $mergedArray[$key] = $value;
        } else {
            // Wenn der Schlüssel bereits existiert und noch kein Array ist, konvertiere in ein Array
            if (!is_array($mergedArray[$key])) {
                $mergedArray[$key] = [$mergedArray[$key]];
            }
            $mergedArray[$key][] = $value;
        }
    }

    // Schleife durch das zweite Array und füge es zum Ergebnis hinzu
    foreach ($array2 as $key => $value) {
        if (!isset($mergedArray[$key])) {
            // Wenn der Schlüssel im Ergebnisarray noch nicht existiert, einfach hinzufügen
            $mergedArray[$key] = $value;
        } else {
            // Wenn der Schlüssel bereits existiert und noch kein Array ist, konvertiere in ein Array
            if (!is_array($mergedArray[$key])) {
                $mergedArray[$key] = [$mergedArray[$key]];
            }
            $mergedArray[$key][] = $value;
        }
    }

    return $mergedArray;
}
