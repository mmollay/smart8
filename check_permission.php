<?php
session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Prüfen, ob eine Sitzung existiert (z.B. durch Überprüfen der client_id)
if (!isset($_SESSION['client_id'])) {
    // Benutzer ist nicht angemeldet, Weiterleitung zur Login-Seite
    header('Location: ../../login.php?error=not_logged_in');
    exit();
}
?>