<?php
$title = "SSI Trader"; // Der Titel Ihres Moduls
$moduleName = "trader2"; // Der Name Ihres Moduls
$version = "1.0.0"; // Die Version Ihres Moduls

require(__DIR__ . "/../../DashboardClass.php");

// Menü erstellen
$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);

// Home
$dashboard->addMenuItem('leftMenu', "trader2", "home", "Home", "home icon", "home");

// Trading hinzufügen
$dashboard->addMenuItem('leftMenu', "trader2", "trading", "Trading", "bitcoin icon", "trading");

// Trades
$dashboard->addMenuItem('leftMenu', "trader2", "list_trades", "Trades", "money check icon");

$dashboard->addMenuItem('leftMenu', "trader2", "list_pnl", "PnL History", "chart bar icon");

$dashboard->addMenuItem('leftMenu', "trader2", "analysis_dashboard", "Markt-Analyse", "chart bar icon");

// Clients
$dashboard->addMenuItem('leftMenu', "trader2", "list_users", "Clients", "users icon", "client");

// Trading Parameter Modelle
$dashboard->addMenuItem('leftMenu', "trader2", "list_parameter_models", "Parameter Modelle", "sliders icon", "parameter_models");

// Version
$dashboard->addMenuItem('leftMenu', "trader2", "version", "Version", "code branch icon", "version");

// Monitor
$dashboard->addMenuItem('leftMenu', "trader2", "monitor", "Monitor", "eye icon");

// Sync Button
//$dashboard->addMenuItem('leftMenu', 'trader2', "#", "Sync", "sync icon blue", "Daten synchronisieren", "", "", false, "syncData()");

// Scripts
$dashboard->addScript("js/sync-functions.js");

$dashboard->addScript("js/analysis-dashboard.js", false);
// Chart.js und benötigte Plugins einbinden
$dashboard->addScript("https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js", false);
$dashboard->addScript("https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js", false);
$dashboard->addScript("https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.1/dist/chartjs-adapter-moment.min.js", false);
// Render the menu
$dashboard->render();