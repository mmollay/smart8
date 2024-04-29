<?php
$title = "SSI Faktura";    // Der Titel Ihres Moduls
$moduleName = "faktura";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require (__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui left large vertical fixed sidebar menu');

$dashboard->addMenuItem('leftMenu', "faktura", "Home", "home icon", "home");
$dashboard->addMenuItem('leftMenu', "faktura", "Kunden", "users icon", "list_clients");
$dashboard->addMenuItem('leftMenu', "faktura", "Rechnungen", "file text icon", "list_earnings");
$dashboard->addMenuItem('leftMenu', "faktura", "Ausgaben", "file text icon", "list_issues");
$dashboard->addMenuItem('leftMenu', "faktura", "Artikel", "cubes icon", "list_article");

$dashboard->setSidebarClass('ui left vertical pointing menu'); //Menü immer sichtbar 
$dashboard->setSidebarVisibleOnInit(true);

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