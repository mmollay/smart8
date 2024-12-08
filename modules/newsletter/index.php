<?php
$versions = require(__DIR__ . "/version.php");
$title = "SSI Newsletter";
$moduleName = "newsletter";
$version = $versions['version'];

require(__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);
$dashboard->addMenuItem('leftMenu', "newsletter", "home", "Home", "home icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_newsletters", "Newsletter", "newspaper icon");
//Newsletter erstellen
//$dashboard->addMenuItem('leftMenu', "newsletter", "create_newsletter", "Newsletter erstellen", "edit icon");
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

$package = '<br><div id="packageInfo"></div>';

$dashboard->addMenuItem('leftMenu', "", "", "$package", "");

//$dashboard->addScript('js/send_emails.js');
$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/ckeditor.js");
$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/translations/de.js");
$dashboard->addScript("js/form_after.js");
$dashboard->addScript("js/cron-control.js");
$dashboard->addScript("
    function updatePackageInfo() {
        $.ajax({
            url: 'ajax/get_package_info.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $('#packageInfo').html(response.html);
                    $('.ui.progress').progress();
                }
            }
        });
    }
    // Initiales Update
    updatePackageInfo();
    
    // Update alle 20 Sekunden
    setInterval(updatePackageInfo, 20000);
", true);

$dashboard->render();
