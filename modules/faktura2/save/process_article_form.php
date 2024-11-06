<?php
include(__DIR__ . '/../f_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_number = trim($_POST['article_number']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $unit = trim($_POST['unit']);
    $price = !empty($_POST['price']) ? floatval($_POST['price']) : null;
    $account_id = !empty($_POST['account_id']) ? intval($_POST['account_id']) : null;
    $article_id = !empty($_POST['article_id']) ? intval($_POST['article_id']) : null;

    $modus = $article_id ? 'update_article' : 'add_article';

    if (empty($article_number) || empty($name) || empty($unit) || $price === null || $account_id === null) {
        send_response('error', 'Bitte füllen Sie alle Pflichtfelder aus.');
        exit;
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM articles WHERE article_number = ? AND article_id != ?");
    $stmt->bind_param("si", $article_number, $article_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        send_response('error', 'Diese Artikelnummer existiert bereits.');
        exit;
    }

    if ($modus === 'add_article') {
        $sql = "INSERT INTO articles (article_number, name, description, unit, price, account_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssssdi", $article_number, $name, $description, $unit, $price, $account_id);
    } else {
        $sql = "UPDATE articles SET article_number = ?, name = ?, description = ?, unit = ?, price = ?, account_id = ? WHERE article_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssssdii", $article_number, $name, $description, $unit, $price, $account_id, $article_id);
    }

    if ($stmt->execute()) {
        send_response('success', 'Artikel erfolgreich ' . ($modus === 'add_article' ? 'hinzugefügt' : 'aktualisiert') . '.');
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