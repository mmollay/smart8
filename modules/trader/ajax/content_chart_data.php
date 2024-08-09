<?php
include (__DIR__ . "/../t_config.php");
//include (__DIR__ . '/../../trader_client/functions.php');

// Funktion zur Abfrage der Daten für alle Accounts
function fetchDataForAllAccounts($connection, $timeFrame, $accountType = null)
{
    $accountsData = [];
    if ($accountType === '1') {
        $accountFilter = " AND real_account = 1"; // Real Server
    } elseif ($accountType === '2') {
        $accountFilter = " AND real_account = 0"; // Demo Server
    } else {
        $accountFilter = ""; // Alle Server
    }

    // Hole alle Account-IDs und Titel mit dem neuen Filter
    $accounts = $connection->query("SELECT DISTINCT user, title FROM ssi_trader.broker WHERE 1=1 $accountFilter");

    while ($account = $accounts->fetch_assoc()) {
        $accountId = $account['user'];
        $title = $account['title'];

        switch ($timeFrame) {
            case 'hours':
                $sql = getSqlForHoursByAccount($accountId, 24, $accountFilter);
                break;
            case 'days':
                $sql = getSqlForDaysByAccount($accountId, 7, $accountFilter);
                break;
            case 'weeks':
                $sql = getSqlForWeeksByAccount($accountId, 4, $accountFilter);
                break;
            case 'months':
                $sql = getSqlForMonthsByAccount($accountId, 6, $accountFilter);
                break;
        }

        $result = $connection->query($sql);
        $chartData = [];
        while ($row = $result->fetch_assoc()) {
            $chartData[] = $row;
        }

        $accountsData[] = [
            'accountId' => $accountId,
            'title' => $accountId . " (" . $title . ")",
            'data' => $chartData
        ];
    }
    return $accountsData;
}

if (isset($_POST['timeFrame'])) {
    $timeFrame = $_POST['timeFrame'];
    $accountType = isset($_POST['accountType']) ? $_POST['accountType'] : null;
    $accountsData = fetchDataForAllAccounts($connection, $timeFrame, $accountType);
    echo json_encode($accountsData);
    exit;
}

function getSqlForHoursByAccount($accountId, $hours = 24, $accountFilter = "")
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
            SUM(hourly_profit) AS profit 
            FROM (
                SELECT HOUR(FROM_UNIXTIME(o.time)) AS hour, o.profit AS hourly_profit 
                FROM ssi_trader.orders AS o 
                LEFT JOIN ssi_trader.broker AS b ON o.broker_id = b.broker_id 
                WHERE o.account = '$accountId' AND trash = 0 
                AND $exclusionClause
                AND o.time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL $hours HOUR) 
                $accountFilter 
                UNION ALL 
                SELECT hour, 0 AS hourly_profit 
                FROM (SELECT hour FROM ({$hoursSubQuery}) AS subquery) AS a
            ) AS combined 
            GROUP BY hour 
            ORDER BY hour ASC";
    return $sql;
}


function getSqlForDaysByAccount($accountId, $days, $accountFilter = "")
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
                COALESCE(SUM(daily_profit), 0) AS profit
            FROM (
                SELECT UNIX_TIMESTAMP(DATE(FROM_UNIXTIME(o.time))) AS day, o.profit AS daily_profit
                FROM ssi_trader.orders AS o
                WHERE o.account = '$accountId' AND trash = 0
                AND $exclusionClause
                AND o.time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL {$days} DAY)
            UNION ALL
                SELECT UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL tmp.day DAY)) AS day, 0 AS daily_profit
                FROM (SELECT day FROM ({$daysSubQuery}) AS subquery) AS tmp
            ) AS combined
            GROUP BY day
            ORDER BY day ASC";
    return $sql;
}

function getSqlForWeeksByAccount($accountId, $weeks, $accountFilter = "")
{
    global $exclusionClause;

    // Initialize the weeks subquery to generate the first day of each week for the last 'n' weeks
    $weeksSubQuery = "SELECT DATE_SUB(CURDATE(), INTERVAL a.week WEEK) AS first_day_of_week
                      FROM (";

    for ($i = 0; $i < $weeks; $i++) {
        $weeksSubQuery .= "SELECT $i AS week UNION ALL ";
    }

    $weeksSubQuery = rtrim($weeksSubQuery, ' UNION ALL');
    $weeksSubQuery .= ") AS a";

    // Construct the full SQL query
    $sql = "SELECT CONCAT('KW ', WEEK(combined.day, 1), '/', YEAR(combined.day)) AS label,
                   SUM(combined.profit) AS profit
            FROM (
                SELECT DATE(FROM_UNIXTIME(o.time)) AS day, o.profit AS profit
                FROM orders AS o
                LEFT JOIN ssi_trader.broker AS b ON o.broker_id = b.broker_id
                WHERE o.account = '$accountId'
                  AND $exclusionClause
                  AND trash = 0
                  AND o.time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL $weeks WEEK)
                  $accountFilter
                UNION ALL
                SELECT first_day_of_week AS day, 0 AS profit
                FROM ($weeksSubQuery) AS w
            ) AS combined
            GROUP BY YEAR(combined.day), WEEK(combined.day, 1)
            ORDER BY YEAR(combined.day) ASC, WEEK(combined.day, 1) ASC";

    return $sql;
}


function getSqlForMonthsByAccount($accountId, $months, $accountFilter = "")
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
            SUM(combined.profit) AS profit
            FROM (
                SELECT DATE(FROM_UNIXTIME(o.time)) AS day, o.profit
                FROM orders AS o
                LEFT JOIN ssi_trader.broker AS b ON o.broker_id = b.broker_id
                WHERE o.account = '$accountId' AND trash = 0 
                AND $exclusionClause
                AND o.time >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL $months MONTH))
                $accountFilter
                UNION ALL
                SELECT first_day_of_month AS day, 0 AS profit
                FROM ($monthsSubQuery) AS m
            ) AS combined
            GROUP BY YEAR(combined.day), MONTH(combined.day)
            ORDER BY YEAR(combined.day) ASC, MONTH(combined.day) ASC";

    return $sql;
}