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
	return;
	$logFile = BASE_PATH . '/logs/webhook_debug.log';
	$logDir = dirname($logFile);

	if (!is_dir($logDir)) {
		mkdir($logDir, 0755, true);
	}

	error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, $logFile);
}

debugLog("Script started");
debugLog("ALLOW_WEBHOOK is defined: " . (defined('ALLOW_WEBHOOK') ? 'yes' : 'no'));

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
// Debug für POST Daten
debugLog("POST Data: " . print_r($_POST, true));
debugLog("Raw input: " . file_get_contents('php://input'));
debugLog("Content Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

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
		'bounced' => 'failed',
		'blocked' => 'failed',
		'spam' => 'rejected',
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
			debugLog("Executing status_log INSERT for messageId: $messageId, event: $eventType");

			try {
				$stmt = $db->prepare("
                    INSERT INTO status_log 
                    (event, timestamp, message_id, email)
                    VALUES (?, NOW(), ?, ?)
                ");
				$stmt->bind_param("sss", $eventType, $messageId, $email);
				$success = $stmt->execute();

				if ($success) {
					debugLog("Status log INSERT successful. Affected rows: " . $stmt->affected_rows);
				} else {
					debugLog("Status log INSERT failed. Error: " . $stmt->error);
				}
			} catch (Exception $e) {
				debugLog("Database error on status_log INSERT: " . $e->getMessage());
				throw $e;
			}

			// Email-Job Status aktualisieren
			$status = $statusMapping[$eventType] ?? $eventType;
			debugLog("Executing email_jobs UPDATE for messageId: $messageId, new status: $status");

			try {
				$stmt = $db->prepare("
                    UPDATE email_jobs 
                    SET 
                        status = ?,
                        updated_at = NOW()
                    WHERE message_id = ? 
                    AND status != 'unsub'
                ");
				$stmt->bind_param("ss", $status, $messageId);
				$success = $stmt->execute();

				if ($success) {
					debugLog("Email jobs UPDATE successful. Affected rows: " . $stmt->affected_rows);
				} else {
					debugLog("Email jobs UPDATE failed. Error: " . $stmt->error);
				}
			} catch (Exception $e) {
				debugLog("Database error on email_jobs UPDATE: " . $e->getMessage());
				throw $e;
			}

			// Abmeldungen verarbeiten
			if ($eventType === 'unsub' && !empty($email)) {
				debugLog("Processing unsubscribe for: $email");

				// Empfänger Details holen
				$stmt = $db->prepare("
                    SELECT r.id, ej.content_id
                    FROM email_jobs ej
                    JOIN recipients r ON r.id = ej.recipient_id
                    WHERE ej.message_id = ?
                    LIMIT 1
                ");
				$stmt->bind_param("s", $messageId);
				$stmt->execute();
				$result = $stmt->get_result();
				$recipient = $result->fetch_assoc();

				if ($recipient) {
					debugLog("Found recipient: " . print_r($recipient, true));

					// Empfänger Status aktualisieren
					$stmt = $db->prepare("
                        UPDATE recipients
                        SET 
                            status = 'unsubscribed',
                            unsubscribed_at = NOW()
                        WHERE id = ?
                    ");
					$stmt->bind_param("i", $recipient['id']);
					$stmt->execute();

					// Abmeldung loggen
					$stmt = $db->prepare("
                        INSERT INTO unsubscribe_log 
                        (recipient_id, email, content_id, message_id, timestamp)
                        VALUES (?, ?, ?, ?, NOW())
                    ");
					$stmt->bind_param(
						"isis",
						$recipient['id'],
						$email,
						$recipient['content_id'],
						$messageId
					);
					$stmt->execute();
					debugLog("Unsubscribe processed for: $email");
				}
			}

			$db->commit();
			debugLog("Committed transaction for message ID: $messageId");

		} catch (Exception $e) {
			$db->rollback();
			debugLog("Error processing event: " . $e->getMessage());
			throw $e;
		}
	}

	// Erfolgreiche Antwort
	http_response_code(200);
	$response = [
		'success' => true,
		'message' => 'Events processed',
		'count' => count($events),
		'timestamp' => date('c')
	];
	debugLog("Success response: " . json_encode($response));
	echo json_encode($response);

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

debugLog("Script completed");