<?php
/**
 * mm@ssi.at am 22.04.2017
 *
 * Diese Configdatei wird bei Erzeugen der Webseite "admin/ajax/page_generate.php" überschrieben und im System abgespeichert.
 * Die Sessionwerte werden in "../login/config_main.php" erzeugt.
 */

// Fehlerberichterstattung auf alle außer E_NOTICE setzen
error_reporting(E_ALL ^ E_NOTICE);

// Starte die PHP-Sitzung
session_start();

// Setze die Standard-Zeitzone auf Europa/Wien
date_default_timezone_set('Europe/Vienna');

// Mail-Konfigurationsdaten aus der Sitzung holen
$MailConfig['return_path'] = $MailConfig['error_email'] = $_SESSION['MailConfig']['return_path'];
$MailConfig['from_email'] = $_SESSION['MailConfig']['from_email'];
$MailConfig['from_title'] = $MailConfig['from_name'] = $_SESSION['MailConfig']['from_title'];
$MailConfig['smtp_host'] = $MailConfig['smtp_server'] = $_SESSION['MailConfig']['smtp_host'];
$MailConfig['smtp_user'] = $_SESSION['MailConfig']['smtp_user'];
$MailConfig['smtp_password'] = $_SESSION['MailConfig']['smtp_password'];
$MailConfig['smtp_port'] = $_SESSION['MailConfig']['smtp_port'];
$MailConfig['smtp_secure'] = $_SESSION['MailConfig']['smtp_secure'];
$MailConfig['mailjet_smtp_user'] = $_SESSION['MailConfig']['mailjet_smtp_user'];
$MailConfig['mailjet_smtp_password'] = $_SESSION['MailConfig']['mailjet_smtp_password'];

// Andere Session-Variablen
$smart_company_id = $_SESSION['smart_company_id'];
$site_key = $_SESSION['recaptcha']['site_key'];
$secret_key = $_SESSION['recaptcha']['secret_key'];
$smart_user_id = $_SESSION['user_id'];
$user_id = $_SESSION['user_id'];
$page_id = $_SESSION['smart_page_id'];
$company_id = $_SESSION['cart_company_id'] = $_SESSION['company_id'];
$company = $_SESSION['company'];

// Client-Token holen (aus Session oder Cookie)
$client_token = isset($_SESSION['client_token']) ? $_SESSION['client_token'] : ($_COOKIE["client_token"] ?? null);

// Lieferungssystem für localhost auf PHPMailer setzen
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $MailConfig['delivery_system'] = 'phpmailer';
}

// MySQL-Konfigurationsdaten aus der Sitzung holen
$cfg_mysql['user'] = $_SESSION['mysql']['user'];
$cfg_mysql['password'] = $_SESSION['mysql']['password'];
$cfg_mysql['server'] = $_SESSION['mysql']['server'];
$cfg_mysql['db'] = $_SESSION['mysql']['db']; // ssi_smart(x)
$cfg_mysql['db_nl'] = $_SESSION['mysql']['db_nl']; // ssi_newsletter(x)
$cfg_mysql['db_map'] = $_SESSION['mysql']['db_map']; // ssi_fruitmap
$cfg_mysql['db_bazar'] = $_SESSION['mysql']['db_bazar']; // ssi_bazar
$cfg_mysql['db_21'] = $_SESSION['mysql']['db_21']; // ssi_fruitmap
$cfg_mysql['db_learning'] = $_SESSION['mysql']['db_learning']; // ssi_fruitmap
$cfg_mysql['db_faktura'] = $_SESSION['mysql']['db_faktura']; // ssi_fruitmap

// Verbindung zur MySQL-Datenbank herstellen
$GLOBALS['mysqli'] = new mysqli($cfg_mysql['server'], $cfg_mysql['user'], $cfg_mysql['password'], $cfg_mysql['db']) or die("Could not open connection to server {$cfg_mysql['server']}");

// Begrüßungs-Arrays
$TEMPLATES_INTRO2 = array(
    'm' => 'Lieber',
    'f' => 'Liebe',
    'c' => 'Liebe Firma',
    'e' => 'Hallo'
);
$TEMPLATES_INTRO3 = array(
    'm' => 'Sehr geehrter Herr',
    'f' => 'Sehr geehrte Frau',
    'c' => 'Sehr geehrte Firma',
    'e' => 'Sehr geehrte Damen und Herren'
);
?>
