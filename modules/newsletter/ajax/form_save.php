<?php
require_once(__DIR__ . '/../n_config.php');

// Start der Hauptverarbeitung
$db->begin_transaction();

try {
	$list_id = sanitizeInput($_POST['list_id'] ?? '');
	$id = isset($_POST['update_id']) ? intval($_POST['update_id']) : null;
	$operation = $id ? 'UPDATE' : 'INSERT';
	$group_ids = $_POST['tags'][0];

	// Bei UPDATE: Prüfe ob der Datensatz dem User gehört
	if ($id) {
		$check_sql = "SELECT id FROM $list_id WHERE id = ? AND user_id = ?";
		if ($list_id === 'newsletters') {
			$check_sql = "SELECT id FROM email_contents WHERE id = ? AND user_id = ?";
		}

		$stmt = $db->prepare($check_sql);
		$stmt->bind_param("ii", $id, $userId);
		$stmt->execute();
		if ($stmt->get_result()->num_rows === 0) {
			throw new Exception("Keine Berechtigung zum Bearbeiten dieses Eintrags");
		}
		$stmt->close();
	}

	switch ($list_id) {
		case 'blacklist':
			$data = [
				'email' => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
				'reason' => sanitizeInput($_POST['reason'] ?? ''),
				'source' => 'manual',  // Da es über das Formular gespeichert wird
				'created_by' => $userId // Speichern wer den Eintrag erstellt hat
			];

			// Prüfe ob die E-Mail bereits in der Blacklist ist
			if ($operation === 'INSERT') {
				$check = $db->prepare("SELECT id FROM blacklist WHERE email = ? AND user_id = ?");
				$check->bind_param("si", $data['email'], $userId);
				$check->execute();
				if ($check->get_result()->num_rows > 0) {
					throw new Exception("Diese E-Mail-Adresse ist bereits auf der Blacklist");
				}
				$check->close();
			}

			$affected_id = handleDatabaseOperation($db, $operation, 'blacklist', $data, $id);

			// Wenn die E-Mail einem Empfänger gehört, setze dessen Status
			$update_recipient = $db->prepare("
				UPDATE recipients 
				SET unsubscribed = 1, 
					unsubscribed_at = NOW() 
				WHERE email = ? 
				AND user_id = ?
			");
			$update_recipient->bind_param("si", $data['email'], $userId);
			$update_recipient->execute();
			$update_recipient->close();
			break;
		case 'senders':
			$data = [
				'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
				'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
				'company' => sanitizeInput($_POST['company'] ?? ''),
				'email' => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
				'test_email' => filter_var($_POST['test_email'] ?? ''),
				'gender' => in_array($_POST['gender'] ?? '', ['male', 'female', 'other']) ? $_POST['gender'] : 'other',
				'title' => sanitizeInput($_POST['title'] ?? ''),
				'comment' => sanitizeInput($_POST['comment'] ?? '')
			];

			if (empty($data['test_email'])) {
				unset($data['test_email']);
			}

			$affected_id = handleDatabaseOperation($db, $operation, 'senders', $data, $id);
			break;

		case 'recipients':
			// Prüfe ob die Email für diesen User bereits existiert
			$check_email = $db->prepare("
					SELECT id, email 
					FROM recipients 
					WHERE email = ? 
					AND user_id = ? 
					AND id != COALESCE(?, 0)
				");
			$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
			$check_email->bind_param("sii", $email, $userId, $id);
			$check_email->execute();
			$result = $check_email->get_result();

			if ($result->num_rows > 0) {
				$existing = $result->fetch_assoc();
				throw new Exception("Die E-Mail-Adresse '{$existing['email']}' existiert bereits in Ihrer Empfängerliste");
			}
			$check_email->close();

			// Check vorherigen Status
			if ($operation === 'UPDATE') {
				$stmt = $db->prepare("SELECT unsubscribed FROM recipients WHERE id = ?");
				$stmt->bind_param("i", $id);
				$stmt->execute();
				$oldStatus = $stmt->get_result()->fetch_assoc()['unsubscribed'];

				// Log wenn Admin zurücksetzt
				if ($oldStatus == 1 && !isset($_POST['unsubscribed'])) {
					$stmt = $db->prepare("
							INSERT INTO unsubscribe_log 
							(recipient_id, email, content_id, message_id, timestamp)
							VALUES (?, ?, 0, 'ADMIN_RESET', NOW())
						");
					$stmt->bind_param("is", $id, $_POST['email']);
					$stmt->execute();
				}
			}

			$data = [
				'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
				'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
				'company' => sanitizeInput($_POST['company'] ?? ''),
				'email' => $email,
				'gender' => in_array($_POST['gender'] ?? '', ['male', 'female', 'other']) ? $_POST['gender'] : 'other',
				'title' => sanitizeInput($_POST['title'] ?? ''),
				'comment' => sanitizeInput($_POST['comment'] ?? ''),
				'unsubscribed' => isset($_POST['unsubscribed']) ? 1 : 0,
				'unsubscribed_at' => isset($_POST['unsubscribed']) ? date('Y-m-d H:i:s') : null
			];

			$affected_id = handleDatabaseOperation($db, $operation, 'recipients', $data, $id);

			if (!empty($group_ids)) {
				saveGroups($db, 'recipient_group', $affected_id, $group_ids);
			}
			break;

		case 'newsletters':
			// Prüfe ob der sender_id zum User gehört
			$sender_id = intval($_POST['sender_id'] ?? 0);
			$stmt = $db->prepare("SELECT id FROM senders WHERE id = ? AND user_id = ?");
			$stmt->bind_param("ii", $sender_id, $userId);
			$stmt->execute();
			if ($stmt->get_result()->num_rows === 0) {
				throw new Exception("Ungültiger Absender ausgewählt");
			}
			$stmt->close();

			$data = [
				'sender_id' => $sender_id,
				'subject' => sanitizeInput($_POST['subject'] ?? ''),
				'message' => $_POST['message'] ?? '',
				'send_status' => 0
			];

			$affected_id = handleDatabaseOperation($db, $operation, 'email_contents', $data, $id);

			if (!empty($group_ids)) {
				saveGroups($db, 'email_content_groups', $affected_id, $group_ids);
			}
			break;

		case 'groups':
			$data = [
				'name' => sanitizeInput($_POST['name'] ?? ''),
				'description' => sanitizeInput($_POST['description'] ?? ''),
				'color' => sanitizeInput($_POST['color'] ?? '')
			];

			$affected_id = handleDatabaseOperation($db, $operation, 'groups', $data, $id);
			break;

		default:
			throw new Exception("Ungültige Anfrage: Unbekannte Liste '$list_id'");
	}

	$db->commit();
	echo json_encode([
		'success' => true,
		'message' => 'Daten erfolgreich gespeichert',
		'id' => $affected_id ?? null
	]);

} catch (Exception $e) {
	$db->rollback();
	error_log("Fehler in form_handler.php: " . $e->getMessage());
	echo json_encode([
		'success' => false,
		'message' => 'Fehler: ' . $e->getMessage()
	]);

} finally {
	if (isset($db) && $db->ping()) {
		$db->close();
	}
}


function handleDatabaseOperation($db, $operation, $table, $data, $id = null)
{
	global $userId;

	// Füge user_id zu den Daten hinzu
	$data['user_id'] = $userId;

	$columns = implode(", ", array_keys($data));
	$placeholders = implode(", ", array_fill(0, count($data), "?"));
	$types = str_repeat("s", count($data));
	$values = array_values($data);

	if ($operation === 'INSERT') {
		$sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
	} else {
		$set = implode(" = ?, ", array_keys($data)) . " = ?";
		$sql = "UPDATE $table SET $set WHERE id = ? AND user_id = ?";
		$types .= "ii";
		$values[] = $id;
		$values[] = $userId;
	}

	$stmt = $db->prepare($sql);
	if (!$stmt) {
		throw new Exception("Prepare failed: " . $db->error);
	}

	$stmt->bind_param($types, ...$values);
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: " . $stmt->error);
	}

	$affected_id = $operation === 'INSERT' ? $stmt->insert_id : $id;
	$stmt->close();
	return $affected_id;
}

function saveGroups($db, $table, $content_id, $group_ids)
{
	global $userId;

	// Konvertiere group_ids zu Array, falls als String übergeben
	if (!is_array($group_ids)) {
		$group_ids = explode(',', $group_ids);
	}

	// Bestimme den korrekten Spaltenname basierend auf der Tabelle
	$id_column = $table === 'email_content_groups' ? 'email_content_id' : 'recipient_id';

	// Lösche existierende Verknüpfungen, aber nur für Gruppen die dem User gehören
	$stmt = $db->prepare("
        DELETE t FROM $table t 
        INNER JOIN groups g ON t.group_id = g.id 
        WHERE t.$id_column = ? AND g.user_id = ?
    ");
	$stmt->bind_param("ii", $content_id, $userId);
	$stmt->execute();
	$stmt->close();

	// Füge neue Verknüpfungen hinzu, aber prüfe ob die Gruppen dem User gehören
	if (!empty($group_ids)) {
		$stmt = $db->prepare("
            INSERT INTO $table ($id_column, group_id)


			
            SELECT ?, g.id 
            FROM groups g 
            WHERE g.id = ? AND g.user_id = ?
        ");
		foreach ($group_ids as $group_id) {
			if (!empty($group_id)) {
				$stmt->bind_param("iii", $content_id, $group_id, $userId);
				$stmt->execute();
			}
		}
		$stmt->close();
	}
}

function sanitizeInput($input)
{
	return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
