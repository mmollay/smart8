<?php
namespace Newsletter;
define('BASE_PATH', dirname(__DIR__));

// Grundeinstellungen
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/mailjet_webhook.log');

require_once BASE_PATH . '/n_config.php';

class WebhookHandler
{
	private $db;
	private $isDebug;
	private $validEvents = [
		'sent',
		'delivered',
		'opened',
		'clicked',
		'bounced',
		'blocked',
		'spam',
		'unsub'
	];

	private $statusMapping = [
		'sent' => 'send',
		'delivered' => 'delivered',
		'opened' => 'open',
		'clicked' => 'click',
		'bounced' => 'failed',
		'blocked' => 'failed',
		'spam' => 'rejected',
		'unsub' => 'unsub'
	];

	public function __construct($db, $isDebug = false)
	{
		$this->db = $db;
		$this->isDebug = $isDebug;
	}

	private function log($message, $type = 'INFO')
	{
		$timestamp = date('Y-m-d H:i:s');
		$logMessage = "[$timestamp][$type] $message" . PHP_EOL;
		error_log($logMessage, 3, BASE_PATH . '/logs/mailjet_webhook.log');

		if ($this->isDebug) {
			error_log($logMessage);
		}
	}

	public function handleEvent()
	{
		try {
			// JSON Payload empfangen
			$payload = file_get_contents('php://input');
			if (empty($payload)) {
				throw new \Exception('Keine Daten empfangen');
			}

			$this->log("Empfangene Daten: " . $payload);

			// JSON decodieren
			$events = json_decode($payload, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new \Exception('Ungültiges JSON Format: ' . json_last_error_msg());
			}

			// Einzelnes Event in Array umwandeln
			if (!isset($events[0])) {
				$events = [$events];
			}

			// Events verarbeiten
			foreach ($events as $event) {
				$this->processEvent($event);
			}

			return $this->sendResponse(true);

		} catch (\Exception $e) {
			$this->log("Fehler: " . $e->getMessage(), 'ERROR');
			return $this->sendResponse(false, $e->getMessage());
		}
	}

	private function processEvent($event)
	{
		// Pflichtfelder prüfen
		if (empty($event['MessageID']) || empty($event['event'])) {
			$this->log("Ungültiges Event Format: " . print_r($event, true), 'WARNING');
			return;
		}

		$messageId = $event['MessageID'];
		$eventType = strtolower($event['event']);
		$email = $event['email'] ?? '';

		// Event-Typ validieren
		if (!in_array($eventType, $this->validEvents)) {
			$this->log("Unbekannter Event-Typ: $eventType", 'WARNING');
			return;
		}

		try {
			$this->db->begin_transaction();

			// Event loggen
			$this->logEventStatus($eventType, $messageId, $email);

			// Email-Job Status aktualisieren
			$this->updateEmailJobStatus($messageId, $eventType);

			// Abmeldungen verarbeiten
			if ($eventType === 'unsub') {
				$this->handleUnsubscribe($messageId, $email);
			}

			$this->db->commit();
			$this->log("Event erfolgreich verarbeitet: $eventType für Message ID $messageId");

		} catch (\Exception $e) {
			$this->db->rollback();
			throw new \Exception("Fehler bei der Verarbeitung von Event $eventType: " . $e->getMessage());
		}
	}

	private function logEventStatus($eventType, $messageId, $email)
	{
		$stmt = $this->db->prepare("
            INSERT INTO status_log (event, timestamp, message_id, email)
            VALUES (?, NOW(), ?, ?)
        ");
		$stmt->bind_param("sss", $eventType, $messageId, $email);
		$stmt->execute();
	}

	private function updateEmailJobStatus($messageId, $eventType)
	{
		$status = $this->statusMapping[$eventType] ?? $eventType;

		$stmt = $this->db->prepare("
            UPDATE email_jobs
            SET 
                status = ?,
                updated_at = NOW()
            WHERE message_id = ? 
            AND status != 'unsub'
        ");
		$stmt->bind_param("ss", $status, $messageId);
		$stmt->execute();
	}

	private function handleUnsubscribe($messageId, $email)
	{
		// Empfänger Details holen
		$stmt = $this->db->prepare("
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

		if (!$recipient) {
			$this->log("Kein Empfänger gefunden für Abmeldung: $email", 'WARNING');
			return;
		}

		// Empfänger Status aktualisieren
		$stmt = $this->db->prepare("
            UPDATE recipients
            SET 
                status = 'unsubscribed',
                unsubscribed_at = NOW()
            WHERE id = ?
        ");
		$stmt->bind_param("i", $recipient['id']);
		$stmt->execute();

		// Abmeldung loggen
		$stmt = $this->db->prepare("
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

		$this->log("Abmeldung verarbeitet für: $email");
	}

	private function sendResponse($success, $error = null)
	{
		http_response_code($success ? 200 : 500);
		return json_encode([
			'success' => $success,
			'timestamp' => date('c'),
			'error' => $error
		]);
	}
}

// Handler initialisieren und ausführen
try {
	$isDebug = getenv('APP_ENV') === 'development';
	$handler = new WebhookHandler($db, $isDebug);
	echo $handler->handleEvent();
} catch (Exception $e) {
	error_log("Kritischer Fehler: " . $e->getMessage());
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'error' => 'Internal Server Error'
	]);
}