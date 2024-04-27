<?php
require (__DIR__ . "/../../DashboardClass.php");

$title = "SSI Center";    // Der Titel Ihres Moduls
$moduleName = "main";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

$dashboard = new Dashboard($title, $db, $userId, $version, $moduleName);

$dashboard->addMenuItem("Home", "home icon", "home", "", true);
$dashboard->addMenuItem("Einstellungen", "settings icon", "settings");
//$dashboard->addScript("../../js/main.js");
$dashboard->render();