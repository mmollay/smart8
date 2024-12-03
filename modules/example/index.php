<?php
$title = "Example Module";
$moduleName = "example";
$version = "1.0.0";

require(__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);
$dashboard->addMenuItem('leftMenu', "example", "home", "Home", "home icon");
$dashboard->addMenuItem('leftMenu', "example", "list_items", "Items", "list icon");

$dashboard->render();