<?php
include (__DIR__ . '/../config.php');
include (__DIR__ . '/../functions.php');

$clientId = $_SESSION['client_id'];
$array = getBrokerUserByClientId($db, $clientId);
$accountId = $array['account'];
$positiveMultiplier = $array['positive_multiplier'];
$negativeMultiplier = $array['negative_multiplier'];

// Datenbankverbindung und Funktion getSqlForHoursByAccount() hier

// Überprüfe, ob es sich um eine AJAX-Anfrage handelt
if (isset($_POST['timeFrame']) && isset($_POST['accountId'])) {
    $timeFrame = $_POST['timeFrame'];
    $accountId = $_POST['accountId'];
    $sql = '';

    // Entscheide, welche Funktion basierend auf dem gewählten Zeitrahmen aufgerufen werden soll
    switch ($timeFrame) {
        case 'hours':
            $sql = getSqlForHoursByAccount($accountId, 24, $positiveMultiplier, $negativeMultiplier);
            break;
        case 'days':
            $sql = getSqlForDaysByAccount($accountId, 7, $positiveMultiplier, $negativeMultiplier);
            break;
        case 'weeks':
            $sql = getSqlForWeeksByAccount($accountId, 4, $positiveMultiplier, $negativeMultiplier);
            break;
        case 'months':
            $sql = getSqlForMonthsByAccount($accountId, 6, $positiveMultiplier, $negativeMultiplier);
            break;
    }


    $result = $connection->query($sql);
    $chartData = [];

    while ($row = $result->fetch_assoc()) {
        $chartData[] = $row;
    }

    // Schließe die Datenbankverbindung
    $connection->close();

    // Sende die Daten zurück zum Client
    echo json_encode($chartData);
    exit;
}


function getSqlForHoursByAccount($accountId, $hours = 24, $positiveMultiplier, $negativeMultiplier)
{
    global $exclusionClause;

    // Initialize the hours subquery
    $hoursSubQuery = "";
    for ($i = 0; $i < $hours; $i++) {
        $hoursSubQuery .= "UNION ALL SELECT " . $i . " AS hour ";
    }
    $hoursSubQuery = trim($hoursSubQuery, "UNION ALL");

    // Construct the full SQL query
    $sql = "SELECT hour AS label, 
            CASE
                    WHEN SUM(hourly_profit) > 0 THEN SUM(hourly_profit) * $positiveMultiplier
                    ELSE SUM(hourly_profit) * $positiveMultiplier
                END AS profit
            FROM (
                SELECT HOUR(FROM_UNIXTIME(o.time)) AS hour, o.profit AS hourly_profit 
                FROM ssi_trader.orders AS o 
                LEFT JOIN ssi_trader.broker AS b ON o.broker_id = b.broker_id 
                WHERE o.account = '$accountId' AND  trash = '' AND $exclusionClause AND  o.time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL $hours HOUR) 
                UNION ALL 
                SELECT hour, 0 AS hourly_profit 
                FROM (SELECT hour FROM ({$hoursSubQuery}) AS subquery) AS a
            ) AS combined 
            GROUP BY hour 
            ORDER BY hour ASC";
    return $sql;
}


function getSqlForDaysByAccount($accountId, $days, $positiveMultiplier, $negativeMultiplier)
{

    global $exclusionClause;

    // Erstelle SQL-Abfrage
    $daysSubQuery = "";
    for ($i = 0; $i < $days; $i++) {
        $daysSubQuery .= "UNION ALL SELECT " . $i . " AS day ";
    }
    $daysSubQuery = trim($daysSubQuery, "UNION ALL");

    $sql = "SELECT
                DATE_FORMAT(FROM_UNIXTIME(day), '%W, %d.%m') AS label,
                CASE
                    WHEN SUM(daily_profit) > 0 THEN SUM(daily_profit) * $positiveMultiplier
                    ELSE SUM(daily_profit) * $negativeMultiplier
                END AS profit
            FROM (
                SELECT UNIX_TIMESTAMP(DATE(FROM_UNIXTIME(o.time))) AS day, o.profit AS daily_profit
                FROM ssi_trader.orders AS o
                WHERE o.account = '$accountId' AND  trash = '' AND $exclusionClause
                AND o.time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL {$days} DAY)
            UNION ALL
                SELECT UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL tmp.day DAY)) AS day, 0 AS daily_profit
                FROM (SELECT day FROM ({$daysSubQuery}) AS subquery) AS tmp
            ) AS combined
            GROUP BY day
            ORDER BY day ASC";
    return $sql;
}

