<?php
// Erlaube Webhook-Zugriff ohne Session
define('ALLOW_WEBHOOK', true);

// Konfiguration einbinden
require_once(__DIR__ . '/../n_config.php');

// Brevo Services einbinden
require_once(__DIR__ . '/../classes/BrevoEmailService.php');
require_once(__DIR__ . '/../classes/BrevoWebhookHandler.php');

// Brevo API-Key aus Konfiguration laden
$brevoConfig = $config['mail']['brevo'] ?? [];
$apiKey = $brevoConfig['api_key'] ?? '';

// Initialisieren der Brevo-Services
$emailService = new BrevoEmailService($newsletterDb, $apiKey, $uploadBasePath);
$webhookHandler = new BrevoWebhookHandler($newsletterDb, $emailService, $apiKey);

// Webhook-Anfrage verarbeiten
$result = $webhookHandler->handleRequest();

// JSON-Antwort senden
header('Content-Type: application/json');
echo json_encode($result);
exit;
