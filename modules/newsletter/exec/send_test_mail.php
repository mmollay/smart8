<?php
require_once(__DIR__ . '/../n_config.php');
require_once(__DIR__ . '/../classes/EmailService.php');
require_once(__DIR__ . '/../classes/PlaceholderService.php');
require __DIR__ . '/../../../vendor/autoload.php';
require_once(__DIR__ . '/../functions.php');
use \Mailjet\Resources;

header('Content-Type: application/json');

// Überprüfe API Credentials
if (empty($mailjetConfig['api_key']) || empty($mailjetConfig['api_secret'])) {
    die(json_encode([
        'success' => false,
        'message' => 'Mailjet API Konfiguration fehlt'
    ]));
}

if (!isset($_POST['content_id'])) {
    die(json_encode(['success' => false, 'message' => 'Keine Newsletter-ID übermittelt']));
}

$content_id = intval($_POST['content_id']);

try {
    // Prüfe ob der Newsletter dem User gehört
    $stmt = $db->prepare("
        SELECT 
            ec.subject,
            ec.message,
            s.test_email,
            s.email as sender_email,
            s.first_name as sender_first_name,
            s.last_name as sender_last_name,
            s.company as sender_company,
            s.title as sender_title,
            s.gender as sender_gender
        FROM email_contents ec
        JOIN senders s ON ec.sender_id = s.id
        WHERE ec.id = ? 
        AND ec.user_id = ?
        AND s.user_id = ?
    ");

    $stmt->bind_param("iii", $content_id, $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        throw new Exception('Newsletter nicht gefunden oder keine Berechtigung');
    }

    if (!$data['test_email']) {
        throw new Exception('Keine Test-Email-Adresse für diesen Absender konfiguriert');
    }

    // Initialisiere PlaceholderService
    $placeholderService = PlaceholderService::getInstance();

    // Testempfänger-Daten für Platzhalter
    $recipientData = [
        'first_name' => $data['sender_first_name'],
        'last_name' => $data['sender_last_name'],
        'email' => $data['test_email'],
        'company' => $data['sender_company'],
        'gender' => $data['sender_gender'],
        'title' => $data['sender_title']
    ];

    // Erstelle Platzhalter
    $placeholders = $placeholderService->createPlaceholders($recipientData);

    // Ersetze Platzhalter in Subject und Message
    $subject = $placeholderService->replacePlaceholders($data['subject'], $placeholders);
    $message = $placeholderService->replacePlaceholders($data['message'], $placeholders);
    $message = prepareHtmlForEmail($message) . 'test';
    // Füge Debug-Informationen für Test-Mail hinzu

    //$message = $placeholderService->addDebugInfo($message, $placeholders);

    // Hole Anhänge
    $attachments = [];

    //siehe n_config.php
    $directory = $uploadBasePath . '/' . $content_id . "/attachements/";

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
            'Name' => trim($data['sender_first_name'] . ' ' . $data['sender_last_name'])
        ],
        'To' => [
            [
                'Email' => $data['test_email'],
                'Name' => "Test: " . trim($data['sender_first_name'] . ' ' . $data['sender_last_name'])
            ]
        ],
        'Subject' => '[TEST] ' . $subject,
        'TextPart' => strip_tags($message),
        'HTMLPart' => $message,
        'Attachments' => $attachments,
        'CustomID' => "test_mail_{$content_id}_" . time()
    ];

    // Initialisiere Mailjet
    $mj = new \Mailjet\Client(
        $mailjetConfig['api_key'],
        $mailjetConfig['api_secret'],
        true,
        ['version' => 'v3.1']
    );

    // Sende die E-Mail
    $response = $mj->post(Resources::$Email, ['body' => ['Messages' => [$email]]]);

    if ($response->success()) {
        // Log den erfolgreichen Versand
        $db->query("INSERT INTO email_logs (status, response, created_at) 
           VALUES ('success', 'Test-Mail erfolgreich gesendet', NOW())");

        echo json_encode([
            'success' => true,
            'message' => 'Test-Mail wurde erfolgreich an ' . $data['test_email'] . ' gesendet'
        ]);
    } else {
        throw new Exception('Fehler beim Senden der Test-Mail: ' . json_encode($response->getBody()));
    }

} catch (Exception $e) {
    // Log den Fehler
    $db->query("INSERT INTO email_logs (status, response, created_at) 
    VALUES ('failed', '" . $db->real_escape_string($e->getMessage()) . "', NOW())");
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($db)) {
        $db->close();
    }
}