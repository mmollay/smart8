<?php

$title = "SSI Newsletter";    // Der Titel Ihres Moduls
$moduleName = "newsletter";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require (__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);
$dashboard->addMenuItem('leftMenu', "newsletter", "home", "Home", "home icon");

//ABSENDER ICON
$dashboard->addMenuItem('leftMenu', "newsletter", "list_senders", "Absender", "at icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_recipients", "Empfänger", "address card icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_groups", "Gruppen", "users icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_newsletters", "Newsletter", "newspaper icon");
//$dashboard->addMenuItem('leftMenu', "newsletter", "list_templates", "Vorlagen", "file alternate icon");
//$dashboard->addMenuItem('leftMenu', "newsletter", "list_logs", "Logs", "list icon");
//Manueller Sendebutton für den Newsletter  
$dashboard->addMenuItem('leftMenu', "", "", '<button class="ui blue labeled icon button" id="testSendEmail"><i class="paper small plane icon"></i>Absenden</button>', "");

$dashboard->addScript("js/form_after.js");
$dashboard->addScript('js/send_emails.js');
//$dashboard->addScript("../../smartform2/js/listGenerator.js");
//$dashboard->addScript("../../../smartform/ckeditor5/ckeditor.js");
//$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/35.0.1/classic/ckeditor.js");
$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/ckeditor.js");
$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/translations/de.js");

$dashboard->render();