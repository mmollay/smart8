<?php
require_once(__DIR__ . '/../n_config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Ungültige Anfragemethode']));
}

$delete_id = intval($_POST['delete_id']);
$list_id = $_POST['list_id'];

if (!$delete_id || !$list_id) {
    die(json_encode(['success' => false, 'message' => 'Ungültige Parameter']));
}

$db->begin_transaction();

try {
    switch ($list_id) {
        case 'senders':
            $stmt = $db->prepare("DELETE FROM senders WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            break;

        case 'recipients':
            // Lösche verknüpfte Einträge in email_logs und email_jobs
            $stmt = $db->prepare("DELETE el FROM email_logs el 
                                  JOIN email_jobs ej ON el.job_id = ej.id 
                                  WHERE ej.recipient_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            $stmt = $db->prepare("DELETE FROM email_jobs WHERE recipient_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            // Lösche Verknüpfungen in recipient_group
            $stmt = $db->prepare("DELETE FROM recipient_group WHERE recipient_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            // Lösche den Empfänger
            $stmt = $db->prepare("DELETE FROM recipients WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            break;

        case 'groups':
            // Lösche Verknüpfungen in recipient_group und email_content_groups
            $stmt = $db->prepare("DELETE FROM recipient_group WHERE group_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            $stmt = $db->prepare("DELETE FROM email_content_groups WHERE group_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            // Lösche die Gruppe
            $stmt = $db->prepare("DELETE FROM groups WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            break;

        case 'newsletters':
            // Lösche verknüpfte Einträge in email_tracking
            $stmt = $db->prepare("DELETE et FROM email_tracking et 
                                JOIN email_jobs ej ON et.job_id = ej.id 
                                WHERE ej.content_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            // Lösche verknüpfte Einträge in email_logs
            $stmt = $db->prepare("DELETE el FROM email_logs el 
                                JOIN email_jobs ej ON el.job_id = ej.id 
                                WHERE ej.content_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            // Lösche Einträge in email_jobs
            $stmt = $db->prepare("DELETE FROM email_jobs WHERE content_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            // Lösche Verknüpfungen in email_content_groups
            $stmt = $db->prepare("DELETE FROM email_content_groups WHERE email_content_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            // Lösche Einträge in newsletter_attachments
            $stmt = $db->prepare("DELETE FROM newsletter_attachments WHERE newsletter_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            // Lösche die physischen Attachment-Dateien
            //$upload_dir = __DIR__ . "/../../uploads/users/{$delete_id}/";
            $upload_dir = "../../../uploads/users/{$delete_id}/";
            if (file_exists($upload_dir)) {
                $files = glob($upload_dir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                // Versuche das Verzeichnis zu löschen
                rmdir($upload_dir);
            }

            // Lösche den Newsletter selbst
            $stmt = $db->prepare("DELETE FROM email_contents WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            break;

        default:
            throw new Exception("Ungültige Liste angegeben");
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Eintrag erfolgreich gelöscht']);
} catch (Exception $e) {
    $db->rollback();
    error_log("Fehler beim Löschen: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Fehler beim Löschen: ' . $e->getMessage()]);
}
?>