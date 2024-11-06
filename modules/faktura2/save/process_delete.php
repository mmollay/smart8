<?php
include(__DIR__ . '/../f_config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Ungültige Anfragemethode.']);
    exit;
}

$delete_id = isset($_POST['delete_id']) ? intval($_POST['delete_id']) : 0;
$entity_type = isset($_POST['entity_type']) ? $_POST['entity_type'] : '';

if ($delete_id === 0 || empty($entity_type)) {
    echo json_encode(['status' => 'error', 'message' => 'Ungültige Parameter.']);
    exit;
}

$db->begin_transaction();

try {
    switch ($entity_type) {
        case 'customer':
            // Überprüfen, ob der Kunde Rechnungen hat
            $stmt = $db->prepare("SELECT COUNT(*) FROM invoices WHERE customer_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                throw new Exception("Kunde kann nicht gelöscht werden, da noch Rechnungen vorhanden sind.");
            }

            $stmt = $db->prepare("DELETE FROM customers WHERE customer_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            break;

        case 'supplier':
            // Überprüfen, ob der Lieferant Ausgaben hat
            $stmt = $db->prepare("SELECT COUNT(*) FROM expenses WHERE supplier_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                throw new Exception("Lieferant kann nicht gelöscht werden, da noch Ausgaben vorhanden sind.");
            }

            $stmt = $db->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            break;

        case 'article':
            // Überprüfen, ob der Artikel in Rechnungen verwendet wird
            $stmt = $db->prepare("SELECT COUNT(*) FROM invoice_items WHERE article_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                throw new Exception("Artikel kann nicht gelöscht werden, da er in Rechnungen verwendet wird.");
            }

            $stmt = $db->prepare("DELETE FROM articles WHERE article_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            break;

        case 'invoice':
            $db->query("START TRANSACTION");

            $stmt = $db->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $db->prepare("DELETE FROM invoices WHERE invoice_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            break;

        case 'account':
            // Überprüfen, ob das Konto in Transaktionen verwendet wird
            $stmt = $db->prepare("SELECT COUNT(*) FROM transactions WHERE account_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                throw new Exception("Konto kann nicht gelöscht werden, da es in Transaktionen verwendet wird.");
            }

            $stmt = $db->prepare("DELETE FROM accounts WHERE account_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            break;

        case 'expense':
            $db->query("START TRANSACTION");

            $stmt = $db->prepare("DELETE FROM expense_items WHERE expense_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $db->prepare("DELETE FROM expenses WHERE expense_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            break;

        default:
            throw new Exception("Ungültiger Entitätstyp.");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Kein Datensatz wurde gelöscht. Möglicherweise existiert der Datensatz nicht mehr.");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Datensatz erfolgreich gelöscht.']);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}