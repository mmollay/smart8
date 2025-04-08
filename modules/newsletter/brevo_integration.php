<?php
/**
 * Brevo Integration Beispiel
 * 
 * Dieses Skript zeigt, wie du die Brevo-Integration verwenden kannst.
 * Folge den Anweisungen in den Kommentaren, um Mailjet auf Brevo umzustellen.
 */

// 1. Konfiguration in config.php anpassen:
// Füge in der config.php im 'mail' Bereich folgendes hinzu:
/*
'brevo' => [
    'api_key' => $_ENV['BREVO_API_KEY'] ?? ''
]
*/

// 2. In n_config.php den Brevo API-Key hinzufügen:
// Füge nach den SMTP-Zugangsdaten folgendes hinzu:
/*
// Brevo API Key für die API-Funktionen (zusätzlich zu SMTP)
$_ENV['BREVO_API_KEY'] = 'xkeysib-85728a001-EHX36xRwBKM0scNP-VVh34RMp';
// Hinweis: Den korrekten API-Key findest du in deinem Brevo-Konto unter "SMTP & API" 
*/

// 3. Brevo-Service initialisieren (z.B. in ajax/send_newsletter.php):
/*
// Lade die Brevo-Bibliothek
require_once(__DIR__ . '/../classes/BrevoEmailService.php');

// Brevo API-Key aus Konfiguration laden
$brevoConfig = $config['mail']['brevo'] ?? [];
$apiKey = $brevoConfig['api_key'] ?? '';

// Initialisieren des Brevo-Services
$emailService = new BrevoEmailService($newsletterDb, $apiKey, $uploadBasePath);

// Newsletter mit Brevo senden (gleiche Methode wie bei Mailjet)
$result = $emailService->sendSingleEmail($contentId, $sender, $recipient, $subject, $message, $jobId, $isTest);
*/

// 4. Webhook in Brevo konfigurieren:
// - Melde dich in deinem Brevo-Konto an
// - Gehe zu "Einstellungen" -> "Webhooks"
// - Erstelle einen neuen Webhook mit der URL: https://deine-domain.de/modules/newsletter/webhooks/brevo_webhook.php
// - Wähle die Ereignisse aus, auf die der Webhook reagieren soll (z.B. "Zugestellt", "Geöffnet", "Geklickt", etc.)
?>
