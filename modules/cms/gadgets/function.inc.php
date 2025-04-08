<?php

/**
 * Checkt Bestand und schickt gegebenenfalls einen Alarm an User (=user_id)
 * 30.07.2091
 *
 * @param $promotion_id ID
 *        	vom der Promotion
 * @param $from_id -
 *        	ID vom Absender
 */
function check_alert_code_empty($db, $promotion_id, $from_id)
{
	$query = $GLOBALS['mysqli']->query("SELECT alert_empty_code, user_id, title, amazon_promotion_id,
			COUNT(IF(!contact_id,1,null)) count_empty,
			COUNT(IF(contact_id,1,null)) count_sent,
			COUNT(*) count_total,
			(SELECT COUNT(*) FROM $db.amazon_order c WHERE c.amazon_promotion_id = a.amazon_promotion_id) count_used
			FROM
			$db.promotion a LEFT JOIN $db.code b ON a.promotion_id = b.promotion_id
			WHERE a.promotion_id = '$promotion_id' AND alert_empty_code ") or die(mysqli_error($GLOBALS['mysqli']));
	$array = mysqli_fetch_array($query);
	$count_empty = $array['count_empty'];
	$count_total = $array['count_total'];
	$count_sent = $array['count_sent'];
	$count_used = $array['count_used'];
	$title = $array['title'];
	$amazon_promotion_id = $array['amazon_promotion_id'];
	$user_id = $array['user_id'];
	$alert_empty_code = $array['alert_empty_code'];
	// Wenn der freie Codes = als alarm_code, wird Email vom System geschickt
	if ($count_empty == $alert_empty_code) {
		// call email from User
		$query = $GLOBALS['mysqli']->query("SELECT user_name FROM ssi_company.user2company WHERE user_id = '$user_id' ");
		$array = mysqli_fetch_array($query);
		$to_email = $array['user_name'];
		$email_subject = 'Promotion-Codes fast verbraucht';

		$email_text = "<b>Promotion:</b> $title<br>";
		if ($amazon_promotion_id)
			$email_text .= "<b>Amazonnachverfolgungsnummer:</b> $amazon_promotion_id<br><br>";

		$datum = date("d.m.Y", $timestamp);
		$uhrzeit = date("H:i", $timestamp);
		$email_text .= $datum . " - " . $uhrzeiT . " Uhr";

		$email_text .= "Es wurden von bislang von $count_total Codes, $count_sent versendet.<br>
		Restliche Codes:  $count_empty (Eingelöst $count_used)";

		$send_mail = send_mail($from_id, $to_email, $email_subject, $email_text);
	}
}

// versenden einer Mail für das Formular
function send_mail($from_id, $email_to, $subject, $message, $path = false, $style = false)
{

	/**
	 * *****************************************************************
	 * Config - Emails
	 * *****************************************************************
	 */
	date_default_timezone_set('Europe/Belgrade');

	// Defaultmässig werden diese verwendet
	include('config.php');

	$db = $cfg_mysql['db_nl'];

	// $delivery_system = 'mailjet';

	// Liest aus Newsletter DB - die Parameter aus
	if ($from_id) {
		$query = $GLOBALS['mysqli']->query("SELECT * from 
				$db.sender a LEFT JOIN $db.setting b ON a.user_id = b.user_id
				where id = '$from_id'") or die(mysqli_error($GLOBALS['mysqli']));
		$array = mysqli_fetch_array($query);
		if ($array['delivery_system'])
			$delivery_system = $array['delivery_system'];
		if ($array['from_email'])
			$MailConfig['from_email'] = $array['from_email'];
		if ($array['from_name'])
			$MailConfig['from_name'] = $array['from_name'];
		if ($array['error_email'])
			$MailConfig['error_email'] = $array['error_email'];

		// Smtp-Server Daten werden geladen falls vorhanden
		if ($array['smtp_server'] && $array['smtp_user'] && $array['smtp_password']) {
			$MailConfig['smtp_host'] = $array['smtp_server'];
			$MailConfig['smtp_user'] = $array['smtp_user'];
			$MailConfig['smtp_password'] = $array['smtp_password'];
			$MailConfig['smtp_port'] = $array['smtp_port'];
			$MailConfig['smtp_secure'] = $array['smtp_secure'];
		}
	}

	//print_r($MailConfig);

	// echo $delivery_system;
	$MailConfig['to_email'] = $email_to;

	$message = "
	<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
	<html>
	<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
	<title>$subject</title>
	<style type='text/css'>
	a { color:#222222; text-decoration:none; font-weight:normal; }
	$style
	</style>
	</head>
	<body topmargin='0' rightmargin='0' leftmargin='0'>
	$message
	</body></html>";

	// Remove Backspaces
	$subject = stripslashes($subject);
	$message = stripslashes($message);

	$MailConfig['delivery_system'] = $delivery_system;
	$MailConfig['subject'] = $subject; // Betreff
	$MailConfig['text'] = $message;
	$MailConfig['path'] = "$path";
	// print_r ($MailConfig);
	include_once(__DIR__ . '/../php_functions/function_sendmail.php');
	return smart_sendmail($MailConfig); // ok oder error
}

// path: gadgets/functions.inc.php

/*
 * mm@ssi.at 20.04.2011
 * Copy Values from an Array to a file
 */
function array2file($file, $array)
{
	// Open file
	$Datei = fopen($file, "w");

	foreach ($array as $key => $value) {
		$value = preg_replace("/\n/", "<br>", $value);
		fwrite($Datei, "$key=$value\n");
	}
	fclose($Datei);
}

/*
 * mm@ssi.at 20.04.2011
 * Output an array from a file (wert1 = 234).... array(wert1 => 234)
 */
function file2array($file)
{
	if (!is_file($file)) {
		echo "File - $file - nicht erreichbar";
		return;
	}
	$handle = fopen($file, "r");

	while (!feof($handle)) {
		$buffer = fgets($handle, 4096);
		$split = explode("=", $buffer);
		if ($split[0])
			$array[$split[0]] = trim($split[1]);
	}

	fclose($handle);
	return $array;
}

// Ruft alle Paramter des jeweiligen Layers auf
// 25.01.2017
// Wird derzeit eingesetzt zum abrufen der parameter für Newsletter und Gästebuch(submit)
function call_layer_parameter($layer_id)
{

	// Defaultmässig werden diese verwendet
	include('config.php');
	$db = $cfg_mysql['db'];

	$sql = $GLOBALS['mysqli']->query("SELECT * from $db.smart_layer WHERE layer_id = '$layer_id'") or die(mysqli_error($GLOBALS['mysqli']));
	$array_fetch = mysqli_fetch_array($sql);
	$GLOBALS['format'] = $array_fetch['format'];
	$GLOBALS['from_id'] = $array_fetch['from_id'];
	$gadget_array_n = explode("|", $array_fetch['gadget_array']);
	if ($array_fetch['gadget_array']) {
		foreach ($gadget_array_n as $array) {
			$array2 = preg_split("[=]", $array, 2);
			$GLOBALS[$array2[0]] = $array2[1];
		}
	}
}

// function fuer callback fuer das SELECT
function change_link($matches)
{
	global $index_id;

	// global $file_ending;
	$site_id = $matches[1];
	$file_ending = check_php_script($site_id);

	// Titel auslesen und als url einbinden
	$query = $GLOBALS['mysqli']->query("SELECT site_url,fk_id FROM smart_langSite WHERE fk_id = '$site_id' ") or die(mysqli_error($GLOBALS['mysqli'])); //AND lang = '{$_SESSION['page_lang']}' 
	$array = mysqli_fetch_array($query);
	if (!$array['site_url'])
		$array['site_url'] = $array['fk_id'];
	$site_url = $array['site_url'] . "$file_ending";
	$site_id = $array['fk_id'];
	if ($index_id == $site_id)
		return "index$file_ending";
	else
		return $site_url;
}

// Check ob Bazar oder andere Seiten welche php verwenden eine andere Endung brauchen
function check_php_script($site_id)
{

	//Check global setting
	$set_dynamic = call_smart_option($_SESSION['smart_page_id'], '', 'global_set_dynamic');

	//Check site settign
	if (!$set_dynamic)
		$set_dynamic = call_smart_option('', $site_id, 'set_dynamic');

	if ($set_dynamic) {
		$set_dynamic_modul = true;
	} else {
		// Prüft ob ein dynamische Element verwendet werden muss - Bsp. bazar und co.
		$sql = $GLOBALS['mysqli']->query("SELECT gadget FROM smart_layer WHERE (gadget = 'bazar' OR gadget = 'placeholder') AND site_id = '$site_id' ");
		$query = mysqli_fetch_array($sql);
		$set_dynamic_modul = mysqli_num_rows($sql);
	}
	if ($set_dynamic_modul) {
		return '.php';
	} else
		return '.html';
}


