<?php
require_once('../n_config.php');
header('Content-Type: application/json');

// Nutze die bestehende Funktion
$packageInfo = getUserPackageInfo($userId);

// Bereite HTML für die Antwort vor
$html = '';
if ($packageInfo) {
    $colorClass = 'green';
    if ($packageInfo['usage_percent'] > 70) {
        $colorClass = 'red';
    } elseif ($packageInfo['usage_percent'] > 40) {
        $colorClass = 'yellow';
    }

    $html = '

    <div class="ui header">
   ' . ucfirst($packageInfo['package_type']) . '<br>
    </div>
   ' . $packageInfo['emails_remaining_formatted'] . ' von ' . $packageInfo['emails_limit_formatted'] . '<br><br>E-Mails verfügbar
    <br><br><div class="ui tiny ' . $colorClass . ' progress" data-percent="' . $packageInfo['usage_percent'] . '">
    <div class="bar"></div>
    </div>
   ';
}

echo json_encode([
    'success' => true,
    'html' => $html
]);


function getUserPackageInfo($userId)
{
    global $db;
    $sql = "
    SELECT 
        nup.package_type,
        nup.emails_limit,
        nup.emails_sent,
        (nup.emails_limit - nup.emails_sent) as emails_remaining,
        DATE_FORMAT(nup.valid_until, '%d.%m.%Y') as valid_until_formatted,
        (SELECT COUNT(*) 
         FROM ssi_newsletter.email_jobs ej 
         JOIN ssi_newsletter.email_contents ec ON ej.content_id = ec.id 
         WHERE ec.user_id = ? 
         AND ej.status IN ('send', 'delivered', 'open', 'click')
        ) as total_emails_sent
    FROM ssi_company2.newsletter_user_packages nup
    WHERE nup.user_id = ?
    AND nup.valid_until IS NULL
    LIMIT 1";

    try {

        $stmt = $db->prepare($sql);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $packageInfo = $result->fetch_assoc();

        if ($packageInfo) {
            // Formatiere die Zahlen für die Anzeige
            $packageInfo['emails_limit_formatted'] = number_format($packageInfo['emails_limit'], 0, ',', '.'); // Gesamtlimit
            $packageInfo['emails_sent_formatted'] = number_format($packageInfo['emails_sent'], 0, ',', '.'); // Im aktuellen Paket gesendet
            $packageInfo['total_emails_sent_formatted'] = number_format($packageInfo['total_emails_sent'], 0, ',', '.'); // Tatsächlich gesendet

            // Berechne verbleibende E-Mails (Limit minus tatsächlich gesendet)
            $packageInfo['emails_remaining'] = max(0, $packageInfo['emails_limit'] - $packageInfo['total_emails_sent']);
            $packageInfo['emails_remaining_formatted'] = number_format($packageInfo['emails_remaining'], 0, ',', '.');

            // Berechne Prozentsatz basierend auf tatsächlich versendeten E-Mails
            $packageInfo['usage_percent'] = round(($packageInfo['total_emails_sent'] / $packageInfo['emails_limit']) * 100, 1);

        }

        return $packageInfo;
    } catch (Exception $e) {
        error_log("Fehler beim Laden der Paketinformationen: " . $e->getMessage());
        return null;
    }
}