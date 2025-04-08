<?php
// Verbindung zur Datenbank herstellen
include ('../../config.php');
include_once ('../mysql_days21.inc.php');

$clone_challenge = $_POST['challenge_id'];

//Auslesen der Parameter und neuen db-satz anlegen
//  - Bezugherstellung durch speichern der Parent 1
