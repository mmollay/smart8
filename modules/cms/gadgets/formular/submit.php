<?php
/*
 * Save Formular
 * mm@ssi.at 01.04.2017
 * Version 1.0
 */
include ('../config.php'); // call site_key & secret_key and more
include_once ('../function.inc.php');
include_once ('../../php_functions/functions.php');

$layer_id = $_POST ['layer_id'];
// $from_id = $_POST['from_id'];
// $camp_key = $_POST['camp_key'];

if (! $layer_id) {
	echo "alert('Versendung nicht möglich es fehlt die LayerID');";
	exit ();
}

// Config auslesen
//$array_label = call_layer_parameter($layer_id);
call_smart_element_option ( $layer_id, '', true );
//$camp_key = $array_label['camp_key'];

$array ['user_name'] = $receive_email; // Alternative Emailempfänger auslesen
$array ['set_recaptcha'] = $recaptcha; // Check ob Recaptcha-Function
// $feedback_id = $layer_id;
/**
 * *********************************************************
 * Prüft ob Anmeldung über Pot läuft oder einem Menschen
 * *********************************************************
 */
if ($array ['set_recaptcha']) {
	$secretKey = $secret_key;
	$verifydata = file_get_contents ( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $_POST ['recaptcha'] );
	$response = json_decode ( $verifydata );
	if ($response->success == false) {
		echo "alert('Bitte bestätigen, dass Sie kein Roboter sind!');";
		exit ();
	}
}
/**
 * *********************************************************
 */

// call IP - from Client
if (! isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
	$client_ip = $_SERVER ['REMOTE_ADDR'];
} else {
	$client_ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
}

$layer_id = $GLOBALS ['mysqli']->real_escape_string ( $_POST ['layer_id'] );

