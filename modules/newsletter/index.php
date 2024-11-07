<?php

$title = "SSI Newsletter";    // Der Titel Ihres Moduls
$moduleName = "newsletter";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require(__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);
//$dashboard->addMenu('leftMenu', 'ui left fixed menu vertical', true);
$dashboard->addMenuItem('leftMenu', "newsletter", "home", "Home", "home icon");

//ABSENDER ICON
$dashboard->addMenuItem('leftMenu', "newsletter", "list_newsletters", "Aussendungen", "newspaper icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_recipients", "Empfänger", "address card icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "import_recipients", "Empfänger importieren", "upload icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_groups", "Gruppen", "users icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_senders", "Absender", "at icon");
//immportieren von Empfängern
//$dashboard->addMenuItem('leftMenu', "newsletter", "list_templates", "Vorlagen", "file alternate icon");
//$dashboard->addMenuItem('leftMenu', "newsletter", "list_logs", "Logs", "list icon");
//Manueller Sendebutton für den Newsletter  

$dashboard->addScript("js/newsletter-utils.js");  // Neue Utils-Datei
$dashboard->addScript("js/form_after.js");
$dashboard->addScript('js/send_emails.js');
//$dashboard->addScript("../../smartform2/js/listGenerator.js");
//$dashboard->addScript("../../../smartform/ckeditor5/ckeditor.js");
//$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/35.0.1/classic/ckeditor.js");
$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/ckeditor.js");
$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/translations/de.js");

$dashboard->render();