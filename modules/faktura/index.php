<?php
require (__DIR__ . "/../../DashboardClass.php");

$title = "SSI Faktura";    // Der Titel Ihres Moduls
$moduleName = "faktura";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

$dashboard = new Dashboard($title, $db, $userId, $version, $moduleName);

$dashboard->addMenuItem("Home", "home icon", "home", "", true);
$dashboard->addMenuItem("Kunden", "users icon", "list_clients");
$dashboard->addMenuItem("Rechnungen", "file text icon", "list_earnings");
$dashboard->addMenuItem("Ausgaben", "file text icon", "list_issues");
$dashboard->addMenuItem("Artikel", "cubes icon", "list_article");

$dashboard->addTopMenuItem("Einstellungen", "file text icon", "list_clients", "right");

$dashboard->addJSVar("smart_form_wp", "../../smartform/");
$dashboard->addScript("../../smartform/js/smart_list.js");
$dashboard->addScript("../../smartform/js/smart_form.js");
$dashboard->setSidebarClass('ui left vertical pointing sidebar menu'); //Sidebar klappt ein
$dashboard->setSidebarClass('ui left vertical pointing menu'); //Menü immer sichtbar 
$dashboard->setSidebarVisibleOnInit(true);
$dashboard->setMenuClass('ui pointing fixed menu'); // Beispiel für eine andere Menüklasse

// Konfiguriere die Sidebar mit einem Array von Einstellungen
$dashboard->configureSidebar([
    'transition' => 'overlay',
    'dimPage' => false,
    'direction' => 'top',
    'closable' => true,
    'duration' => 500,
    'easing' => 'easeInOutQuad'
]);



$dashboard->render();