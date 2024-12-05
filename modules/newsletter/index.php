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

//$dashboard->addScript('js/send_emails.js');
$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/ckeditor.js");
$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/translations/de.js");
$dashboard->addScript("js/form_after.js");

//require(__DIR__ . "/changelog_modal.php");
// $dashboard->addFooterContent('
//     <button class="ui basic tiny compact button" onclick="$(\'#changelog-modal\').modal(\'show\')" style="margin: 0; opacity: 0.7;">
//         <i class="history icon"></i>
//         v' . $versions['version'] . ' - Changelog
//     </button>
// ');
$dashboard->render();