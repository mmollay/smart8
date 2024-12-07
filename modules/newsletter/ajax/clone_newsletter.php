<?php
include(__DIR__ . '/../n_config.php');
header('Content-Type: application/json');

function sendJsonResponse($status, $message)
{
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

function copyDirectory($source, $destination)
{
    if (!is_dir($source)) {
        return false;
    }
    if (!is_dir($destination)) {
        if (!mkdir($destination, 0777, true)) {
            throw new Exception('Fehler beim Erstellen des Zielverzeichnisses.');
        }
    }
    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $srcFile = $source . '/' . $file;
            $destFile = $destination . '/' . $file;
            if (is_file($srcFile)) {
                if (!copy($srcFile, $destFile)) {
                    throw new Exception('Fehler beim Kopieren der Datei: ' . $file);
                }
            }
        }
    }
    closedir($dir);
    return true;
}

if (!isset($_POST['content_id'])) {
    sendJsonResponse('error', 'Keine content_id übergeben.');
}

$content_id = intval($_POST['content_id']);

try {
    $db->begin_transaction();

    // Prüfe ob der Original-Newsletter dem User gehört
    $stmt = $db->prepare("SELECT id FROM email_contents WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $content_id, $userId);
    $stmt->execute();
    if (!$stmt->get_result()->num_rows) {
        throw new Exception('Keine Berechtigung für diesen Newsletter.');
    }
    $stmt->close();

    // Daten des Originals abrufen
    $stmt = $db->prepare("SELECT sender_id, subject, message FROM email_contents WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $content_id, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $original = $result->fetch_assoc();
    $stmt->close();

    if (!$original) {
        throw new Exception('Original-Newsletter nicht gefunden.');
    }

    // Neuen Newsletter erstellen
    $new_subject = "Kopie von: " . $original['subject'];
    $stmt = $db->prepare("INSERT INTO email_contents (sender_id, subject, message, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $original['sender_id'], $new_subject, $original['message'], $userId);
    $stmt->execute();
    $new_content_id = $db->insert_id;
    $stmt->close();

    // Gruppen kopieren (nur eigene Gruppen)
    $stmt = $db->prepare("
        INSERT INTO email_content_groups (email_content_id, group_id)
        SELECT ?, ecg.group_id 
        FROM email_content_groups ecg
        JOIN groups g ON ecg.group_id = g.id
        WHERE ecg.email_content_id = ? 
        AND g.user_id = ?");
    $stmt->bind_param("iii", $new_content_id, $content_id, $userId);
    $stmt->execute();
    $stmt->close();

    // Attachments in der Datenbank kopieren
    $stmt = $db->prepare("
        INSERT INTO newsletter_attachments (newsletter_id, file_name, file_size, file_type)
        SELECT ?, file_name, file_size, file_type
        FROM newsletter_attachments
        WHERE newsletter_id = ?
        AND newsletter_id IN (SELECT id FROM email_contents WHERE user_id = ?)");
    $stmt->bind_param("iii", $new_content_id, $content_id, $userId);
    $stmt->execute();
    $stmt->close();

    // Dateien physisch kopieren
    $source_dir = $uploadBasePath . "/{$content_id}";
    $dest_dir = $uploadBasePath . "/{$new_content_id}";
    if (is_dir($source_dir)) {
        if (!copyDirectory($source_dir, $dest_dir)) {
            throw new Exception('Fehler beim Kopieren der Anhänge.');
        }
    }

    $db->commit();
    sendJsonResponse('success', 'Newsletter und Anhänge erfolgreich dupliziert.');

} catch (Exception $e) {
    $db->rollback();
    sendJsonResponse('error', $e->getMessage());
} finally {
    if (isset($db)) {
        $db->close();
    }
}