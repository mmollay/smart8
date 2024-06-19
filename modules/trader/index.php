<?php

$title = "SSI Trader";    // Der Titel Ihres Moduls
$moduleName = "trader";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require (__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);

// Home
$dashboard->addMenuItem('leftMenu', "trader", "home", "Home", "home icon", "home");

// Orders
$dashboard->addMenuItem('leftMenu', "trader", "orders", "Orders", "hand holding usd icon", "orders");

// Charts
$dashboard->addMenuItem('leftMenu', "trader", "chart", "Charts", "chart bar icon", "chart");

// Horizontal Rule
$dashboard->addMenuItem('leftMenu', "", "hr1", "", "", "");

// Servers
$dashboard->addMenuItem('leftMenu', "trader", "server", "Servers", "server icon", "server");

// Broker
$dashboard->addMenuItem('leftMenu', "trader", "broker", "Broker", "university icon", "broker");

// Strategy
$dashboard->addMenuItem('leftMenu', "trader", "strategy", "Strategy", "list icon", "strategy");

// Horizontal Rule
$dashboard->addMenuItem('leftMenu', "", "hr2", "", "", "");

// Clients
$dashboard->addMenuItem('leftMenu', "trader", "client", "Clients", "users icon", "client");

// Investments
$dashboard->addMenuItem('leftMenu', "trader", "investments", "Investments", "money bill alternate icon", "investments");

// Horizontal Rule
$dashboard->addMenuItem('leftMenu', "", "hr3", "", "", "");

//Fetchbutton

// Version
$dashboard->addMenuItem('leftMenu', "trader", "version", "Version", "code branch icon", "version");

$fetch_order_button = "<div align=center><button onclick='fetchOrders();' data-tooltip='Fetch orders' data-position='right center'  class='ui fluid compact mini icon blue button'><i class='sync  alternate icon'></i></button></div>";

$dashboard->addMenuItem('leftMenu', "trader", "", "$fetch_order_button", "sync alternate icon", "fetch_orders");



$dashboard->addScript("js/main.js");
$dashboard->addScript("loadScriptIfNotAlreadyLoaded('js/function_home.js');", true);
$dashboard->addScript("https://cdn.jsdelivr.net/npm/chart.js");
$dashboard->addScript("https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels");

// Render the menu
$dashboard->render();

