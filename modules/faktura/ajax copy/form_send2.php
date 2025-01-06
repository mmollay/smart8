<?php
require ("../config.inc.php");
include_once (__DIR__ . '/../../ssi_smart/php_functions/function_sendmail.php');

// notwendig damit der Nachricht übergeben wird, weiss aber nicht warum das "message" übergeben werden kann
$_POST['message'] = $_POST['message2'];

if (!$_SESSION['faktura_company_id']) {
	echo "Firma ist nicht gew&auml;hlt";
	exit();
}

/* Auslesen der SMTP-Daten der Firma falls im Profil vorhanden */
$query = $GLOBALS['mysqli']->query("Select * from company WHERE company_id = '{$_SESSION['faktura_company_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
$smtp_array = mysqli_fetch_array($query);

if ($smtp_array['smtp_server']) {
	$MailConfig['smtp_host'] = $smtp_array['smtp_server'];
	$MailConfig['smtp_user'] = $smtp_array['smtp_user'];
	$MailConfig['smtp_password'] = $smtp_array['smtp_password'];
	$MailConfig['smtp_port'] = $smtp_array['smtp_port'];
	$MailConfig['smtp_secure'] = $smtp_array['smtp_secure'];
	$MailConfig['return_path'] = $smtp_array['smtp_return'];
	$MailConfig['from_email'] = $smtp_array['smtp_email'];
} else {
	require ("../../login/config_mail.php");
	$MailConfig['from_title'] = 'Verrechnung';
	$MailConfig['from_email'] = 'verrechnung@ssi.at';
}

if ($smtp_array['smtp_title'])
	$MailConfig['from_title'] = $smtp_array['smtp_title'];
if ($smtp_array['smtp_email'])
	$MailConfig['from_email'] = $smtp_array['smtp_email'];
if ($smtp_array['smtp_return'])
	$MailConfig['return_path'] = $smtp_array['smtp_return'];

/*
 * Massenversendung - TEMPLATES umwandeln und und id's auslesen
 */

if ($_POST['bill_id'] == 'all') {
	// include_once ('../function.inc.php');

	$mysql_query = $GLOBALS['mysqli']->query("SELECT bill_id,company_id,remind_level,email,bill_number,brutto,firstname,secondname,gender,title,date_create FROM bills WHERE remind_level = 0 AND email !='' and date_booking = '0000-00-00' $mysql_list_filter") or die(mysqli_error($GLOBALS['mysqli']));
	while ($mysql_array = mysqli_fetch_array($mysql_query)) {
		$bill_id = $mysql_array['bill_id'];
		$array_user[$bill_id] = $bill_id;
		$email = $mysql_array['email'];
		$company_id = $mysql_array['company_id'];
		$bill_number = $mysql_array['bill_number'];
		$firstname = $mysql_array['firstname'];
		$secondname = $mysql_array['secondname'];
		$gender = $mysql_array['gender'];
		$title = $mysql_array['title'];
		$brutto = $mysql_array['brutto'];
		$remind_level = $mysql_array['remind_level'];
		$date_create = $mysql_array['date_create'];

		if ($firstname or $secondname) {
			if ($gender == 'f')
				$gender = "Sehr geehrte Frau";
			elseif ($gender == 'm')
				$gender = "Sehr geehrter Herr";
			else
				$gender = "Sehr geehrte(r)";
		} else
			$gender = "Sehr geehrte Damen und Herren";

		$subject = preg_replace("/\[%bill_number%\]/", $bill_number, $_POST['subject']);
		$message = preg_replace("/\[%bill_number%\]/", $bill_number, $_POST['message']);
		$message = preg_replace("/\[%summery%\]/", nr_format($brutto), $message);
		$message = preg_replace("/\[%firstname%\]/", $firstname, $message);
		$message = preg_replace("/\[%secondname%\]/", $secondname, $message);
		$message = preg_replace("/\[%gender%\]/", $gender, $message);
		$message = preg_replace("/\[%title%\]/", $gender, $message);
		$message = preg_replace("/\[%date%\]/", $date_create, $message);

		$MailConfig[$bill_id]['to_email'] = $email;
		$MailConfig[$bill_id]['subject'] = $subject;
		// $MailConfig[$bill_id]['body'] = mb_convert_encoding ( $message, "iso-8859-1", "UTF-8" );
		$MailConfig[$bill_id]['body'] = $message;
	}

} else {
	/*
	 * Singleversendung - Einstellung sind bereits in der ersten Maske sichtbar
	 */
	// $bill_id = $_POST['id'];
	$bill_id = $_POST['bill_id'];

	$array_user[] = $bill_id;

	$MailConfig[$bill_id]['to_email'] = $_POST['email'];
	$MailConfig[$bill_id]['cc_email'] = $_POST['email_cc'];
	// $MailConfig[$bill_id]['bcc_email'] = $_POST['email_bcc'];
	$MailConfig[$bill_id]['subject'] = $_POST['subject'];
	// $MailConfig[$bill_id]['body'] = mb_convert_encoding ( $_POST['message'], "iso-8859-1", "UTF-8" );
	$MailConfig[$bill_id]['body'] = $_POST['message'];
}

// Falls keine Rechung vorhanden ist, Prozess abbrechen
if (!$array_user) {
	echo "Keine Rechnung zum versenden vorhanden!";
	exit();
}

$path_temp = $_SERVER['DOCUMENT_ROOT'] . "/temp/";

// generiert ein temp. Verzeichnis
exec("mkdir " . $path_temp);
exec("mkdir " . $path_temp . $_SESSION['user_id']);


foreach ($array_user as $id) {
	$MailConfig['addAttachment'][1] = '';
	$bill_array = '';

	$_GET['bill'] = $id;

	// auf Server speichern bei PDF erstellen
	$pdf_output['modus'] = 'F';
	// Pfad wo die Datei temp gelagert wird
	$pdf_output['path'] = $path_temp . $_SESSION['user_id'] . "/";

	// erzeugen der Datei
	include (__DIR__ . '/../pdf_generator.php');

	// Gesamter Pfad fuer das Attachment fuer das Versenden einer Email
	$pdf_pfad = $pdf_output['path'] . $pdf_dateiname;

	// $MailConfig['from_email'] = 'martin@ssi.at';
	// $MailConfig['delivery_system'] = 'phpmailer';
	$MailConfig['relay_email'] = $MailConfig['return_path']; // ZURÜCK AN Email (Kann leer bleiben)
	$MailConfig['relay_name'] = $MailConfig['return_path']; // ZURÜCK AN Email (Kann leer bleiben)
	$MailConfig['to_email'] = $MailConfig[$id]['to_email']; // AN Email
	$MailConfig['cc_email'] = $MailConfig[$id]['cc_email']; // AN Email CC
	$MailConfig['bcc_name'] = $MailConfig[$id]['to_email']; // AN Name BCC
	$MailConfig['subject'] = $MailConfig[$id]['subject']; // Betreff
	$MailConfig['text'] = nl2br($MailConfig[$id]['body']); // Text
	$MailConfig['addAttachment'][1] = $pdf_pfad;

	// Eintrag in Logfile über status
	$sender_result = smart_sendmail($MailConfig, true); // ok oder error

	if (is_array($sender_result)) {
		$mail_info = $sender_result['mail_info'];
		$MessageID = $sender_result['MessageID'];
	} else
		$mail_info = $sender_result;

	if ($mail_info != 'ok')
		$error = 1;
	else
		$error = 0;

	// Sendung bestätigen

	if ($mail_info == 'ok') {
		$count++;
		logfile('Email versendet', "{$MailConfig[$id]['to_email']}<br>{$MailConfig[$id]['subject']}<br><br>{$MailConfig[$id]['body']}", 1, '', $id, $mail_info, $MessageID);
		// $_POST['just_send'] = 1;
		if (!$_POST['just_send']) { // Speichert nicht wenn Rechnung nur per Email versendet wird

			if ($_POST['remind_level'])
				$level = $_POST['remind_level'];

			// Auslesen der Mahnzeiten
			$interval = mysql_singleoutput("SELECT remind_time$level FROM company WHERE company_id = '{$_SESSION['faktura_company_id']}' ");
			// Default interavel
			if (!$interval)
				$interval = 10;
			$GLOBALS['mysqli']->query("UPDATE bills SET
			sendet = sendet+1,
			date_send = NOW(),
			send_status = 'ok',
			date_remind = DATE_ADD(NOW(), INTERVAL $interval DAY),
			remind_level = '{$_POST['remind_level']}'
			WHERE bill_id = $bill_id ") or die(mysqli_error($GLOBALS['mysqli']));
		}
	} else {
		$error = 1;
		echo "Error: " . $sender_result . "<br>";
		$GLOBALS['mysqli']->query("UPDATE bills SET send_status='$sender_result' WHERE bill_id = $bill_id ") or die(mysqli_error($GLOBALS['mysqli']));
		logfile('Email Versendefehler', "$sender_result", 2, $client_id, $bill_id);
	}

	// Muss derweilen auskommentiert werden, da der Versand über Mailjet nicht syncon erfolgt -
	// in diesem Zusammenhang müssen die Dateien in einer anderen Form gelöscht werden
	// exec ( "rm $pdf_pfad" );
}

if ($count == 1 and $error != 1)
	echo "1 Rechnung wurde erfolgreich versendet!";
elseif ($count >= 1 and $error != 1)
	echo "$count Rechnungen wurden erfolgreich versendet!";
?>