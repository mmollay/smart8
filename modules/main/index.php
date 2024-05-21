<?php
$title = "SSI Center";    // Der Titel Ihres Moduls
$moduleName = "main";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require (__DIR__ . "/../../DashboardClass.php");

$menu = '
<div class="ui dropdown">
  <div class="text">File</div>
  <i class="dropdown icon"></i>
  <div class="menu">
    <div class="item" data-page="mysql" data-module="service" >Mysql</div>
    <div class="item" data-page="hacker" data-module="service">Hacker-Files</div>
  </div>
  </div>
</div>';


$user = $userDetails['firstname'] . " " . $userDetails['secondname'];

//$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);
//$dashboard->addMenuItem('leftMenu', "service", "test", $menu, "home icon", "Home", '', "ui dropdown item", "left");
//$dashboard->enableJSForMenu('leftMenu');

$dashboard->render();