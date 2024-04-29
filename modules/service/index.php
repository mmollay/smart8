<?php

$title = "SSI Service";    // Der Titel Ihres Moduls
$moduleName = "service";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require (__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui left large vertical fixed sidebar menu');
$dashboard->addMenuItem('leftMenu', "service", "Home", "home icon", "home", true);
$dashboard->addMenuItem('leftMenu', "service", "Mysql", "database icon", "mysql");
$dashboard->addMenuItem('leftMenu', "service", "Hacker-Files", "building icon", "hacker");
$dashboard->addMenuItem('leftMenu', "service", "Apache", "lightning icon", "apache");
$dashboard->addMenuItem('leftMenu', "service", "Logout", "sign out icon", "logout");

$dashboard->render();