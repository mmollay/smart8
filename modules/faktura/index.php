<?php
$title = "SSI Faktura";    // Der Titel Ihres Moduls
$moduleName = "faktura";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require (__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);
$dashboard->addMenuItem('leftMenu', "faktura", "home", "Home", "home icon");
$dashboard->addMenuItem('leftMenu', "faktura", "list_clients", "Kunden", "users icon");
$dashboard->addMenuItem('leftMenu', "faktura", "list_earnings", "Rechnungen", "file green text icon");
$dashboard->addMenuItem('leftMenu', "faktura", "list_issues", "Ausgaben", "file red text icon");
$dashboard->addMenuItem('leftMenu', "faktura", "list_article", "Artikel", "cubes icon");
$dashboard->addMenuItem('leftMenu', "faktura", "list_elba", "Elba", "money yellow check icon");

// $dashboard->addMenu('rightMenu', 'ui labeled icon right fixed menu mini vertical', true);
// $dashboard->addMenuItem('rightMenu', "", "", "Martin", "");
// $dashboard->addMenuItem('rightMenu', "faktura", "home", "Home", "home icon");
// $dashboard->addMenuItem('rightMenu', "faktura", "list_clients1", "Kunden", "users icon");
// $dashboard->enableJSForMenu('rightMenu');

$dashboard->addScript("js/automator.js");

$dashboard->render();