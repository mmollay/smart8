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
    $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;

    $modus = $customer_id ? 'update_customer' : 'add_customer';

    if (empty($company_name) || empty($email)) {
        send_response('error', 'Bitte füllen Sie alle Pflichtfelder aus.');
        exit;
    }

    // E-Mail-Überprüfung
    if ($modus === 'add_customer') {
        // Für neue Kunden: Überprüfen, ob die E-Mail bereits existiert
        $stmt = $db->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
    } else {
        // Für Updates: Überprüfen, ob die E-Mail einem anderen Kunden gehört
        $stmt = $db->prepare("SELECT COUNT(*) FROM customers WHERE email = ? AND customer_id != ?");
        $stmt->bind_param("si", $email, $customer_id);
    }

    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        send_response('error', 'Diese E-Mail-Adresse wird bereits verwendet.');
        exit;
    }

    if ($modus === 'add_customer') {
        $sql = "INSERT INTO customers (company_name, contact_person, street, postal_code, city, country, phone, email, tax_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("sssssssss", $company_name, $contact_person, $street, $postal_code, $city, $country, $phone, $email, $tax_number);
    } else {
        $sql = "UPDATE customers SET company_name = ?, contact_person = ?, street = ?, postal_code = ?, city = ?, country = ?, phone = ?, email = ?, tax_number = ? WHERE customer_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("sssssssssi", $company_name, $contact_person, $street, $postal_code, $city, $country, $phone, $email, $tax_number, $customer_id);
    }

    if ($stmt->execute()) {
        send_response('success', 'Kunde erfolgreich ' . ($modus === 'add_customer' ? 'hinzugefügt' : 'aktualisiert') . '.');
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