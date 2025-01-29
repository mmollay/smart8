<?php

$title = "SSI Trader";    // Der Titel Ihres Moduls
$moduleName = "trader";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require(__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);


// Home
$dashboard->addMenuItem('leftMenu', "trader", "home", "Home", "home icon", "home");

//Prozesse
$dashboard->addMenuItem('leftMenu', "trader", "list_
processes", "Processes", "cogs icon", "processes");
// Orders
$dashboard->addMenuItem('leftMenu', "trader", "list_trades", "Trades", "money check icon");
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

$dashboard->addScript("https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.js");
// Dann erst das Datalabels Plugin
$dashboard->addScript("https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2");

$dashboard->addScript("https://cdnjs.cloudflare.com/ajax/libs/react/18.2.0/umd/react.production.min.js");
$dashboard->addScript("https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.2.0/umd/react-dom.production.min.js");
$dashboard->addScript("https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/7.23.5/babel.min.js");
$dashboard->addScript("js/MT5Status.js", false, 'module');

//$dashboard->addModule("/smart8/node_modules/chart.js/auto/auto.js");
//$dashboard->addModule("/smart8/node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.js");


// Render the menu
$dashboard->render();

