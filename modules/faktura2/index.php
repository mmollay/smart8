<?php
$title = "SSI FakturaV2";    // Der Titel Ihres Moduls
$moduleName = "faktura2";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require(__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui  left fixed menu vertical', true);
$dashboard->addMenuItem('leftMenu', "faktura2", "home", "Home", "home icon");
$dashboard->addMenuItem('leftMenu', "faktura2", "list_customers", "Kunden", "users icon");
$dashboard->addMenuItem('leftMenu', "faktura2", "list_invoices", "Rechnungen", "file green text icon");
$dashboard->addMenuItem('leftMenu', "faktura2", "list_expenses", "Ausgaben", "file red text icon");
$dashboard->addMenuItem('leftMenu', "faktura2", "list_articles", "Artikel", "cubes icon");
$dashboard->addMenuItem('leftMenu', "faktura2", "list_elba", "Elba", "money yellow check icon");
//Konten
$dashboard->addMenuItem('leftMenu', "faktura2", "list_accounts", "Konten", "money bill alternate outline icon");
//lieferanten
$dashboard->addMenuItem('leftMenu', "faktura2", "list_suppliers", "Lieferanten", "truck icon");

// $dashboard->addMenu('rightMenu', 'ui labeled icon right fixed menu mini vertical', true);
// $dashboard->addMenuItem('rightMenu', "", "", "Martin", "");
// $dashboard->addMenuItem('rightMenu', "faktura", "home", "Home", "home icon");
// $dashboard->addMenuItem('rightMenu', "faktura", "list_clients1", "Kunden", "users icon");
// $dashboard->enableJSForMenu('rightMenu');
$dashboard->addScript("js/form.js");

$dashboard->render();