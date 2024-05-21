<?php

$title = "SSI Trader";    // Der Titel Ihres Moduls
$moduleName = "trader";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require (__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);
$dashboard->enableJSForMenu('leftMenu');
$dashboard->addMenuItem('leftMenu', "service", "home", "Home", "home icon", "Home");
//$dashboard->addMenuItem('leftMenu', "service", "mysql", "Mysql", "database icon", "");
$dashboard->addMenuItem('leftMenu', "service", "hacker", "Hacker-Files", "building icon");
$dashboard->addMenuItem('leftMenu', "service", "apache", "Apache", "lightning icon", 'Apache');
$dashboard->render();