function getSqlForWeeksByAccount($accountId, $numWeeks, $positiveMultiplier, $negativeMultiplier)
{
    global $exclusionClause;

    // Initialisiere die Subquery für die Wochen
    $weeksSubQuery = "SELECT DATE_SUB(CURDATE(), INTERVAL a.week WEEK) AS first_day_of_week
                      FROM (";

    for ($i = 0; $i < $numWeeks; $i++) {
        $weeksSubQuery .= "SELECT $i AS week UNION ALL ";
    }

    $weeksSubQuery = rtrim($weeksSubQuery, ' UNION ALL');
    $weeksSubQuery .= ") AS a";

    // Konstruiere die vollständige SQL-Abfrage
    $sql = "SELECT CONCAT('KW ', WEEK(combined.day, 3)) AS label,
                   CASE
                       WHEN SUM(combined.profit) > 0 THEN SUM(combined.profit) * $positiveMultiplier
                       ELSE SUM(combined.profit) * $negativeMultiplier
                   END AS profit
            FROM (
                SELECT DATE(FROM_UNIXTIME(o.time)) AS day, o.profit
                FROM orders AS o
                LEFT JOIN ssi_trader.broker AS b ON o.broker_id = b.broker_id
                WHERE o.account = '$accountId'
                  AND trash = ''
                  AND $exclusionClause
                  AND o.time >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL $numWeeks WEEK))
                UNION ALL
                SELECT first_day_of_week AS day, 0 AS profit
                FROM ($weeksSubQuery) AS w
            ) AS combined
            GROUP BY WEEK(combined.day, 3)
            ORDER BY WEEK(combined.day, 3) ASC";

    return $sql;
}

function getSqlForMonthsByAccount($accountId, $months = 6, $positiveMultiplier, $negativeMultiplier)
{

    global $exclusionClause;

    // Initialize the months subquery to generate the first day of each month for the last 'n' months
    $monthsSubQuery = "SELECT DATE_SUB(CURDATE(), INTERVAL a.month MONTH) AS first_day_of_month
                       FROM (";
    for ($i = 0; $i < $months; $i++) {
        $monthsSubQuery .= "SELECT $i AS month UNION ALL ";
    }
    $monthsSubQuery = rtrim($monthsSubQuery, ' UNION ALL');
    $monthsSubQuery .= ") AS a";

    // Construct the full SQL query
    $sql = "SELECT CONCAT(MONTHNAME(combined.day), ' ', YEAR(combined.day)) AS label,
                CASE
                    WHEN SUM(combined.profit) > 0 THEN SUM(combined.profit) * $positiveMultiplier
                    ELSE SUM(combined.profit) * $negativeMultiplier
                END AS profit
            FROM (
                SELECT DATE(FROM_UNIXTIME(o.time)) AS day, o.profit
                FROM orders AS o
                LEFT JOIN ssi_trader.broker AS b ON o.broker_id = b.broker_id
                WHERE o.account = '$accountId' 
                AND  trash = '' AND $exclusionClause
                AND o.time >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL $months MONTH))
                UNION ALL
                SELECT first_day_of_month AS day, 0 AS profit
                FROM ($monthsSubQuery) AS m
            ) AS combined
            GROUP BY YEAR(combined.day), MONTH(combined.day)
            ORDER BY YEAR(combined.day) ASC, MONTH(combined.day) ASC";

    return $sql;
}