$query = $GLOBALS ['mysqli']->query ( "SELECT * from $db_smart.smart_formular WHERE layer_id = '$layer_id' ORDER by sort" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
while ( $array2 = mysqli_fetch_array ( $query ) ) {

	$id = $array2 ['field_id'];
	$newsletter_field = $array2 ['newsletter_field'];

	if ($newsletter_field == 'intro')
		$array_value = array ('f' => 'Frau','m' => 'Herr' );
	elseif ($newsletter_field == 'country')
		$array_value = array ('at' => 'Österreich','de' => 'Deutschland','ch' => 'Schweiz' );
	else
		$array_value = json_decode ( $array2 ['value'], true );

	$type = $array2 ['type'];
	$label = $array2 ['label'];
	// $text = $array2['text'];
	$value = $_POST ['field-' . $id];

	if ($type == 'uploader') {
		$path = $_POST ['field-' . $id . '_upload_dir'];
	}

	// Setzt Feld für Newsletter falls vorhanden
	if ($array2 ['newsletter_field'])
		$newsletter_client [$array2 ['newsletter_field']] = $value;

	if (isset ( $value )) {
		if ($type == 'select' or $type == 'radio')
			$value = $array_value [$value];

		if ($type == 'textarea')
			$list_body_message .= "<tr><td colspan=2>$label<br>" . nl2br ( "$value" ) . "</td></tr>";
		elseif (strlen ( $label ) < 20)
			$list_body_message .= "<tr><td>$label</td><td>$value $unit</td></tr>";
		else
			$list_body_message .= "<tr><td colspan=2>$label<br>$value $unit</td></tr>";
	}

	$unit = '';
}

$form_style = "
table { border-collapse: collapse; min-width:400px; }
tr:nth-child(even) { background-color: #dddddd; }
td, th { border: 1px solid #dddddd; text-align: left; padding: 8px; }
";

$list_body_message_th = "<tr><th>Title</th><th>Eingabe</th></tr>";

// Uebergabe von Vor und Nachname falls vorhanden
$body_message .= "$body_message_css";
$body_message .= "<i>Achtung! - Bitte als Antwortadresse nicht den Absender der Email verwenden.</i><br><br>";
$body_message .= "<table>$list_body_message_th $list_body_message</table>";

if ($subject_text) {
	$subject = change_temp ( $subject_text );
} else
	$subject = "Emailnachricht von " . $_SERVER ["HTTP_HOST"];

/**
 * *****************************************************************
 * Config - Emails
 * *****************************************************************
 */

if (! $array ['user_name']) {
	// Email-Empfänger
	$query = $GLOBALS ['mysqli']->query ( "SELECT user_name FROM ssi_company.user2company WHERE user_id = '$user_id' " );
	$array = mysqli_fetch_array ( $query );
}
if ($list_body_message) {
	$send_value = send_mail ( $from_id, $array ['user_name'], $subject, $body_message, $path, $style = $form_style );
	//$send_value = 'ok';
}

if ($send_value == 'ok') {

	if ($button_url) {
		if ($button_url and $_SESSION ['admin_modus'])
			$href = "?site_select=$button_url";
		else {
			// Auslesen der aktuellen Seite über die Datenbank durch Verwendung der site_id
			$matches [1] = $button_url;
			$href = change_link ( $matches );
		}
	}

	if ($button_link) {
		if (! preg_match ( '[http]', $button_link )) {
			$button_link = "https://$button_link";
		}
		$href = "$button_link";
	}

	// Bei Link gibt es eine Weiterleitung
	if ($href) {
		echo "$('.center_content').html(\"<div class='ui active inverted dimmer'><div class='ui text loader'>Weiterleitung erfolgt...</div></div>\");";

		if ($button_target) {
			echo "window.open('$href','_blank');";
		} else {
			echo "window.location.replace('$href');";
		}

		// Sonst Ausgabe, dass zugestellt wurde
	} else {
		if ($submit_text)
			$response_text = change_temp($submit_text);
		else {
			$response_text = "<div align=center><br>Versendung war erfolgreich!<br><br></div>";
		}
		echo "$('#context_form$layer_id').html('$response_text' );";
	}

	// Wenn Newsletter ebenfalls aktiviert ist, wird eine NL-Anmeldung verschickt
	if (($camp_key or $camp_key_formular) and $newsletter_client ['email']) {

		// Wenn Eintrag für Newsletter vorhanden ist
		$intro = $newsletter_client ['intro'];
		$company_1 = $newsletter_client ['company_1'];
		$company_2 = $newsletter_client ['company_2'];
		$firstname = $newsletter_client ['firstname'];
		$secondname = $newsletter_client ['secondname'];
		$country = $newsletter_client ['country'];
		$city = $newsletter_client ['city'];
		$zip = $newsletter_client ['zip'];
		$email_to = $newsletter_client ['email'];
		$web = $newsletter_client ['web'];
		$street = $newsletter_client ['street'];
		$telefon = $newsletter_client ['telefon'];
		$commend = $newsletter_client ['commend'];
		$commend2 = $newsletter_client ['commend2'];
		$birth = $newsletter_client ['birth'];
		$set_newsletter = $newsletter_client ['set_newsletter'];
		
		if ($set_newsletter or $camp_key_formular or $camp_key) {
			
			include (__DIR__ . '/../newsletter/include_submit.inc.php');

			// if ($newsletter_client['uploads'] !== '') {
			// Bilder werden in den Ordner verschoben
			if ($path)
				exec ( "mv $path*  /var/www/ssi/smart_users/{$_SESSION['company']}/user{$_SESSION['user_id']}/newsletter/user/$contact_id/" );
			// }
		}
	}
} else {
	if ($send_value) {
	    $send_value = json_encode($send_value);
		echo "alert($send_value); ";
	}
	exit ();
}

//change template
function change_temp($text) {
	return preg_replace_callback ( '!{%(.*?)%}!', function ($matches) {
		global $newsletter_client;
		return $newsletter_client [$matches [1]];
	}, $text );
}
