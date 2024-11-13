<?php
require_once(__DIR__ . '/../n_config.php');
require_once(__DIR__ . '/../classes/EmailService.php');
require_once(__DIR__ . '/../classes/PlaceholderService.php');
require __DIR__ . '/../../../vendor/autoload.php';
use \Mailjet\Resources;

header('Content-Type: application/json');

if (!isset($_POST['content_id'])) {
    die(json_encode(['success' => false, 'message' => 'Keine Newsletter-ID übermittelt']));
}

$content_id = intval($_POST['content_id']);

try {
    // Initialisiere Services mit Singleton Pattern
    $placeholderService = PlaceholderService::getInstance();

    // Hole Newsletter-Daten und Test-Email des Absenders
    $stmt = $db->prepare("
        SELECT 
            ec.subject,
            ec.message,
            s.test_email,
            s.email as sender_email,
            s.first_name as first_name,
            s.last_name as last_name,
            CONCAT(s.first_name, ' ', s.last_name) as sender_name,
            s.company,
            s.title,
            s.gender
        FROM email_contents ec
        JOIN senders s ON ec.sender_id = s.id
        WHERE ec.id = ?
    ");

    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        throw new Exception('Newsletter nicht gefunden');
    }

    if (!$data['test_email']) {
        throw new Exception('Keine Test-Email-Adresse für diesen Absender konfiguriert');
    }

    // Erstelle Platzhalter für Test-Mail
    $placeholders = $placeholderService->createPlaceholders([
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'email' => $data['test_email'],
        'company' => $data['company'],
        'gender' => $data['gender'],
        'title' => $data['title']
    ]);

    // Ersetze Platzhalter im Betreff und der Nachricht
    $subject = $placeholderService->replacePlaceholders($data['subject'], $placeholders);
    $message = $placeholderService->replacePlaceholders($data['message'], $placeholders);

    // Füge Debug-Informationen für Test-Mails hinzu
    $message = $placeholderService->addDebugInfo($message, $placeholders);

    // Hole Anhänge
    $attachments = [];
    $directory = $uploadBasePath . $content_id . "/";

    if (is_dir($directory)) {
        $files = scandir($directory);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $full_path = $directory . $file;
                if (is_file($full_path)) {
                    $attachments[] = [
                        'ContentType' => mime_content_type($full_path),
                        'Filename' => $file,
                        'Base64Content' => base64_encode(file_get_contents($full_path))
                    ];
                }
            }
        }
    }

    // Erstelle E-Mail
    $email = [
        'From' => [
            'Email' => $data['sender_email'],
            'Name' => $data['sender_name']
        ],
        'To' => [
            [
                'Email' => $data['test_email'],
                'Name' => "Test: {$data['first_name']} {$data['last_name']}"
            ]
        ],
        'Subject' => '[TEST] ' . $subject,
        'TextPart' => strip_tags($message),
        'HTMLPart' => $message,
        'Attachments' => $attachments,
        'CustomID' => "test_mail_{$content_id}_" . time()
    ];

    // Initialisiere Mailjet und sende E-Mail
    $mj = new \Mailjet\Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);
    $response = $mj->post(Resources::$Email, ['body' => ['Messages' => [$email]]]);

    if ($response->success()) {
        echo json_encode([
            'success' => true,
            'message' => 'Test-Mail wurde erfolgreich an ' . $data['test_email'] . ' gesendet',
            'placeholders' => $placeholders // Für Debug-Zwecke
        ]);
    } else {
        throw new Exception('Fehler beim Senden der Test-Mail: ' . json_encode($response->getBody()));
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($db)) {
        $db->close();
    }
}