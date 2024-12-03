<?php
$title = "Benutzerverwaltung";
$moduleName = "users";
$version = "1.0.0";

require(__DIR__ . "/../../DashboardClass.php");

//if ($dashboard->hasUserPermission('users', 'view')) {
$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);
$dashboard->addMenuItem('leftMenu', "users", "home", "Dashboard", "home icon");
$dashboard->addMenuItem('leftMenu', "users", "list_users", "Benutzer", "users icon");

if ($dashboard->hasUserPermission('users', 'manage_modules')) {
    $dashboard->addMenuItem('leftMenu', "users", "list_modules", "Module", "cubes icon");
    $dashboard->addMenuItem('leftMenu', "users", "list_permissions", "Berechtigungen", "key icon");
}
//}

$dashboard->render();