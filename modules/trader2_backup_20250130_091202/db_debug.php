<?php
session_start();
require 't_config.php';

echo "Session Info:\n";
echo "-------------\n";
echo "Session ID: " . session_id() . "\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'nicht gesetzt') . "\n\n";

echo "Orders in der Datenbank:\n";
echo "----------------------\n";
$result = $db->query("SELECT * FROM orders");
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . "\n";
    echo "User ID: " . $row['user_id'] . "\n";
    echo "Symbol: " . $row['symbol'] . "\n";
    echo "Status: " . $row['status'] . "\n";
    echo "Created: " . $row['created_at'] . "\n";
    echo "------------------------\n";
}

echo "\nUser in der Datenbank:\n";
echo "--------------------\n";
$result = $db->query("SELECT * FROM users");
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . "\n";
    echo "Username: " . $row['username'] . "\n";
    echo "------------------------\n";
}

echo "\nAPI Credentials:\n";
echo "---------------\n";
$result = $db->query("SELECT * FROM api_credentials");
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . "\n";
    echo "User ID: " . $row['user_id'] . "\n";
    echo "Platform: " . $row['platform'] . "\n";
    echo "Is Active: " . $row['is_active'] . "\n";
    echo "------------------------\n";
}
