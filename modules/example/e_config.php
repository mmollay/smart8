<?php

$dbConfig = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'username' => 'smart',
    'password' => 'Eiddswwenph21;',
    'dbname' => 'demo'
];

try {
    $mysqli = mysqli_init();
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

    if (
        !$mysqli->real_connect(
            $dbConfig['host'],
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['dbname'],
            $dbConfig['port']
        )
    ) {
        throw new Exception('Database connection failed: ' . $mysqli->connect_error);
    }

    $db = $connection = $GLOBALS['mysqli'] = $mysqli;
    $db->set_charset('utf8mb4');

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}