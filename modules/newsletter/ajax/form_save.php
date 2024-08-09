<?php
require_once (__DIR__ . '/../n_config.php');

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

	$db->query("DELETE FROM $table WHERE {$table}_id = $content_id");
	$stmt = $db->prepare("INSERT INTO $table ({$table}_id, group_id) VALUES (?, ?)");

	foreach ($group_ids as $group_id) {

		if (!empty($group_id)) {

			$stmt->bind_param("ii", $content_id, $group_id);
			$stmt->execute();
		}
	}
	$stmt->close();
}

$db->begin_transaction();

try {
	$list_id = $_POST['list_id'] ?? '';
	$id = $_POST['update_id'] ?? null;
	$operation = $id ? 'UPDATE' : 'INSERT';

	//muss ich noch anpassen?! 
	$_POST['tags'] = $_POST['tags'][0];

	switch ($list_id) {
		case 'senders':
		case 'recipients':
			$data = [
				'first_name' => $_POST['first_name'] ?? null,
				'last_name' => $_POST['last_name'] ?? null,
				'company' => $_POST['company'] ?? null,
				'email' => $_POST['email'] ?? null,
				'gender' => in_array($_POST['gender'] ?? '', ['male', 'female', 'other']) ? $_POST['gender'] : 'other',
				'title' => $_POST['title'] ?? null,
				'comment' => $_POST['comment'] ?? null
			];
			$affected_id = handleDatabaseOperation($db, $operation, $list_id, $data, $id);

			if ($list_id === 'recipients') {

				$group_ids = isset($_POST['tags']) ? (is_array($_POST['tags']) ? $_POST['tags'] : explode(',', $_POST['tags'])) : [];

				saveGroups($db, 'recipient_group', $affected_id, $group_ids);
			}
			break;

		case 'newsletters':
			$data = [
				'sender_id' => $_POST['sender_id'] ?? null,
				'subject' => $_POST['subject'] ?? null,
				'message' => $_POST['message'] ?? null
			];
			$affected_id = handleDatabaseOperation($db, $operation, 'email_contents', $data, $id);
			$group_ids = isset($_POST['tags']) ? (is_array($_POST['tags']) ? $_POST['tags'] : explode(',', $_POST['tags'])) : [];
			saveGroups($db, 'email_content_groups', $affected_id, $group_ids);
			break;

		case 'groups':
			$data = [
				'name' => $_POST['name'] ?? null,
				'description' => $_POST['description'] ?? null,
				'color' => $_POST['color'] ?? null
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
}

$db->close();
?>