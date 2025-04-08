<?php
require_once(__DIR__ . '/n_config.php');
require_once(__DIR__ . '/classes/BrevoEmailService.php');

// Fehlerbehandlung aktivieren
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Brevo-Konfiguration laden
    $brevoConfig = $config['brevo'] ?? [];
    $apiKey = $_ENV['BREVO_API_KEY'] ?? $brevoConfig['api_key'] ?? '';
    
    if (empty($apiKey)) {
        throw new Exception("Brevo API Key nicht gefunden. Bitte in n_config.php oder config.php konfigurieren");
    }
    
    echo "API-Key gefunden: " . substr($apiKey, 0, 5) . "..." . "<br>\n";
    
    // BrevoEmailService initialisieren
    $emailService = new BrevoEmailService(
        $db,
        $apiKey,
        __DIR__ . '/test_attachments/'
    );
    
    echo "BrevoEmailService initialisiert<br>\n";
    
    // Testmail-Parameter
    $sender = [
        'email' => 'sender@example.com',  // Ändere dies zu einer gültigen Absender-E-Mail
        'name' => 'Test Sender'
    ];
    
    $recipient = [
        'email' => 'recipient@example.com',  // Ändere dies zu deiner E-Mail-Adresse
        'name' => 'Test Empfänger',
        'first_name' => 'Test',
        'last_name' => 'Empfänger'
    ];
    
    $subject = 'Testmail von Brevo-Integration ' . date('Y-m-d H:i:s');
    $message = '<html><body><h1>Test-E-Mail</h1><p>Dies ist eine Test-E-Mail zur Überprüfung der Brevo-Integration.</p></body></html>';
    
    echo "E-Mail-Parameter vorbereitet<br>\n";
    
    // E-Mail senden
    $result = $emailService->sendSingleEmail(
        999,  // Dummy content_id
        $sender,
        $recipient,
        $subject,
        $message,
        null,  // Kein Job-ID
        true   // Ist eine Test-Mail
    );
    
    echo "E-Mail-Sendung versucht<br>\n";
    
    // Ergebnis ausgeben
    echo "<pre>\n";
    print_r($result);
    echo "</pre>\n";
    
} catch (Exception $e) {
    echo "<h2>Fehler:</h2>\n";
    echo "<p>" . $e->getMessage() . "</p>\n";
    
    echo "<h3>Stack Trace:</h3>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

// Debug-Log für Bibliotheksinitialisierung
echo "<h2>Include-Pfade:</h2>\n";
echo "<pre>\n";
print_r(get_include_path());
echo "</pre>\n";

// PHP-Fehlerprotokoll direkt auslesen
echo "<h2>Letzten 10 Zeilen des PHP-Fehlerprotokolls:</h2>\n";
echo "<pre>\n";
$errorLog = '/Applications/XAMPP/xamppfiles/logs/php_error_log';
if (file_exists($errorLog)) {
    $logContent = file_get_contents($errorLog);
    $lines = explode("\n", $logContent);
    $lastLines = array_slice($lines, -10);
    echo implode("\n", $lastLines);
} else {
    echo "Fehlerprotokoll existiert nicht";
}
echo "</pre>\n";

// Brevo-API-Konfiguration prüfen
echo "<h2>Brevo-API-Konfiguration:</h2>\n";
echo "<pre>\n";
echo "API-Key in ENV: " . (isset($_ENV['BREVO_API_KEY']) ? "Vorhanden" : "Nicht vorhanden") . "\n";
echo "API-Key in config['brevo']: " . (isset($config['brevo']['api_key']) ? "Vorhanden" : "Nicht vorhanden") . "\n";
if (isset($config['brevo'])) {
    print_r($config['brevo']);
}
echo "</pre>\n";

// Klassen-Autoloading testen
echo "<h2>Brevo-Klassen-Check:</h2>\n";
echo "<pre>\n";
echo "Brevo\\Client\\Api\\TransactionalEmailsApi: " . (class_exists('\\Brevo\\Client\\Api\\TransactionalEmailsApi') ? "Gefunden" : "Nicht gefunden") . "\n";
echo "Brevo\\Client\\Configuration: " . (class_exists('\\Brevo\\Client\\Configuration') ? "Gefunden" : "Nicht gefunden") . "\n";
echo "Brevo\\Client\\Model\\SendSmtpEmail: " . (class_exists('\\Brevo\\Client\\Model\\SendSmtpEmail') ? "Gefunden" : "Nicht gefunden") . "\n";
echo "</pre>\n";
