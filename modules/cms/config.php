<?php
$dbHost = 'localhost';
$dbName = 'ssi_cms';
$dbUser = 'smart';
$dbPass = 'Eiddswwenph21;';

try {
    $db = new PDO(
        "mysql:host=localhost;dbname=$dbName;charset=utf8",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Verbindungsfehler: ' . $e->getMessage());
}