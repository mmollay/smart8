<?php
require_once(__DIR__ . '/t_config.php');

// Hole alle API Credentials
$result = $db->query("SELECT * FROM api_credentials WHERE platform = 'bitget'");
$credentials = $result->fetch_all(MYSQLI_ASSOC);

echo "<pre>";
foreach ($credentials as $cred) {
    echo "ID: " . $cred['id'] . "\n";
    echo "User ID: " . $cred['user_id'] . "\n";
    echo "Platform: " . $cred['platform'] . "\n";
    echo "API Key: " . $cred['api_key'] . "\n";
    echo "API Secret: " . $cred['api_secret'] . "\n";
    echo "API Passphrase: " . $cred['api_passphrase'] . "\n";
    echo "Is Active: " . ($cred['is_active'] ? 'Yes' : 'No') . "\n";
    echo "Created: " . $cred['created_at'] . "\n";
    echo "Last Used: " . $cred['last_used'] . "\n";
    echo "----------------------------------------\n";
}
echo "</pre>";
