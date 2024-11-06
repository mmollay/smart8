<?php
include(__DIR__ . '/../f_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = trim($_POST['company_name']);
    $contact_person = trim($_POST['contact_person']);
    $street = trim($_POST['street']);
    $postal_code = trim($_POST['postal_code']);
    $city = trim($_POST['city']);
    $country = trim($_POST['country']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $tax_number = trim($_POST['tax_number']);
    $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;

    $modus = $supplier_id ? 'update_supplier' : 'add_supplier';

    if (empty($company_name) || empty($email)) {
        send_response('error', 'Bitte füllen Sie alle Pflichtfelder aus.');
        exit;
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM suppliers WHERE email = ? AND supplier_id != ?");
    $stmt->bind_param("si", $email, $supplier_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        send_response('error', 'Diese E-Mail-Adresse existiert bereits.');
        exit;
    }

    if ($modus === 'add_supplier') {
        $sql = "INSERT INTO suppliers (company_name, contact_person, street, postal_code, city, country, phone, email, tax_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("sssssssss", $company_name, $contact_person, $street, $postal_code, $city, $country, $phone, $email, $tax_number);
    } else {
        $sql = "UPDATE suppliers SET company_name = ?, contact_person = ?, street = ?, postal_code = ?, city = ?, country = ?, phone = ?, email = ?, tax_number = ? WHERE supplier_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("sssssssssi", $company_name, $contact_person, $street, $postal_code, $city, $country, $phone, $email, $tax_number, $supplier_id);
    }

    if ($stmt->execute()) {
        send_response('success', 'Lieferant erfolgreich ' . ($modus === 'add_supplier' ? 'hinzugefügt' : 'aktualisiert') . '.');
    } else {
        send_response('error', 'Datenbankfehler: ' . $stmt->error);
    }

    $stmt->close();
} else {
    send_response('error', 'Ungültige Anfragemethode.');
}

function send_response($status, $message)
{
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message]);
}