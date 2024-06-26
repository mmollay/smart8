<?
// Zugangsdaten fuer die Datenbank
include (__DIR__ . '/../n_config.php');

$id = $_POST['update_id'] ?? null;

switch ($_POST['list_id']) {
	case 'senders':
		$first_name = $_POST['first_name'] ?? null;
		$last_name = $_POST['last_name'] ?? null;
		$company = $_POST['company'] ?? null;
		$email = $_POST['email'] ?? null;
		$gender = $_POST['gender'] ?? null;
		$title = $_POST['title'] ?? null;
		$comment = $_POST['comment'] ?? null;

		// Sicherstellen, dass der Wert für das Geschlecht korrekt ist
		$valid_genders = ['male', 'female', 'other'];
		if (!in_array($gender, $valid_genders)) {
			$gender = 'other'; // Standardwert, falls ungültig
		}

		if ($id) {

			// UPDATE
			$stmt = $db->prepare("UPDATE senders SET first_name = ?, last_name = ?, company = ?, email = ?, gender = ?, title = ?, comment = ? WHERE id = ?");
			$stmt->bind_param("sssssssi", $first_name, $last_name, $company, $email, $gender, $title, $comment, $id);
		} else {

			// INSERT
			$stmt = $db->prepare("INSERT INTO senders (first_name, last_name, company, email, gender, title, comment) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("sssssss", $first_name, $last_name, $company, $email, $gender, $title, $comment);
		}
		break;

	case 'recipients':
		$first_name = $_POST['first_name'] ?? null;
		$last_name = $_POST['last_name'] ?? null;
		$company = $_POST['company'] ?? null;
		$email = $_POST['email'] ?? null;
		$gender = $_POST['gender'] ?? null;
		$title = $_POST['title'] ?? null;
		$comment = $_POST['comment'] ?? null;
		$group_ids = explode(',', $_POST['tags'] ?? '');

		// Sicherstellen, dass der Wert für das Geschlecht korrekt ist
		$valid_genders = ['male', 'female', 'other'];
		if (!in_array($gender, $valid_genders)) {
			$gender = 'other'; // Standardwert, falls ungültig
		}

		if ($id) {
			// UPDATE
			$stmt = $db->prepare("UPDATE recipients SET first_name = ?, last_name = ?, company = ?, email = ?, gender = ?, title = ?, comment = ? WHERE id = ?");
			$stmt->bind_param("sssssssi", $first_name, $last_name, $company, $email, $gender, $title, $comment, $id);
		} else {
			// INSERT
			$stmt = $db->prepare("INSERT INTO recipients (first_name, last_name, company, email, gender, title, comment) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("sssssss", $first_name, $last_name, $company, $email, $gender, $title, $comment);
		}

		if ($stmt->execute()) {
			if (!$id) {
				$id = $stmt->insert_id; // Hole die ID des neu eingefügten Datensatzes
			}
			$stmt->close();
			saveRecipientGroups($db, $id, $group_ids);
			//	echo "Empfänger und Gruppen wurden erfolgreich gespeichert.";
		} else {
			echo "Fehler: " . $stmt->error;
		}

		break;

	case 'newsletters':
		$sender_id = $_POST['sender_id'] ?? null;
		$subject = $_POST['subject'] ?? null;
		$message = $_POST['message'] ?? null;
		if ($id) {

			// UPDATE
			$stmt = $db->prepare("UPDATE email_contents SET sender_id = ?, subject = ?, message = ? WHERE id = ?");
			$stmt->bind_param("issi", $sender_id, $subject, $message, $id);
		} else {
			// INSERT
			$stmt = $db->prepare("INSERT INTO email_contents (sender_id, subject, message) VALUES (?, ?, ?)");
			$stmt->bind_param("iss", $sender_id, $subject, $message);
		}
		break;
	case 'groups':
		$name = $_POST['name'] ?? null;
		$description = $_POST['description'] ?? null;
		$color = $_POST['color'] ?? null;


		if ($id) {
			// UPDATE
			$stmt = $db->prepare("UPDATE groups SET name = ?, description = ?, color = ? WHERE id = ?");
			$stmt->bind_param("sssi", $name, $description, $color, $id);
		} else {

			// INSERT
			$stmt = $db->prepare("INSERT INTO groups (name, description) VALUES (?, ?, ?)");
			$stmt->bind_param("sss", $name, $description, $color);
		}
		break;
	default:
		echo "Ungültige Anfrage.";
		$default = true;
		break;

}

if (!$default) {
	if ($stmt->execute()) {
		echo "ok";
	} else {
		echo "Fehler: " . $stmt->error;
	}
	$stmt->close();
	$db->close();
}



function saveRecipientGroups($db, $recipient_id, $group_ids)
{
	// Zuerst alle bestehenden Gruppenbeziehungen des Empfängers löschen
	$stmt = $db->prepare("DELETE FROM recipient_group WHERE recipient_id = ?");
	$stmt->bind_param("i", $recipient_id);
	$stmt->execute();
	$stmt->close();

	// Neue Gruppenbeziehungen hinzufügen
	foreach ($group_ids as $group_id) {
		$stmt = $db->prepare("INSERT INTO recipient_group (recipient_id, group_id) VALUES (?, ?)");
		$stmt->bind_param("ii", $recipient_id, $group_id);
		$stmt->execute();
		$stmt->close();
	}
}