<?php
// Definiere Basispfad
define('BASE_PATH', dirname(__DIR__));

// Lade Konfiguration und Klassen
require_once BASE_PATH . '/n_config.php';
require_once BASE_PATH . '/classes/WebhookHandler.php';
require_once BASE_PATH . '/classes/EmailService.php';

// Stelle sicher, dass nur POST-Requests akzeptiert werden
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

try {
    // Initialisiere Handler
    $emailService = new EmailService($db, $apiKey, $apiSecret, $uploadBasePath);
    $webhookHandler = new WebhookHandler($db, $emailService, $apiSecret);

    // Verarbeite Request
    $result = $webhookHandler->handleRequest();

    // Sende Antwort
    http_response_code($result['success'] ? 200 : 400);
    header('Content-Type: application/json');
    echo json_encode($result);

} catch (Exception $e) {
    // Log den Fehler
    $logFile = BASE_PATH . '/logs/webhook_errors.log';
    error_log(date('Y-m-d H:i:s') . ' Webhook Error: ' . $e->getMessage() . "\n", 3, $logFile);

    // Sende Fehlerantwort
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error'
    ]);
}