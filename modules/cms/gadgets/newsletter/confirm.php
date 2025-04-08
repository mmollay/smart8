<?php
/*
 * Email bestätigen vom Newsletter
 * mm@ssi.at 04.04.2107
 * UPDATE: Versenden von Bestätigungsmail
 * UPDATE 01.11.2017: persönliche Anrede + Infomail über Neueinträge
 * UPDATE 15.08.2017: Token gesetzt für das Weiterleiten zum vervollständigen und personalisiertes ansprechen
 */
include ('../config.php');
include ('../function.inc.php');

$verify_key = $GLOBALS['mysqli']->real_escape_string($_GET['verify_key']);
$camp_key = $GLOBALS['mysqli']->real_escape_string($_GET['camp_key']);

$db_nl = $cfg_mysql['db_nl'];

$query = $GLOBALS['mysqli']->query("SELECT * from $db_nl.contact WHERE verify_key = '$verify_key' ") or die (mysqli_error($GLOBALS['mysqli']));
$array = mysqli_fetch_array($query);
$contact_id = $array['contact_id'];
// wird für contact-Vervollständigung benötigt
$array['token'] = $array['verify_key'];
$array['intro_personal'] = $TEMPLATES_INTRO2[$array['sex']];
$array['intro_formal'] = $TEMPLATES_INTRO3[$array['sex']];

// Wenn kein Geschlecht gewählt ist
// Wenn kein Vorname vorhanden ist
if (!$template['intro_formal'] or !$template['firstname'] or !$template['intro_personal']) {
	$template['intro_formal'] = $template['intro_personal'] = 'Hallo';
}

if (!$template['secondname']) {
	$template['intro_formal'] = 'Sehr geehrte Damen und Herren';
}

$query2 = $GLOBALS['mysqli']->query("SELECT * from $db_nl.contact2tag WHERE verify_key = '$verify_key' ") or die (mysqli_error($GLOBALS['mysqli']));
$array2 = mysqli_fetch_array($query2);

// $array2['activate'] = 0;

