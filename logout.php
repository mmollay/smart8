<?php
session_start();
session_destroy(); // Zerstört alle Session-Daten
echo 'Logged out'; // Eine Bestätigung, die per AJAX abgefangen werden kann

//Weiterleitung zur Login-Seite
header('Location: login.php');