<?php
$title = "SSI Trader";    // Der Titel Ihres Moduls
$moduleName = "faktura2";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require(__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui  left fixed menu vertical', true);
$dashboard->addMenuItem('leftMenu', "trader2", "home", "Home", "home icon");
$dashboard->addMenuItem('leftMenu', "trader2", "list_trades", "Trades", "money check icon");

// $dashboard->addMenu('rightMenu', 'ui labeled icon right fixed menu mini vertical', true);
// $dashboard->addMenuItem('rightMenu', "", "", "Martin", "");
// $dashboard->addMenuItem('rightMenu', "faktura", "home", "Home", "home icon");
// $dashboard->addMenuItem('rightMenu', "faktura", "list_clients1", "Kunden", "users icon");
// $dashboard->enableJSForMenu('rightMenu');
//$dashboard->addScript("js/form.js");

$dashboard->render();