// Wenn Kontakt exitiert sowie der Kontakt oder ein Tag noch nicht aktiviert sind, wird der verify-status für tag und contact aktiv gesetzt
if ($array['contact_id'] and (!$array['activate'] or !$array2['activate'])) {

	// Activate User
	$GLOBALS['mysqli']->query("UPDATE $db_nl.contact SET activate = '1' WHERE verify_key = '$verify_key'") or die (mysqli_error($GLOBALS['mysqli']));

	// Activate Group
	$GLOBALS['mysqli']->query("UPDATE $db_nl.contact2tag SET activate = '1' WHERE verify_key = '$verify_key'") or die (mysqli_error($GLOBALS['mysqli']));

	// Default Data
	$email_subject = 'Freischaltung war erfolgreich';
	$email_text = 'Email wurde freigeschalten - Details folgen.';
	$message = "Emailadresse wurde aktivert";

	/**
	 * ************************************************
	 * Ruft Daten über das formular auf
	 * ************************************************
	 */
	$camp_query = $GLOBALS['mysqli']->query("SELECT * from $db_nl.formular WHERE camp_key = '$camp_key' ") or die (mysqli_error($GLOBALS['mysqli']));
	$camp_array = mysqli_fetch_array($camp_query);
	$promotion_id = $camp_array['promotion_id'];

	// Codeabruf, wenn für diese Promotion vorhanden
	$query_code = $GLOBALS['mysqli']->query("SELECT code,code_id FROM $db_nl.code WHERE promotion_id= '$promotion_id' AND contact_id = 0 LIMIT 1 ") or die (mysqli_error($GLOBALS['mysqli']));
	$array_code = mysqli_fetch_array($query_code);
	$array['promotion_code'] = $array_code['code'];
	$code_id = $array_code['code_id'];

	foreach ($camp_array as $key => $value) {
		// Harauslesen der Werte aus der Campagne + Platzhalter werden umgewandelt - Direkt vom POST über preg_replace
		// $GLOBALS[$key] = preg_replace ( '!{%(.*?)%}!e', '$array[ \1 ]', $value );

		$GLOBALS[$key] = preg_replace_callback('!{%(.*?)%}!', function ($matches) {
			global $array;
			return $array[$matches[1]];
		}, $value);
	}

	// Email -Title
	if ($emailtitle_reg_success)
		$email_subject = $emailtitle_reg_success;
	// Email-Text
	if ($emailtext_reg_success)
		$email_text = $emailtext_reg_success;
	// Text auf der Webseite
	if ($text_reg_success) {
		$message = $text_reg_success;
		$message_class = 'success';
	}

	// Abrufen der Email

	/**
	 * ************************************************************************************************************
	 * mm@ssi.at am 31.10.2017
	 * Bestätigugngmail an Absender schicken,
	 * wenn dieser den gewünschten TAG beinhaltet -
	 * ALERT ist unter LISTBUILDING einzustellen
	 * ************************************************************************************************************
	 */
	if ($camp_array['alert'] && $camp_array['alert_email']) {
		setlocale(LC_TIME, "de_DE");
		$alert_query = $GLOBALS['mysqli']->query("SELECT email from $db_nl.verification where verify_id = '{$camp_array['alert_email']}' and checked = 1 ") or die (mysqli_error($GLOBALS['mysqli']));
		$alert_array = mysqli_fetch_array($alert_query);
		$alert_email = $alert_array['email'];
		if ($alert_email) {
			$email_alert_subject = 'Newsletter-Info';
			$email_alert_text = "Neuer Eintrag in Liste: " . $camp_array['matchcode'] . "<br><br>";
			$email_alert_text .= "Datum: " . date("d.F.Y H:i") . "<br>";
			$email_alert_text .= "Domain: " . $_SERVER['SERVER_NAME'] . "<br>";
			$email_alert_text .= "Email: " . $array['email'] . "<br>";
			if ($array['firstname'] or $array['secondname'])
				$email_alert_text .= "Name: " . $array['firstname'] . " " . $array['secondname'];
			if ($array['plz'])
				$email_alert_text .= "Plz: " . $array['plz'];
			// Mail versenden
			send_mail($camp_array['from_id'], $alert_email, $email_alert_subject, $email_alert_text);
		}
	}

	// Mailversender
	$send_mail = send_mail($camp_array['from_id'], $array['email'], $email_subject, $email_text);

	/****************************************************************************************************
	 * Session-Generator über Followup-Sequenz
	 * 10.05.2018 mm@ssi.at
	 * contact_id wird weiter obenn aus dem Insert erzeugt und in generate_new_session.php übergeben
	 * $contact_id = ''; //übergabe
	 ****************************************************************************************************/
	//include_once (__DIR__ . '/../../../ssi_newsletter/inc_followup/generate_new_session.php');

	if ($send_mail != 'ok') {
		$message .= 'Email-Absendebestätigung fehlgeschlagen';
		$message_class = 'error';
		// Wenn Link gesetzt ist wieder nach Versendung weitergeleitet
	} else {

		// Speichert den User in der Datenbank in Verbindung mit dem Code
		if ($code_id and $contact_id) {
			$GLOBALS['mysqli']->query("UPDATE $db_nl.code SET contact_id = '$contact_id' WHERE code_id = '$code_id' ") or die (mysqli_error($GLOBALS['mysqli']));
			// Prüft Bestand in der Promotion und schickt einen Alert aber einer gewissen Anzahl per Email
			check_alert_code_empty($db_nl, $promotion_id, $camp_array['from_id']);
		}
		if ($camp_array['link_reg_success']) {
			header("Location: {$camp_array['link_reg_success']}");
			exit();
		}
	}
} else if ($array['contact_id']) {
	$message = "Die Adresse <b>{$array['email']}</b> wurde bereits aktiviert!";
	$message_class = 'info';
} else {
	$message = "Es scheint keine Eintragung im System vorhanden zu sein!";
	$message_class = 'info';
}

// echo"<a class='ui button' href='http://{$_SERVER["HTTP_HOST"]'>Zurück zur Startseite</a><br><br>";
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8" />
	<title>SSI-Newsletter Anmeldebestätigung</title>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1' />
	<meta http-equiv='expires' content='0'>
	<meta name='generator' content='SmartKit v<?= $_SESSION['version_smart'] ?>'>
	<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0'>
	<link rel="stylesheet" href="../../smart_form/semantic/dist/semantic.min.css">
</head>

<body style="background: none transparent">
	<br>
	<br>
	<br>
	<div class='ui container'>
		<div align=center class='ui message <?= $message_class; ?>'>
			<br>
			<?= $message; ?><br> <br>

		</div>
		<script type='text/javascript'>
			!function (f, b, e, v, n, t, s) {
				if (f.fbq) return; n = f.fbq = function () {
					n.callMethod ?
						n.callMethod.apply(n, arguments) : n.queue.push(arguments)
				}; if (!f._fbq) f._fbq = n;
				n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; t = b.createElement(e); t.async = !0;
				t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s)
			}(window,
				document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
			fbq('init', '1780492765522093');
			fbq('track', 'PageView');
		</script>
		<noscript>
			<img height="1" width="1" style="display: none"
				src="https://www.facebook.com/tr?id=1780492765522093&ev=PageView&noscript=1" />
		</noscript>

</body>

</html>