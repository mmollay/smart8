<?php
session_start();
/**
 * export_web.php - Datenbank-Export auf Excel
 *
 * @author Martin Mollay
 * @last-changed 2011-10-21
 *
 */
$list_id = $_GET['list_id'];
$mysql_connect_path = $_SESSION['smart_list_config'][$list_id]['mysql_connect_path'];

include (realpath($mysql_connect_path));
require_once ('dbio.inc.php');
date_default_timezone_set('Europe/Belgrade');
// Zugangsdaten fuer Datenbank
$DB_HOST = 'localhost';
$DB_USER = $cfg_mysql['user'];
$DB_PASS = $cfg_mysql['password'];
// $DB_NAME = '';
// $DB_TABLE = $_GET['file'];

// Session wir in mysql_list.php erzeugt
// mm@ssi.at 04.06.2012

$DB_TABLE = $_SESSION['export']['table'];
$EXPORT_FILTER = $_SESSION['export']['filter'];
$DB_FIELDS = $_SESSION['export']['field'];

if (! $_SESSION['user_id'])
    exit();

/**
 * * MAIN **
 */
$my_dbio = new DBIO($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Header fuer Excel schreiben
$filename = "export_" . date('Ymd');
// header("Content-Disposition: attachment; filename=\"$filename.txt\"");
// header("Content-Type: application/vnd.ms-excel");

// Header fuer Textdatei
header("Content-Disposition: attachment; filename=\"$filename.csv\"");
header("Content-Type: text/plain");

// Daten in temp. File schreiben
$rows = $my_dbio->export("-", $DB_TABLE, explode(',', $DB_FIELDS), "\t", $EXPORT_FILTER);

// Nach der folgenden Zeile soll nichts mehr stehen; auch keine Leerzeilen
?>