<?php
include (__DIR__ . '/../../config.php');
include (__DIR__ . '/functions.inc.php');

if (!mysqli_select_db($db, 'ssi_newsletter')) {
    die('Datenbankauswahl fehlgeschlagen: ' . mysqli_error($db));
}
