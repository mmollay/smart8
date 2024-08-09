<?php
include (__DIR__ . '/../../config.php');
include (__DIR__ . '/functions.inc.php');

//Key für Mailjet wird versentet in send_emails_background.php
$apiKey = '452e5eca1f98da426a9a3542d1726c96';
$apiSecret = '55b277cd54eaa3f1d8188fdc76e06535';

$dbname = 'ssi_newsletter';

//Wird gesetzt in ../../config.php 
//$host = 'localhost';
//$username = 'smart';
//$password = 'Eiddswwenph21;';

//Select the database
if (!mysqli_select_db($db, $dbname)) {
    die('Datenbankauswahl fehlgeschlagen: ' . mysqli_error($db));
}