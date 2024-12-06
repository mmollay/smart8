<?php
$versions = require(__DIR__ . "/version.php");
$title = "SSI Newsletter";
$moduleName = "newsletter";
$version = $versions['version'];

require(__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);
$dashboard->addMenuItem('leftMenu', "newsletter", "home", "Home", "home icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_newsletters", "Newsletter", "newspaper icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_recipients", "Empfänger", "address card icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "import_recipients", "Empfänger importieren", "upload icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_groups", "Gruppen", "users icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_senders", "Absender", "at icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_templates", "Vorlagen", "file alternate icon");


if (isset($_SESSION['superuser']) && $_SESSION['superuser'] == 1) {
    $dashboard->addMenuItem('leftMenu', "newsletter", "list_packages", "User Pakete", "box icon");
    //Button zum Absenden anstossen
}

if (isset($_SESSION['superuser']) && $_SESSION['superuser'] == 1) {
    $button_exec = '<button onclick="startCron()" class="ui tiny primary button"><i class="play icon"></i>Versand starten</button>';
    $dashboard->addMenuItem('leftMenu', "", "", $button_exec, "");

    $dashboard->addScript("
        function startCron() {
            $.ajax({
                url: 'ajax/start_cron.php',
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    $('body').toast({
                        message: response.message || 'Newsletter-Versand wurde gestartet',
                        class: response.success ? 'success' : 'error'
                    });
                }
            });
        }", true);
}


// Hole Paketinformationen
$packageInfo = getUserPackageInfo($userId);

if ($packageInfo) {
    // Farbklasse basierend auf Nutzung bestimmen
    $colorClass = 'green';
    if ($packageInfo['usage_percent'] > 70) {
        $colorClass = 'red';
    } elseif ($packageInfo['usage_percent'] > 40) {
        $colorClass = 'yellow';
    }

    $package = '
    <br>
    <div class="ui message">
        <div class="ui header">
            ' . ucfirst($packageInfo['package_type']) . '-Paket:<br>
        </div>
        ' . $packageInfo['emails_remaining_formatted'] . ' von ' . $packageInfo['emails_limit_formatted'] . '<br>E-Mails verfügbar
        <div class="ui tiny ' . $colorClass . ' progress" data-percent="' . $packageInfo['usage_percent'] . '">
            <div class="bar"></div>
        </div>
    </div>
    <script>$(document).ready(function () {$(".ui.progress").progress(); });</script>';
}

$dashboard->addMenuItem('leftMenu', "", "", "$package", "");

//$dashboard->addScript('js/send_emails.js');
$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/ckeditor.js");
$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/translations/de.js");
$dashboard->addScript("js/form_after.js");
$dashboard->addScript("js/cron-control.js");

$dashboard->render();

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
?>