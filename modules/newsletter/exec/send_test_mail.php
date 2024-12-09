<?php
require_once(__DIR__ . '/../n_config.php');
require_once(__DIR__ . '/../classes/EmailService.php');
require_once(__DIR__ . '/../classes/PlaceholderService.php');
require __DIR__ . '/../../../vendor/autoload.php';
require_once(__DIR__ . '/../functions.php');
use \Mailjet\Resources;

header('Content-Type: application/json');

// Logging-Funktion
function logTestMail($userId, $message, $status = 'info')
{
    $logFile = __DIR__ . '/../logs/test_mail.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp][User:$userId][$status] $message\n";
    error_log($logMessage, 3, $logFile);
}

// Überprüfe API Credentials
if (empty($mailjetConfig['api_key']) || empty($mailjetConfig['api_secret'])) {
    logTestMail($userId ?? 0, 'Mailjet API Konfiguration fehlt', 'error');
    die(json_encode([
        'success' => false,
        'message' => 'Mailjet API Konfiguration fehlt'
    ]));
}

if (!isset($_POST['content_id'])) {
    logTestMail($userId ?? 0, 'Keine Newsletter-ID übermittelt', 'error');
    die(json_encode(['success' => false, 'message' => 'Keine Newsletter-ID übermittelt']));
}

$content_id = intval($_POST['content_id']);

try {
    // Prüfe ob der Newsletter und Absender dem User gehören
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

    // Blacklist-Prüfung
    $stmt = $db->prepare("
        SELECT id, reason 
        FROM blacklist 
        WHERE email = ? 
        AND user_id = ?
    ");
    $stmt->bind_param("si", $data['test_email'], $userId);
    $stmt->execute();
    $blacklistResult = $stmt->get_result();

    if ($blacklistResult->num_rows > 0) {
        $blacklistData = $blacklistResult->fetch_assoc();
        logTestMail($userId, "Test-Mail an Blacklist-Adresse verhindert: {$data['test_email']}", 'warning');
        throw new Exception('Diese E-Mail-Adresse steht auf der Blacklist und kann keine E-Mails empfangen. Grund: ' .
            ($blacklistData['reason'] ?? 'Nicht angegeben'));
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

    $APP_URL = $_ENV['APP_URL'] ?? 'https://newsletter.ssi.at';

    // Ersetze Platzhalter in Subject und Message
    $subject = $placeholderService->replacePlaceholders($data['subject'], $placeholders);
    $message = $placeholderService->replacePlaceholders($data['message'], $placeholders);
    $message = prepareHtmlForEmail($message);
    $message = makeUrlsAbsolute($message, $APP_URL);

    // Hole Anhänge
    $attachments = [];
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

    $test_emails = array_filter(array_map('trim', explode("\n", $data['test_email'])));
    $successful_emails = [];

    foreach ($test_emails as $test_email) {
        // Erstelle E-Mail
        $email = [
            'From' => [
                'Email' => $data['sender_email'],
                'Name' => trim($data['sender_first_name'] . ' ' . $data['sender_last_name'])
            ],
            'To' => [
                [
                    'Email' => $test_email,
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

        logTestMail($userId, "Sende Test-Mail an: {$test_email}", 'info');

        // Sende die E-Mail
        $response = $mj->post(Resources::$Email, ['body' => ['Messages' => [$email]]]);

        if ($response->success()) {
            $successful_emails[] = $test_email;
            logTestMail($userId, "Test-Mail erfolgreich an {$test_email} gesendet", 'success');
        } else {
            $errorMsg = 'Fehler beim Senden der Test-Mail: ' . json_encode($response->getBody());
            throw new Exception($errorMsg);
        }
    }

    // Log den erfolgreichen Versand
    if (!empty($successful_emails)) {
        $successMsg = "Test-Mail erfolgreich versendet an:<br/>" . implode("<br/>", $successful_emails);

        $stmt = $db->prepare("
            INSERT INTO email_logs (status, response, created_at) 
            VALUES ('success', ?, NOW())
        ");
        $stmt->bind_param("s", $successMsg);
        $stmt->execute();

        logTestMail($userId, $successMsg, 'success');

        echo json_encode([
            'success' => true,
            'message' => $successMsg,
            'duration' => 5000,
            'html' => true
        ]);
    }

} catch (Exception $e) {
    // Log den Fehler
    $errorMessage = $e->getMessage();

    $stmt = $db->prepare("
        INSERT INTO email_logs (status, response, created_at) 
        VALUES ('failed', ?, NOW())
    ");
    $stmt->bind_param("s", $errorMessage);
    $stmt->execute();

    logTestMail($userId ?? 0, $errorMessage, 'error');

    echo json_encode([
        'success' => false,
        'message' => $errorMessage,
        'duration' => 8000
    ]);
} finally {
    if (isset($db)) {
        $db->close();
    }
}