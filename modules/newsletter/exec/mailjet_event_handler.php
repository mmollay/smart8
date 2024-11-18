<?php
require_once(__DIR__ . '/../n_config.php');

// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

try {
	// Initialize database connection
	$db = new mysqli($host, $username, $password, $dbname);
	if ($db->connect_error) {
		throw new Exception("Database connection failed: " . $db->connect_error);
	}
	$db->set_charset('utf8mb4');

	// Get JSON payload from Mailjet
	$payload = file_get_contents('php://input');
	$events = json_decode($payload, true);

	if (empty($events)) {
		throw new Exception('No events received');
	}

	// Convert single event to array
	if (!isset($events[0])) {
		$events = [$events];
	}

	// Process each event
	foreach ($events as $event) {
		// Validate required fields
		if (empty($event['MessageID']) || empty($event['event'])) {
			continue;
		}

		// Extract event data
		$messageId = $event['MessageID'];
		$eventType = $event['event'];
		$email = $event['email'] ?? '';
		$timestamp = date('Y-m-d H:i:s');

		// Log the event
		$stmt = $db->prepare("
            INSERT INTO status_log 
            (event, timestamp, message_id, email) 
            VALUES (?, NOW(), ?, ?)
        ");
		$stmt->bind_param("sss", $eventType, $messageId, $email);
		$stmt->execute();

		// Update email job status
		$updateStatus = match ($eventType) {
			'bounced', 'blocked' => 'failed',
			'spam', 'unsub' => 'rejected',
			default => $eventType
		};

		$stmt = $db->prepare("
            UPDATE email_jobs 
            SET 
                status = ?,
                updated_at = NOW()
            WHERE message_id = ? AND status != 'unsub'
        ");
		$stmt->bind_param("ss", $updateStatus, $messageId);
		$stmt->execute();

		// Handle unsubscribe events
		if ($eventType === 'unsub' && !empty($email)) {
			// Get recipient details
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
				// Update recipient status
				$stmt = $db->prepare("
                    UPDATE recipients 
                    SET 
                        status = 'unsubscribed',
                        unsubscribed_at = NOW() 
                    WHERE id = ?
                ");
				$stmt->bind_param("i", $recipient['id']);
				$stmt->execute();

				// Log unsubscribe
				$stmt = $db->prepare("
                    INSERT INTO unsubscribe_log 
                    (recipient_id, email, content_id, message_id, timestamp) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
				$stmt->bind_param("isis", $recipient['id'], $email, $recipient['content_id'], $messageId);
				$stmt->execute();
			}
		}
	}

	http_response_code(200);
	echo json_encode(['success' => true]);

} catch (Exception $e) {
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'error' => $e->getMessage()
	]);
} finally {
	if (isset($db) && $db instanceof mysqli) {
		$db->close();
	}
}