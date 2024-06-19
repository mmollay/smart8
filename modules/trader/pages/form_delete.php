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

require_once ('../t_config.php');

switch ($_POST['list_id']) {
    case 'orders':
        //UPdate the order status
        $stmt = $GLOBALS['mysqli']->prepare("UPDATE ssi_trader.orders SET trash = 1 WHERE ticket = ? LIMIT 1");
        if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
            $delete_id = intval($_POST['delete_id']);
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();
            echo $delete_id;
        } else {
            die("Ungültige 'delete_id'");
        }
        break;

    case 'client':
        $stmt = $GLOBALS['mysqli']->prepare("DELETE FROM ssi_trader.clients WHERE client_id = ? LIMIT 1");
        if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
            $delete_id = intval($_POST['delete_id']);
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();
        } else {
            die("Ungültige 'delete_id'");
        }
        break;

    case 'strategy':
        // Bereite die SQL-Anweisungen vor
        $stmt1 = $GLOBALS['mysqli']->prepare("DELETE FROM ssi_trader.hedging_group WHERE group_id = ? LIMIT 1");
        $stmt2 = $GLOBALS['mysqli']->prepare("DELETE FROM ssi_trader.hedging WHERE group_id = ?");

        // Überprüfe, ob die DELETE-ID vorhanden und gültig ist
        if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
            $delete_id = intval($_POST['delete_id']);

            // Führe die Anweisungen aus
            $stmt1->bind_param("i", $delete_id);
            $stmt1->execute();

            $stmt2->bind_param("i", $delete_id);
            $stmt2->execute();

            // Schließe die Anweisungen
            $stmt1->close();
            $stmt2->close();
        } else {
            // Fehlerbehandlung, wenn delete_id ungültig oder nicht vorhanden ist
            die("Ungültige 'delete_id'");
        }
        break;

    case 'broker':
        // Bereite die SQL-Anweisung vor
        $stmt = $GLOBALS['mysqli']->prepare("DELETE FROM ssi_trader.broker WHERE broker_id = ? LIMIT 1");

        // Überprüfe, ob die DELETE-ID vorhanden und gültig ist
        if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
            $delete_id = intval($_POST['delete_id']);

            // Führe die Anweisung aus
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            // Schließe die Anweisung
            $stmt->close();
        } else {
            // Fehlerbehandlung, wenn delete_id ungültig oder nicht vorhanden ist
            die("Ungültige 'delete_id'");
        }
        break;

    case 'server':
        // Bereite die SQL-Anweisung vor
        $stmt = $GLOBALS['mysqli']->prepare("DELETE FROM ssi_trader.servers WHERE server_id = ? LIMIT 1");

        // Überprüfe, ob die DELETE-ID vorhanden und gültig ist
        if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
            $delete_id = intval($_POST['delete_id']);

            // Führe die Anweisung aus
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            // Schließe die Anweisung
            $stmt->close();
            echo "Server erfolgreich gelöscht.";
        } else {
            // Fehlerbehandlung, wenn delete_id ungültig oder nicht vorhanden ist
            die("Ungültige 'delete_id'");
        }
        break;

    case 'investment':
        // Bereite die SQL-Anweisung vor
        $stmt = $GLOBALS['mysqli']->prepare("DELETE FROM ssi_trader.deposits WHERE deposit_id = ? LIMIT 1");

        // Überprüfe, ob die DELETE-ID vorhanden und gültig ist
        if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
            $delete_id = intval($_POST['delete_id']);

            // Führe die Anweisung aus
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            // Schließe die Anweisung
            $stmt->close();
            echo "Investment erfolgreich gelöscht.";
        } else {
            // Fehlerbehandlung, wenn delete_id ungültig oder nicht vorhanden ist
            die("Ungültige 'delete_id'");
        }
        break;

}
