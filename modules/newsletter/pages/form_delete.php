<?php
include (__DIR__ . '/../../../../smartform/include_form.php');

if (!$_POST['delete_id']) {

    $arr['ajax'] = array('success' => "$table_reload $('.modal.ui').modal('hide'); table_reload(); message({ title: 'Entfernen', text:'Ein Eintrag wurde entfernt'});", 'dataType' => "html");
    $arr['hidden']['delete_id'] = $_POST['update_id'];
    $arr['hidden']['list_id'] = $_POST['list_id'];
    $arr['button']['submit'] = array('value' => 'Löschen', 'color' => 'red');
    $arr['button']['close'] = array('value' => 'Abbrechen', 'color' => 'gray', 'js' => "$('.modal.ui').modal('hide');");
    $output = call_form($arr);
    echo $output['html'];
    echo $output['js'];
    exit();
}

require_once ('../n_config.php');

$delete_id = intval($_POST['delete_id']);

switch ($_POST['list_id']) {
    case 'senders':
        $stmt = $GLOBALS['mysqli']->prepare("DELETE FROM senders WHERE id = ? LIMIT 1");
        if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();
            //echo "ok";
            echo $delete_id;
        } else {
            die("Ungültige 'delete_id'");
        }
        break;
    case 'recipients':

        $stmt = $GLOBALS['mysqli']->prepare("DELETE FROM recipients WHERE id = ? LIMIT 1");
        if (isset($delete_id) && is_numeric($delete_id)) {
            // Löschen der Einträge in der Tabelle email_logs, die sich auf die zu löschenden email_jobs beziehen
            $stmt = $GLOBALS['mysqli']->prepare("SELECT id FROM email_jobs WHERE recipient_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $job_ids = [];
            while ($row = $result->fetch_assoc()) {
                $job_ids[] = $row['id'];
            }
            $stmt->close();

            if (!empty($job_ids)) {
                $job_ids_placeholder = implode(',', array_fill(0, count($job_ids), '?'));
                $types = str_repeat('i', count($job_ids));

                $stmt = $GLOBALS['mysqli']->prepare("DELETE FROM email_logs WHERE job_id IN ($job_ids_placeholder)");
                $stmt->bind_param($types, ...$job_ids);
                $stmt->execute();
                $stmt->close();
            }

            // Löschen der Einträge in der Tabelle email_jobs, die sich auf den Empfänger beziehen
            $stmt = $GLOBALS['mysqli']->prepare("DELETE FROM email_jobs WHERE recipient_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();

            // Löschen der Einträge in der Tabelle recipient_group, die sich auf den Empfänger beziehen
            $stmt = $GLOBALS['mysqli']->prepare("DELETE FROM recipient_group WHERE recipient_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();

            // Löschen des Eintrags in der Tabelle recipients
            $stmt = $GLOBALS['mysqli']->prepare("DELETE FROM recipients WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();
            echo $delete_id;
        } else {
            die("Ungültige 'delete_id'");
        }
        break;

    case 'groups':
        $stmt = $GLOBALS['mysqli']->prepare("DELETE FROM groups WHERE id = ? LIMIT 1");
        if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();
            //echo "ok";
            echo $delete_id;
        } else {
            die("Ungültige 'delete_id'");
        }
        break;
}
