<?php
// ajax/template/get_recipient_data.php
include(__DIR__ . '/../../n_config.php');

header('Content-Type: application/json');

$recipient_id = $_POST['recipient_id'] ?? null;

if (!$recipient_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Empfänger ID fehlt'
    ]);
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT 
            first_name, 
            last_name, 
            title,
            company,
            email,
            gender
        FROM recipients 
        WHERE id = ?
    ");

    $stmt->bind_param('i', $recipient_id);
    $stmt->execute();
    $recipient = $stmt->get_result()->fetch_assoc();

    if (!$recipient) {
        throw new Exception('Empfänger nicht gefunden');
    }

    // Anrede basierend auf Geschlecht
    $anrede = 'Sehr geehrte Damen und Herren';
    if ($recipient['gender'] === 'male') {
        $anrede = 'Sehr geehrter' . ($recipient['title'] ? ' Herr ' . $recipient['title'] : ' Herr');
    } elseif ($recipient['gender'] === 'female') {
        $anrede = 'Sehr geehrte' . ($recipient['title'] ? ' Frau ' . $recipient['title'] : ' Frau');
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'anrede' => $anrede,
            'titel' => $recipient['title'],
            'vorname' => $recipient['first_name'],
            'nachname' => $recipient['last_name'],
            'firma' => $recipient['company'],
            'email' => $recipient['email'],
            'datum' => date('d.m.Y'),
            'uhrzeit' => date('H:i')
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$db->close();