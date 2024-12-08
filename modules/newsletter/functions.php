<?php
// Hilfsfunktionen für das Newsletter-Modul
function getDefaultPlaceholders($customEmail = null)
{
    $now = new DateTime();
    return [
        'vorname' => 'Max',
        'nachname' => 'Mustermann',
        'titel' => 'Dr.',
        'geschlecht' => 'Herr',
        'anrede' => 'Sehr geehrter Herr Dr. Mustermann',
        'firma' => 'Demo GmbH',
        'company' => 'Demo GmbH',
        'email' => $customEmail ?? 'max.mustermann@beispiel.de',
        'datum' => $now->format('d.m.Y'),
        'datum_lang' => $now->format('l, d. F Y'),
        'uhrzeit' => $now->format('H:i'),
        'uhrzeit_lang' => $now->format('H:i:s'),
        'datum_kurz' => $now->format('d.m.y'),
        'monat' => $now->format('F'),
        'jahr' => $now->format('Y'),
        'wochentag' => $now->format('l')
    ];
}

function replacePlaceholders($text, $customPlaceholders = [])
{
    $placeholders = array_merge(
        getDefaultPlaceholders($customPlaceholders['email'] ?? null),
        $customPlaceholders
    );

    foreach ($placeholders as $key => $value) {
        $text = str_replace('{{' . $key . '}}', $value, $text);
    }

    return $text;
}

function getAllGroups($db)
{
    global $userId;
    $groups = [];

    $query = "
SELECT
g.id,
g.name,
g.color,
COUNT(DISTINCT CASE WHEN r.unsubscribed = 0 THEN rg.recipient_id END) as recipient_count
FROM
groups g
LEFT JOIN recipient_group rg ON g.id = rg.group_id
LEFT JOIN recipients r ON rg.recipient_id = r.id
WHERE
g.user_id = ?
GROUP BY
g.id,
g.name,
g.color
ORDER BY
g.name
";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        error_log("Prepare fehlgeschlagen: " . $db->error);
        return [];
    }

    $stmt->bind_param("s", $userId);

    if (!$stmt->execute()) {
        error_log("Execute fehlgeschlagen: " . $stmt->error);
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $groups[$row['id']] = sprintf(
            '<i class="circle %s icon"></i> %s (%d)',
            htmlspecialchars($row['color']),
            htmlspecialchars($row['name']),
            $row['recipient_count']
        );
    }

    $stmt->close();
    return $groups;
}

// Package-bezogene Funktionen
function getCurrentUserPackage($userId)
{
    global $db;
    $stmt = $db->prepare("
SELECT package_type, emails_sent, emails_limit
FROM newsletter_user_packages
WHERE user_id = ? AND valid_until IS NULL
");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function checkEmailLimit($userId)
{
    $package = getCurrentUserPackage($userId);
    if (!$package)
        return false;
    return $package['emails_sent'] < $package['emails_limit'];
}


