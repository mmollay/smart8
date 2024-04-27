<?php
require (__DIR__ . "/../../DashboardClass.php");

$title = "SSI Service";    // Der Titel Ihres Moduls
$moduleName = "service";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

$dashboard = new Dashboard($title, $db, $userId, $version, $moduleName);

$dashboard->addMenuItem("Haupt-Dashboard", "tachometer alternate icon", "../main/index.php");
$dashboard->addMenuItem("Home", "home icon", "home", true);
$dashboard->addMenuItem("Mysql", "database icon", "mysql");
$dashboard->addMenuItem("Hacker-Files", "building icon", "hacker");
$dashboard->addMenuItem("Apache", "lightning icon", "apache");
$dashboard->addMenuItem("Logout", "sign out icon", "logout");
//$dashboard->addScript("../../js/main.js");
//$dashboard->addStyle("../../css/basis.css");
$dashboard->render();