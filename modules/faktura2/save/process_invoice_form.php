<?php
include(__DIR__ . '/../f_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->begin_transaction();

    try {
        $invoice_number = trim($_POST['invoice_number']);
        $customer_id = intval($_POST['customer_id']);
        $invoice_date = trim($_POST['invoice_date']);
        $due_date = trim($_POST['due_date']);
        $total_amount = floatval($_POST['total_amount']);
        $paid = isset($_POST['paid']) ? 1 : 0;
        $invoice_id = !empty($_POST['invoice_id']) ? intval($_POST['invoice_id']) : null;

        $modus = $invoice_id ? 'update_invoice' : 'add_invoice';

        if (empty($invoice_number) || empty($customer_id) || empty($invoice_date)) {
            throw new Exception('Bitte füllen Sie alle Pflichtfelder aus.');
        }

        $stmt = $db->prepare("SELECT COUNT(*) FROM invoices WHERE invoice_number = ? AND invoice_id != ?");
        $stmt->bind_param("si", $invoice_number, $invoice_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            throw new Exception('Diese Rechnungsnummer existiert bereits.');
        }

        if ($modus === 'add_invoice') {
            $sql = "INSERT INTO invoices (invoice_number, customer_id, invoice_date, due_date, total_amount, paid) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sissdl", $invoice_number, $customer_id, $invoice_date, $due_date, $total_amount, $paid);
        } else {
            $sql = "UPDATE invoices SET invoice_number = ?, customer_id = ?, invoice_date = ?, due_date = ?, total_amount = ?, paid = ? WHERE invoice_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sissdli", $invoice_number, $customer_id, $invoice_date, $due_date, $total_amount, $paid, $invoice_id);
        }

        $stmt->execute();

        if ($modus === 'add_invoice') {
            $invoice_id = $db->insert_id;
        }

        // Verarbeiten der Rechnungspositionen
        if ($modus === 'update_invoice') {
            $db->query("DELETE FROM invoice_items WHERE invoice_id = $invoice_id");
        }

        $items = json_decode($_POST['invoice_items'], true);
        $stmt = $db->prepare("INSERT INTO invoice_items (invoice_id, article_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");

        foreach ($items as $item) {
            $stmt->bind_param("iiddd", $invoice_id, $item['article_id'], $item['quantity'], $item['unit_price'], $item['total_price']);
            $stmt->execute();
        }

        $db->commit();
        send_response('success', 'Rechnung erfolgreich ' . ($modus === 'add_invoice' ? 'hinzugefügt' : 'aktualisiert') . '.');
    } catch (Exception $e) {
        $db->rollback();
        send_response('error', $e->getMessage());
    }
} else {
    send_response('error', 'Ungültige Anfragemethode.');
}

function send_response($status, $message)
{
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message]);
}