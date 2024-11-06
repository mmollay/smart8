<?php
require_once(__DIR__ . '/../n_config.php');

function handleDatabaseOperation($db, $operation, $table, $data, $id = null)
{
	$columns = implode(", ", array_keys($data));
	$placeholders = implode(", ", array_fill(0, count($data), "?"));
	$types = str_repeat("s", count($data));
	$values = array_values($data);

	if ($operation === 'INSERT') {
		$sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
	} else {
		$set = implode(" = ?, ", array_keys($data)) . " = ?";
		$sql = "UPDATE $table SET $set WHERE id = ?";
		$types .= "i";
		$values[] = $id;
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
	$group_ids = explode(',', $group_ids);

	$id_column = $table === 'email_content_groups' ? 'email_content_id' : 'recipient_id';

	$stmt = $db->prepare("DELETE FROM $table WHERE $id_column = ?");
	$stmt->bind_param("i", $content_id);
	$stmt->execute();
	$stmt->close();

	if (!empty($group_ids)) {
		$stmt = $db->prepare("INSERT INTO $table ($id_column, group_id) VALUES (?, ?)");
		foreach ($group_ids as $group_id) {
			if (!empty($group_id)) {
				$stmt->bind_param("ii", $content_id, $group_id);
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

$db->begin_transaction();
try {
	$list_id = sanitizeInput($_POST['list_id'] ?? '');
	$id = isset($_POST['update_id']) ? intval($_POST['update_id']) : null;
	$operation = $id ? 'UPDATE' : 'INSERT';
	$tags = $_POST['tags'][0];
	$tags = isset($_POST['tags']) ? (is_array($_POST['tags']) ? $_POST['tags'] : explode(',', $_POST['tags'])) : [];
	$group_ids = array_filter(array_map('intval', $tags));
	$group_ids = $tags[0];
	switch ($list_id) {
		case 'senders':
		case 'recipients':
			$data = [
				'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
				'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
				'company' => sanitizeInput($_POST['company'] ?? ''),
				'email' => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
				'gender' => in_array($_POST['gender'] ?? '', ['male', 'female', 'other']) ? $_POST['gender'] : 'other',
				'title' => sanitizeInput($_POST['title'] ?? ''),
				'comment' => sanitizeInput($_POST['comment'] ?? '')
			];
			$affected_id = handleDatabaseOperation($db, $operation, $list_id, $data, $id);
			if ($list_id === 'recipients') {
				saveGroups($db, 'recipient_group', $affected_id, $group_ids);
			}
			break;

		case 'newsletters':
			$data = [
				'sender_id' => intval($_POST['sender_id'] ?? 0),
				'subject' => sanitizeInput($_POST['subject'] ?? ''),
				'message' => $_POST['message'] ?? '', // Nicht sanitieren, da es HTML enthalten kann
				'send_status' => 0 // Setze den Standard-Status
			];
			$affected_id = handleDatabaseOperation($db, $operation, 'email_contents', $data, $id);
			saveGroups($db, 'email_content_groups', $affected_id, $group_ids);

			break;

		case 'groups':
			$data = [
				'name' => sanitizeInput($_POST['name'] ?? ''),
				'description' => sanitizeInput($_POST['description'] ?? ''),
				'color' => sanitizeInput($_POST['color'] ?? '')
			];
			handleDatabaseOperation($db, $operation, 'groups', $data, $id);
			break;

		default:
			throw new Exception("Ungültige Anfrage.");
	}

	$db->commit();
	echo json_encode(['success' => true, 'message' => 'Daten erfolgreich gespeichert']);
} catch (Exception $e) {
	$db->rollback();
	echo json_encode(['success' => false, 'message' => 'Fehler: ' . $e->getMessage()]);
} finally {
	$db->close();
}
?>