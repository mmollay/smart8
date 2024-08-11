<?php

$title = "SSI Service";    // Der Titel Ihres Moduls
$moduleName = "service";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require (__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);
$dashboard->addMenuItem('leftMenu', "service", "home", "Home", "home icon", "Home");
$dashboard->addMenuItem('leftMenu', "service", "hacker", "Hacker-Files", "building icon");
$dashboard->addMenuItem('leftMenu', "service", "apache", "Apache", "lightning icon", 'Apache');
$dashboard->render();