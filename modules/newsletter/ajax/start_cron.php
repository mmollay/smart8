<?php
require_once('../n_config.php');
header('Content-Type: application/json');

if (!$isAdmin) {
    die(json_encode(['error' => 'Keine Berechtigung']));
}


$command = '';
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    $command = "export PATH=/Applications/XAMPP/xamppfiles/bin:/usr/local/bin:/usr/bin:/bin && cd " . __DIR__ . "/../exec && php cron_controller.php 2>&1";
} else {
    $command = "cd " . __DIR__ . "/../exec && /usr/bin/php cron_controller.php 2>&1";
}

try {
    $output = [];
    exec($command, $output, $return_var);
    file_put_contents(__DIR__ . '/debug.log', "Output: " . print_r($output, true) . "\nReturn: $return_var\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
}

echo json_encode([
    'success' => $return_var === 0,
    'message' => 'Cron ausgeführt',
    'output' => $output,
    'return_var' => $return_var
]);