<?php
// Flag für Webhook-Zugriff - MUSS vor n_config.php sein
define('ALLOW_WEBHOOK', true);

// Basis-Definition
define('BASE_PATH', dirname(__DIR__));

// Fehlerbehandlung
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/webhook_error.log');

// Log-Funktion
function debugLog($message)
{
	$logFile = BASE_PATH . '/logs/webhook_debug.log';
	$logDir = dirname($logFile);

	if (!is_dir($logDir)) {
		mkdir($logDir, 0755, true);
	}

	error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, $logFile);
}

debugLog("Script started");

// Basic Auth Prüfung
if (
	!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
	$_SERVER['PHP_AUTH_USER'] !== 'mailjet' || $_SERVER['PHP_AUTH_PW'] !== 'm41lj3t'
) {
	debugLog("Auth failed: " . $_SERVER['PHP_AUTH_USER'] ?? 'no user');
	header('HTTP/1.0 401 Unauthorized');
	exit('Unauthorized');
}

debugLog("Auth successful");

// Konfiguration laden
require_once BASE_PATH . '/n_config.php';

debugLog("Config loaded");

try {
	// JSON Payload empfangen
	$payload = file_get_contents('php://input');
	if (empty($payload)) {
		throw new Exception('Keine Daten empfangen');
	}

	debugLog("Received payload: " . $payload);

	// JSON decodieren
	$events = json_decode($payload, true);
	if (json_last_error() !== JSON_ERROR_NONE) {
		throw new Exception('Ungültiges JSON Format: ' . json_last_error_msg());
	}

	debugLog("Decoded events: " . print_r($events, true));

	// Einzelnes Event in Array umwandeln
	if (!isset($events[0])) {
		$events = [$events];
	}

	// Status-Mapping
	$statusMapping = [
		'sent' => 'send',
		'delivered' => 'delivered',
		'opened' => 'open',
		'clicked' => 'click',
		'bounced' => 'bounce',
		'blocked' => 'blocked',
		'spam' => 'spam',
		'unsub' => 'unsub'
	];

	// Events verarbeiten
	foreach ($events as $event) {
		debugLog("Processing event: " . print_r($event, true));

		// Pflichtfelder prüfen
		if (empty($event['MessageID']) || empty($event['event'])) {
			debugLog("Invalid event format");
			continue;
		}

		$messageId = $event['MessageID'];
		$eventType = strtolower($event['event']);
		$email = $event['email'] ?? '';

		try {
			$db->begin_transaction();
			debugLog("Started transaction for message ID: $messageId");

			// Event loggen
			$stmt = $db->prepare("
                INSERT INTO status_log 
                (event, timestamp, message_id, email)
                VALUES (?, NOW(), ?, ?)
            ");
			$stmt->bind_param("sss", $eventType, $messageId, $email);
			$stmt->execute();
			debugLog("Status log entry created");

			// Job ID und Recipient ID für das Tracking holen
			$stmt = $db->prepare("
                SELECT ej.id as job_id, ej.recipient_id 
                FROM email_jobs ej
                WHERE ej.message_id = ?
            ");
			$stmt->bind_param("s", $messageId);
			$stmt->execute();
			$jobInfo = $stmt->get_result()->fetch_assoc();

			// Tracking Events verarbeiten
			if ($jobInfo && in_array($eventType, ['opened', 'clicked', 'unsub', 'bounced', 'spam'])) {
				// Event-spezifische Daten sammeln
				$eventData = [
					'timestamp' => date('Y-m-d H:i:s'),
					'ip' => $event['ip'] ?? null,
					'user_agent' => $event['user_agent'] ?? null
				];

				// Für Click-Events die URL hinzufügen
				if ($eventType === 'clicked' && isset($event['url'])) {
					$eventData['url'] = $event['url'];
				}

				// Tracking Event speichern
				$stmt = $db->prepare("
                    INSERT INTO email_tracking 
                    (job_id, recipient_id, event_type, event_data, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");

				$eventDataJson = json_encode($eventData);
				$mappedEventType = $statusMapping[$eventType] ?? $eventType;

				$stmt->bind_param(
					"iiss",
					$jobInfo['job_id'],
					$jobInfo['recipient_id'],
					$mappedEventType,
					$eventDataJson
				);
				$stmt->execute();
				debugLog("Tracking event created for job_id: {$jobInfo['job_id']}");
			}

			// Email-Job Status aktualisieren
			$status = $statusMapping[$eventType] ?? $eventType;
			$stmt = $db->prepare("
                UPDATE email_jobs 
                SET 
                    status = ?,
                    updated_at = NOW()
                WHERE message_id = ? 
                AND status != 'unsub'
            ");
			$stmt->bind_param("ss", $status, $messageId);
			$stmt->execute();
			debugLog("Email job status updated");

			// Abmeldungen verarbeiten
			if ($eventType === 'unsub' && !empty($email)) {
				debugLog("Processing unsubscribe for: $email");

				// Empfänger Status aktualisieren
				if ($jobInfo) {
					$stmt = $db->prepare("
                        UPDATE recipients
                        SET 
                            unsubscribed = 1,
                            unsubscribed_at = NOW()
                        WHERE id = ?
                    ");
					$stmt->bind_param("i", $jobInfo['recipient_id']);
					$stmt->execute();

					// Abmeldung in Log-Tabelle eintragen
					$stmt = $db->prepare("
                        INSERT INTO unsubscribe_log 
                        (recipient_id, email, message_id, timestamp)
                        VALUES (?, ?, ?, NOW())
                    ");
					$stmt->bind_param(
						"iss",
						$jobInfo['recipient_id'],
						$email,
						$messageId
					);
					$stmt->execute();
					debugLog("Unsubscribe processed successfully");
				}
			}

			$db->commit();
			debugLog("Transaction committed for message ID: $messageId");

		} catch (Exception $e) {
			$db->rollback();
			debugLog("Error processing event: " . $e->getMessage());
			throw $e;
		}
	}

	// Erfolgreiche Antwort
	http_response_code(200);
	echo json_encode([
		'success' => true,
		'message' => 'Events processed',
		'count' => count($events),
		'timestamp' => date('c')
	]);

} catch (Exception $e) {
	debugLog("Critical error: " . $e->getMessage());
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'error' => $e->getMessage(),
		'timestamp' => date('c')
	]);
} finally {
	if (isset($db) && $db instanceof mysqli) {
		$db->close();
		debugLog("Database connection closed");
	}
}