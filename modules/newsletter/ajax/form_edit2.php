<?php
// Zugangsdaten für die Datenbank
require_once(__DIR__ . '/../n_config.php');

// Funktion zur Validierung und Bereinigung von Eingaben
function sanitizeInput($input)
{
	return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Funktion zur Überprüfung der Gültigkeit einer E-Mail-Adresse
function isValidEmail($email)
{
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Funktion zum sicheren Speichern von Empfängergruppen
function saveRecipientGroups($db, $recipient_id, $group_ids)
{
	$db->begin_transaction();
	try {
		// Zuerst alle bestehenden Gruppenbeziehungen des Empfängers löschen
		$stmt = $db->prepare("DELETE FROM recipient_group WHERE recipient_id = ?");
		$stmt->bind_param("i", $recipient_id);
		$stmt->execute();

		// Neue Gruppenbeziehungen hinzufügen
		$stmt = $db->prepare("INSERT INTO recipient_group (recipient_id, group_id) VALUES (?, ?)");
		foreach ($group_ids as $group_id) {
			if (!empty($group_id) && is_numeric($group_id)) {
				$stmt->bind_param("ii", $recipient_id, $group_id);
				$stmt->execute();
			}
		}
		$db->commit();
	} catch (Exception $e) {
		$db->rollback();
		throw $e;
	}
}

// Hauptlogik
try {
	$id = isset($_POST['update_id']) ? intval($_POST['update_id']) : null;
	$list_id = sanitizeInput($_POST['list_id'] ?? '');

	if (!in_array($list_id, ['senders', 'recipients', 'newsletters', 'groups'])) {
		throw new Exception("Ungültige Liste angegeben");
	}

	$db->begin_transaction();

	switch ($list_id) {
		case 'senders':
		case 'recipients':
			$data = [
				'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
				'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
				'company' => sanitizeInput($_POST['company'] ?? ''),
				'email' => sanitizeInput($_POST['email'] ?? ''),
				'gender' => in_array($_POST['gender'] ?? '', ['male', 'female', 'other']) ? $_POST['gender'] : 'other',
				'title' => sanitizeInput($_POST['title'] ?? ''),
				'comment' => sanitizeInput($_POST['comment'] ?? '')
			];

			if (!isValidEmail($data['email'])) {
				throw new Exception("Ungültige E-Mail-Adresse");
			}

			if ($id) {
				$stmt = $db->prepare("UPDATE $list_id SET first_name = ?, last_name = ?, company = ?, email = ?, gender = ?, title = ?, comment = ? WHERE id = ?");
				$stmt->bind_param("sssssssi", $data['first_name'], $data['last_name'], $data['company'], $data['email'], $data['gender'], $data['title'], $data['comment'], $id);
			} else {
				$stmt = $db->prepare("INSERT INTO $list_id (first_name, last_name, company, email, gender, title, comment) VALUES (?, ?, ?, ?, ?, ?, ?)");
				$stmt->bind_param("sssssss", $data['first_name'], $data['last_name'], $data['company'], $data['email'], $data['gender'], $data['title'], $data['comment']);
			}
			$stmt->execute();
			$affected_id = $id ?? $stmt->insert_id;

			if ($list_id === 'recipients') {
				$group_ids = array_filter(array_map('intval', explode(',', $_POST['tags'] ?? '')));
				saveRecipientGroups($db, $affected_id, $group_ids);
			}
			break;

		case 'newsletters':
			$sender_id = intval($_POST['sender_id'] ?? 0);
			$subject = sanitizeInput($_POST['subject'] ?? '');
			$message = $_POST['message'] ?? ''; // Nicht sanitieren, da es HTML enthalten kann
			$message = prepareHtmlForEmail($message);

			$group_ids = array_filter(array_map('intval', explode(',', $_POST['tags'] ?? '')));

			if ($id) {
				$stmt = $db->prepare("UPDATE email_contents SET sender_id = ?, subject = ?, message = ? WHERE id = ?");
				$stmt->bind_param("issi", $sender_id, $subject, $message, $id);
			} else {
				$stmt = $db->prepare("INSERT INTO email_contents (sender_id, subject, message) VALUES (?, ?, ?)");
				$stmt->bind_param("iss", $sender_id, $subject, $message);
			}
			$stmt->execute();
			$email_content_id = $id ?? $db->insert_id;

			// Bestehende Gruppen-Zuordnungen löschen
			$stmt = $db->prepare("DELETE FROM email_content_groups WHERE email_content_id = ?");
			$stmt->bind_param("i", $email_content_id);
			$stmt->execute();

			// Neue Gruppen-Zuordnungen einfügen
			if (!empty($group_ids)) {
				$stmt = $db->prepare("INSERT INTO email_content_groups (email_content_id, group_id) VALUES (?, ?)");
				foreach ($group_ids as $group_id) {
					$stmt->bind_param("ii", $email_content_id, $group_id);
					$stmt->execute();
				}
			}

			// Aktualisiere den send_status, falls nötig
			$send_status = 0; // Standardwert, kann je nach Anforderung angepasst werden
			$stmt = $db->prepare("UPDATE email_contents SET send_status = ? WHERE id = ?");
			$stmt->bind_param("ii", $send_status, $email_content_id);
			$stmt->execute();

			break;
		case 'groups':
			$name = sanitizeInput($_POST['name'] ?? '');
			$description = sanitizeInput($_POST['description'] ?? '');
			$color = sanitizeInput($_POST['color'] ?? '');

			if ($id) {
				$stmt = $db->prepare("UPDATE groups SET name = ?, description = ?, color = ? WHERE id = ?");
				$stmt->bind_param("sssi", $name, $description, $color, $id);
			} else {
				$stmt = $db->prepare("INSERT INTO groups (name, description, color) VALUES (?, ?, ?)");
				$stmt->bind_param("sss", $name, $description, $color);
			}
			$stmt->execute();
			break;
	}

	$db->commit();
	echo json_encode(['success' => true, 'message' => 'Daten erfolgreich gespeichert']);
} catch (Exception $e) {
	$db->rollback();
	echo json_encode(['success' => false, 'message' => 'Fehler: ' . $e->getMessage()]);
} finally {
	$db->close();
}
