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




function makeUrlsAbsolute($content, $baseUrl)
{
    $baseUrl = rtrim($baseUrl, '/');

    $patterns = [
        ['pattern' => '/(src\s*=\s*)"(\/users\/[^"]+)"/i', 'attr' => 'src'],
        ['pattern' => '/(href\s*=\s*)"(\/users\/[^"]+)"/i', 'attr' => 'href'],
    ];

    foreach ($patterns as $p) {
        $content = preg_replace_callback(
            $p['pattern'],
            function ($matches) use ($baseUrl) {
                $oldUrl = $matches[2];
                $newUrl = $baseUrl . $oldUrl;
                return $matches[1] . '"' . $newUrl . '"';
            },
            $content
        );
    }

    return $content;
}



function prepareHtmlForEmail($content)
{
    // Bereinige Style-Attribute
    $content = str_replace('=3D', '=', $content);
    $content = preg_replace('/style="([^"]*?);+([^"]*?)"/i', 'style="$1;$2"', $content);

    // Behandle figure mit image-style-side (rechtsbündig)
    $content = preg_replace(
        '/<figure(.*?)class="(.*?)image-style-side(.*?)"(.*?)style="width:200px;?(.*?)"/i',
        '<div$1class="$2image-style-align-right$3"$4style="float: right; margin-left: 20px; width: 200px;$5"',
        $content
    );

    // Ersetze übrige figure-Tags
    $content = preg_replace('/<figure(.*?)>/i', '<div$1>', $content);
    $content = str_replace('</figure>', '</div>', $content);

    // Behandle linksbündige Bilder
    $content = preg_replace(
        '/class="([^"]*?)image-style-align-left([^"]*?)"\s*style="width:200px;?(.*?)"/i',
        'class="$1image-style-align-left$2" style="width: 200px; float: left; margin-right: 20px;$3"',
        $content
    );

    // Behandle zentrierte Bilder
    $content = preg_replace(
        '/class="([^"]*?)image-style-align-center([^"]*?)"\s*style="width:200px;?(.*?)"/i',
        'class="$1image-style-align-center$2" style="width: 200px; display: block; margin: 0 auto; text-align: center;$3"',
        $content
    );

    // Bereinige das HTML
    $content = preg_replace('/\s+/', ' ', $content);
    $content = preg_replace('/;\s*;/', ';', $content);
    $content = preg_replace('/";\s*"/', '"', $content);

    return $content;
}
