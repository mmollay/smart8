<?php
include(__DIR__ . '/../../n_config.php');

header('Content-Type: application/json');

$group_ids = $_POST['group_ids'] ?? [];
$content = $_POST['content'] ?? '';
$subject = $_POST['subject'] ?? '';

if (empty($group_ids) || empty($content)) {
    echo json_encode([
        'success' => false,
        'message' => 'Fehlende Parameter'
    ]);
    exit;
}

try {
    // Hole Empfänger aus den ausgewählten Gruppen
    $group_ids_str = implode(',', array_map('intval', $group_ids));
    $sql = "
        SELECT DISTINCT r.* 
        FROM recipients r
        JOIN recipient_group rg ON r.id = rg.recipient_id
        WHERE rg.group_id IN ($group_ids_str)
        AND r.unsubscribed = 0
        LIMIT 5"; // Begrenzen auf 5 Vorschau-Empfänger

    $result = $db->query($sql);
    $recipients = [];
    $templates = [];

    while ($recipient = $result->fetch_assoc()) {
        $recipients[] = [
            'id' => $recipient['id'],
            'first_name' => $recipient['first_name'],
            'last_name' => $recipient['last_name'],
            'email' => $recipient['email']
        ];

        // Erstelle personalisierte Vorschau
        $personalizedContent = replacePlaceholders($content, $recipient);
        $personalizedSubject = replacePlaceholders($subject, $recipient);

        $templates[$recipient['id']] = [
            'subject' => $personalizedSubject,
            'content' => $personalizedContent
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'recipients' => $recipients,
            'templates' => $templates
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fehler bei der Vorschau-Generierung: ' . $e->getMessage()
    ]);
}

$db->close();

function replacePlaceholders($text, $recipient)
{
    $placeholders = [
        '{{anrede}}' => getAnrede($recipient),
        '{{titel}}' => $recipient['title'] ?? '',
        '{{vorname}}' => $recipient['first_name'] ?? '',
        '{{nachname}}' => $recipient['last_name'] ?? '',
        '{{firma}}' => $recipient['company'] ?? '',
        '{{email}}' => $recipient['email'] ?? '',
        '{{datum}}' => date('d.m.Y'),
        '{{datum_lang}}' => strftime('%e. %B %Y'),
        '{{uhrzeit}}' => date('H:i'),
    ];

    return str_replace(array_keys($placeholders), array_values($placeholders), $text);
}

function getAnrede($recipient)
{
    $gender = $recipient['gender'] ?? '';
    $title = $recipient['title'] ?? '';
    $lastName = $recipient['last_name'] ?? '';

    if ($gender === 'male') {
        return 'Sehr geehrter' . ($title ? ' Herr ' . $title : ' Herr') . ' ' . $lastName;
    } elseif ($gender === 'female') {
        return 'Sehr geehrte' . ($title ? ' Frau ' . $title : ' Frau') . ' ' . $lastName;
    } else {
        return 'Sehr geehrte Damen und Herren';
    }
}

