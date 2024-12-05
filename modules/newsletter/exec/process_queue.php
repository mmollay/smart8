<?php
if (php_sapi_name() !== 'cli') {
    die('Dieses Script kann nur über die Kommandozeile ausgeführt werden');
}

define('BASE_PATH', dirname(__DIR__));

// Erforderliche Klassen einbinden
require_once BASE_PATH . '/n_config.php';
require_once BASE_PATH . '/classes/QueueProcessor.php';
require_once BASE_PATH . '/classes/EmailService.php';
require_once BASE_PATH . '/classes/PlaceholderService.php';
require_once BASE_PATH . '/classes/EmailQueueManager.php';

// Logging-Verzeichnis erstellen falls nicht vorhanden
$logDir = BASE_PATH . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Command Line Parameter
$options = getopt('', ['content-id:']);
$contentId = isset($options['content-id']) ? intval($options['content-id']) : null;

// Services initialisieren - PlaceholderService als Singleton
$emailService = new EmailService($db, $GLOBALS['apiKey'], $GLOBALS['apiSecret'], $GLOBALS['uploadBasePath']);
$placeholderService = PlaceholderService::getInstance();


function processNewsletter($db, $emailService, $placeholderService, $contentId)
{
    $stmt = $db->prepare("
    SELECT ej.*, 
           ec.subject, 
           ec.message,
           s.email as sender_email,
           s.first_name as sender_first_name,
           s.last_name as sender_last_name,
           r.email as recipient_email,
           r.first_name as recipient_first_name,
           r.last_name as recipient_last_name,
           r.gender as recipient_gender,
           r.title as recipient_title,
           r.company as recipient_company
    FROM email_jobs ej
    JOIN email_contents ec ON ej.content_id = ec.id
    JOIN senders s ON ej.sender_id = s.id
    JOIN recipients r ON ej.recipient_id = r.id
    WHERE ej.content_id = ? 
    AND ej.status = 'pending'
    AND r.unsubscribed = 0  -- Nur nicht abgemeldete Empfänger
");

    $stmt->bind_param("i", $contentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Keine ausstehenden E-Mails für Newsletter ID: $contentId\n";
        return 0;
    }

    $count = 0;
    while ($job = $result->fetch_assoc()) {
        // Erstelle Platzhalter für den aktuellen Empfänger
        $placeholders = [
            'vorname' => $job['recipient_first_name'],
            'nachname' => $job['recipient_last_name'],
            'email' => $job['recipient_email'],
            'firma' => $job['recipient_company'],
            'company' => $job['recipient_company'],
            'geschlecht' => $job['recipient_gender'],
            'titel' => $job['recipient_title'],
            'anrede' => getAnrede(
                $job['recipient_gender'],
                $job['recipient_title'],
                $job['recipient_first_name'],
                $job['recipient_last_name']
            )
        ];

        // Ersetze Platzhalter in Betreff und Nachricht
        $subject = $placeholderService->replacePlaceholders($job['subject'], $placeholders);
        $message = makeUrlsAbsolute($job['message'], $_SERVER['HTTP_HOST']);
        $message = $placeholderService->replacePlaceholders($job['message'], $placeholders);

        // Füge Abmelde-Link hinzu
        $unsubscribeUrl = "https://" . $_SERVER['HTTP_HOST'] . "/unsubscribe.php?email=" .
            urlencode($job['recipient_email']) . "&id=" . $job['id'];
        $unsubscribeLink = "<br><br><hr><p style='font-size: 12px; color: #666;'>
            Falls Sie keine weiteren E-Mails erhalten möchten, 
            können Sie sich hier <a href='{$unsubscribeUrl}'>abmelden</a>.</p>";
        $message .= $unsubscribeLink;

        $sender = [
            'email' => $job['sender_email'],
            'name' => trim($job['sender_first_name'] . ' ' . $job['sender_last_name'])
        ];

        $recipient = [
            'email' => $job['recipient_email'],
            'name' => trim($job['recipient_first_name'] . ' ' . $job['recipient_last_name'])
        ];

        $sendResult = $emailService->sendSingleEmail(
            $contentId,
            $sender,
            $recipient,
            $subject,
            $message,
            $job['id']
        );

        if ($sendResult['success']) {
            // Update job status
            $updateStmt = $db->prepare("
                UPDATE email_jobs 
                SET status = 'send',
                    sent_at = NOW(),
                    message_id = ?
                WHERE id = ?
            ");
            $updateStmt->bind_param("si", $sendResult['message_id'], $job['id']);
            $updateStmt->execute();

            // Log success
            $logStmt = $db->prepare("
                INSERT INTO status_log 
                (event, timestamp, message_id, email) 
                VALUES ('send', NOW(), ?, ?)
            ");
            $logStmt->bind_param("ss", $sendResult['message_id'], $job['recipient_email']);
            $logStmt->execute();

            $count++;
            echo "E-Mail {$count} gesendet an: {$job['recipient_email']}\n";
        } else {
            echo "Fehler beim Senden an {$job['recipient_email']}: {$sendResult['error']}\n";
        }
    }

    // Aktualisiere Newsletter-Status
    $stmt = $db->prepare("
        UPDATE email_contents 
        SET send_status = 2 
        WHERE id = ? 
        AND NOT EXISTS (
            SELECT 1 
            FROM email_jobs 
            WHERE content_id = ? 
            AND status = 'pending'
        )
    ");
    $stmt->bind_param("ii", $contentId, $contentId);
    $stmt->execute();

    return $count;
}

function getAnrede($gender, $title, $firstName, $lastName)
{
    $anrede = 'Sehr ';

    if ($gender === 'female') {
        $anrede .= 'geehrte';
        $anrede .= $title ? ' Frau ' . $title : ' Frau';
    } else {
        $anrede .= 'geehrter';
        $anrede .= $title ? ' Herr ' . $title : ' Herr';
    }

    $anrede .= ' ' . $lastName;

    return $anrede;
}

if ($contentId) {
    // Verarbeite spezifischen Newsletter
    echo "Verarbeite Newsletter ID: $contentId\n";
    $count = processNewsletter($db, $emailService, $placeholderService, $contentId);
    echo "\nNewsletter verarbeitet. $count E-Mails gesendet.\n";
} else {
    // Verarbeite alle ausstehenden Newsletter
    $stmt = $db->prepare("
        SELECT DISTINCT ec.id
        FROM email_contents ec
        JOIN email_jobs ej ON ec.id = ej.content_id
        WHERE ec.send_status = 1
        AND ej.status = 'pending'
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Keine ausstehenden Newsletter gefunden.\n";
        exit(0);
    }

    $totalCount = 0;
    $newsletterCount = 0;

    while ($row = $result->fetch_assoc()) {
        $newsletterId = $row['id'];
        echo "\nVerarbeite Newsletter ID: $newsletterId\n";
        $count = processNewsletter($db, $emailService, $placeholderService, $newsletterId);
        if ($count > 0) {
            $totalCount += $count;
            $newsletterCount++;
        }
    }

    echo "\nVerarbeitung abgeschlossen.\n";
    echo "Newsletter verarbeitet: $newsletterCount\n";
    echo "Gesamt E-Mails gesendet: $totalCount\n";
}

function makeUrlsAbsolute($content, $baseUrl)
{
    $baseUrl = rtrim($baseUrl, '/');

    // Array von Mustern und ihren Attributen
    $patterns = [
        // Bilder
        ['pattern' => '/src="(\/users\/[^"]+)"/i', 'attr' => 'src'],
        // Links
        ['pattern' => '/href="(\/users\/[^"]+)"/i', 'attr' => 'href'],
        // Weitere Muster nach Bedarf...
    ];

    foreach ($patterns as $p) {
        $content = preg_replace_callback(
            $p['pattern'],
            function ($matches) use ($baseUrl) {
                $relativePath = $matches[1];
                return $p['attr'] . '="' . $baseUrl . $relativePath . '"';
            },
            $content
        );
    }
    echo $content;
    return $content;
}
