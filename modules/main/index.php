<?php
$title = "SSI Center";    // Der Titel Ihres Moduls
$moduleName = "main";  // Der Name Ihres Moduls
$version = "1.0.0";       // Die Version Ihres Moduls

require (__DIR__ . "/../../DashboardClass.php");

$menu = '
  Dropdown
  <i class="dropdown icon"></i>
  <div class="menu">
    <div class="item" data-page="mysql" data-module="service" >Mysql</div>
    <div class="item" data-page="hacker" data-module="service">Hacker-Files</div>
  </div>
';

$dashboard->addMenuItem('leftMenu', "service", "", $menu, "home icon", "Home", '', "ui dropdown item");
//Erklärung: addMenuItem($menu, $module, $id(path/), $title, $icon, $popup, $position, $class)
//$dashboard->addMenuItem('mainMenu', "faktura", "settings" "Einstellungen", "cog icon", "right" , "");
$dashboard->render();