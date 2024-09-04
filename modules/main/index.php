<?php
$title = "SSI Center";    // Der Titel Ihres Moduls
$moduleName = "main";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require(__DIR__ . "/../../DashboardClass.php");

$dashboard->render();