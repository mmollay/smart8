<?php
$reg_domain = $_SERVER["HTTP_HOST"];
$token = md5(uniqid(rand(), true));
$token2 = md5(uniqid(rand(), true));
$verify_key = $token . $token2;
$db = $cfg_mysql['db_nl'];

// call IP - from Client
if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$client_ip = $_SERVER['REMOTE_ADDR'];
} else {
	$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

if ($_SESSION['admin_modus']) {
	//$reg_domain = $reg_domain . "/ssi_smart";
}

// HTTP OR HTTPS
$protocol = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://");

// Dient als Platzhalter für den Bestätigungs-code
$_POST['verify_link'] = "<a href='$protocol$reg_domain/gadgets/newsletter/confirm.php?verify_key=$verify_key&camp_key=$camp_key'>[Bestätigen]</a>";
$_POST['token'] = $verify_key;
$email_from = "office@ssi.at";
$subject = "Newsletter bestätigen";
$set_message = "Bitte bestätigen Sie den Newsletter.<br>Klicken Sie auf diesen Link: " . $_POST['verify_link'];

if (!filter_var($email_to, FILTER_VALIDATE_EMAIL)) {
	echo "$('#context_form$layer_id,#newsletter_content$layer_id').prepend(\"<div class='message error ui'>Emailadresse scheint ungültig zu sein!</div>\");";
	return;
} elseif (!$layer_id) {
	echo "$('#context_form$layer_id,#newsletter_content$layer_id').prepend(\"<div class='message error ui'>Versendung nicht möglich es fehlt die LayerID</div>\");";
	return;
}

/**
 * ************************************************************************************************************
 * Ruft Daten über die Main-Kampange für Newsletter auf
 * ************************************************************************************************************
 */
if ($camp_key) {

	$query = $GLOBALS['mysqli']->query("SELECT * from $db.formular WHERE camp_key = '$camp_key' ") or die(mysqli_error($GLOBALS['mysqli']));
	$camp_array = mysqli_fetch_array($query);
	$form_id = $camp_array['form_id'];
	$add_contact_faktura = $camp_array['add_contact_faktura'];
	//$user_id = $camp_array['user_id'];

	if (is_array($camp_array)) {
		foreach ($camp_array as $key => $value) {
			${$key} = change_temp($value);
			//${$key} = $value;
		}

		// TAGS welche zugewiesen werden sollen auslesen
		$query_tag = $GLOBALS['mysqli']->query("SELECT * from $db.formular2tag WHERE form_id = '$form_id' ") or die(mysqli_error($GLOBALS['mysqli']));
		while ($array_tag = mysqli_fetch_array($query_tag)) {
			$array_tag_id[] = $array_tag['tag_id'];
		}
	}
}

/**
 * ************************************************************************************************************************
 * Erweiterung am 30.07.2019
 * Auswahl findet bei Formular statt, es wird ein automatischer Eintrag gemacht ohne, dass der User diesen bestätigen muss,
 * Bsp.
 * Fotowettbewerb oder ähnliches
 * ************************************************************************************************************************
 */
if ($camp_key_formular) {

	// Wenn User angelegt werden soll, wird dieser direkt auf "aktiv" geschalten
	$set_activate = 1;

	$query = $GLOBALS['mysqli']->query("SELECT * from $db.formular WHERE camp_key = '$camp_key_formular' ") or die(mysqli_error($GLOBALS['mysqli']));
	$camp_array = mysqli_fetch_array($query);
	$form_formular_id = $camp_array['form_id'];
	$add_contact_faktura = $camp_array['add_contact_faktura'];

	if (is_array($camp_array)) {
		foreach ($camp_array as $key => $value) {
			${$key} = change_temp($value);
		}

		// TAGS welche zugewiesen werden sollen auslesen
		$query_tag = $GLOBALS['mysqli']->query("SELECT * from $db.formular2tag WHERE form_id = '$form_formular_id' ") or die(mysqli_error($GLOBALS['mysqli']));
		while ($array_tag = mysqli_fetch_array($query_tag)) {
			$array_formular_tag_id[] = $array_tag['tag_id'];
		}
	}

}

/**
 * *************************************************************************************************************************
 * Erweiterung für FAKTURA - 10.10.2020 mm@ssi.at
 * Anlegen des Kunden in der Faktura-Db
 * Erweiterung für das Formular für Obstststadt Mitglieder anlegen
 * *************************************************************************************************************************
 */
if ($add_contact_faktura) {

	if ($user_id == '40')
		$add_faktura_db = 'ssi_faktura';
	else
		$add_faktura_db = "ssi_faktura$user_id";

	$email_to = $GLOBALS['mysqli']->real_escape_string($email_to);

	// Prüfen ob User bereits aktiviert ist, contact_id und Aktivierung auslesen
	$query = $GLOBALS['mysqli']->query("SELECT * from $add_faktura_db.client WHERE email = '$email_to' AND user_id = '$user_id' ") or die(mysqli_error($GLOBALS['mysqli']));
	$array = mysqli_fetch_array($query);
	$client_id = $array['client_id']; // UserID

	$sql = "INSERT into $add_faktura_db.client SET
		user_id   = '" . $GLOBALS['mysqli']->real_escape_string($user_id) . "',
		client_number = (SELECT MAX( client_number ) FROM $add_faktura_db.client cust)+1,
		email     = '$email_to',
		gender       = '" . $GLOBALS['mysqli']->real_escape_string($intro) . "',
		birth = '" . $GLOBALS['mysqli']->real_escape_string($birth) . "',
	    company_1 = '" . $GLOBALS['mysqli']->real_escape_string($company_1) . "',
		company_2 = '" . $GLOBALS['mysqli']->real_escape_string($company_2) . "',
		firstname = '" . $GLOBALS['mysqli']->real_escape_string($firstname) . "',
		secondname = '" . $GLOBALS['mysqli']->real_escape_string($secondname) . "',
		verify_key = '" . $GLOBALS['mysqli']->real_escape_string($verify_key) . "',
		street  = '" . $GLOBALS['mysqli']->real_escape_string($street) . "',
	    tel = '" . $GLOBALS['mysqli']->real_escape_string($telefon) . "',
		web = '" . $GLOBALS['mysqli']->real_escape_string($web) . "',
		zip = '" . $GLOBALS['mysqli']->real_escape_string($zip) . "',
		city = '" . $GLOBALS['mysqli']->real_escape_string($city) . "',
		country = '" . $GLOBALS['mysqli']->real_escape_string($country) . "',
		reg_date = now(),
		join_date = now(),
		reg_ip =  '" . $GLOBALS['mysqli']->real_escape_string($client_ip) . "',
		reg_domain = '" . $GLOBALS['mysqli']->real_escape_string($reg_domain) . "'
		";

	if (!$client_id) {
		$GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
		$client_id = mysqli_insert_id($GLOBALS['mysqli']);
	} else {
		echo "$('#context_form$layer_id,#newsletter_content$layer_id').append(\"<div align=center>Der Kunde wurde bereits angelegt.</div>\");";
		//exit ();
	}
}

/**
 * *************************************************************************************************************************
 * 10.10.2020 mm@ssi.at
 * Prüfen und Contact - einschreiben in das NL-System
 * *************************************************************************************************************************
 */

if ($set_newsletter) {

	// Prüfen ob User bereits aktiviert ist, contact_id und Aktivierung auslesen
	$query = $GLOBALS['mysqli']->query("SELECT * from $db.contact WHERE email = '$email_to' AND user_id = '$user_id' ") or die(mysqli_error($GLOBALS['mysqli']));
	$array = mysqli_fetch_array($query);
	$contact_id = $array['contact_id']; // UserID
	$contact_activate = $array['activate']; // Aktivierung

	$sql = "
	INSERT into $db.client_logfile SET
	ip = '$client_ip',
	email = '$email_to',
	contact_id = '$contact_id',
	domain = '$reg_domain' ";

	// Logfile for the client
	$GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));

	// Insert new USER
	if (!$contact_id) {
		$GLOBALS['mysqli']->query("INSERT into $db.contact SET
	user_id   = '" . $GLOBALS['mysqli']->real_escape_string($user_id) . "',
	email     = '" . $GLOBALS['mysqli']->real_escape_string($email_to) . "',
	sex       = '" . $GLOBALS['mysqli']->real_escape_string($intro) . "',
    company_1 = '" . $GLOBALS['mysqli']->real_escape_string($company_1) . "',
	company_2 = '" . $GLOBALS['mysqli']->real_escape_string($company_2) . "',
	firstname = '" . $GLOBALS['mysqli']->real_escape_string($firstname) . "',
	secondname = '" . $GLOBALS['mysqli']->real_escape_string($secondname) . "',
	verify_key = '" . $GLOBALS['mysqli']->real_escape_string($verify_key) . "',
    telefon = '" . $GLOBALS['mysqli']->real_escape_string($telefon) . "',
	web = '" . $GLOBALS['mysqli']->real_escape_string($web) . "',
	zip = '" . $GLOBALS['mysqli']->real_escape_string($zip) . "',
	city = '" . $GLOBALS['mysqli']->real_escape_string($zip) . "',
	country = '" . $GLOBALS['mysqli']->real_escape_string($country) . "',
    commend = '" . $GLOBALS['mysqli']->real_escape_string($commend) . "',
	commend2 = '" . $GLOBALS['mysqli']->real_escape_string($commend2) . "',
	activate   = '$set_activate',
	reg_date = now(),
	reg_ip =  '" . $GLOBALS['mysqli']->real_escape_string($client_ip) . "',
	reg_domain = '" . $GLOBALS['mysqli']->real_escape_string($reg_domain) . "'
	") or die(mysqli_error($GLOBALS['mysqli']));
		$contact_id = mysqli_insert_id($GLOBALS['mysqli']);
	} // OR Update, User exists an is activated
	elseif ($contact_id) {
		if ($intro)
			$add_field .= "sex = '" . $GLOBALS['mysqli']->real_escape_string($intro) . "',";
		if ($company_1)
			$add_field .= "firstname = '" . $GLOBALS['mysqli']->real_escape_string($company_1) . "',";
		if ($company_2)
			$add_field .= "firstname = '" . $GLOBALS['mysqli']->real_escape_string($company_2) . "',";
		if ($firstname)
			$add_field .= "firstname = '" . $GLOBALS['mysqli']->real_escape_string($firstname) . "',";
		if ($secondname)
			$add_field .= "secondname = '" . $GLOBALS['mysqli']->real_escape_string($secondname) . "',";
		if ($telefon)
			$add_field .= "secondname = '" . $GLOBALS['mysqli']->real_escape_string($telefon) . "',";
		if ($zip)
			$add_field .= "zip = '" . $GLOBALS['mysqli']->real_escape_string($zip) . "',";
		if ($city)
			$add_field .= "city = '" . $GLOBALS['mysqli']->real_escape_string($city) . "',";
		if ($commend)
			$add_field .= "commend = '" . $GLOBALS['mysqli']->real_escape_string($commend) . "',";
		if ($commend2)
			$add_field .= "commend2 = '" . $GLOBALS['mysqli']->real_escape_string($commend2) . "',";
		if ($country)
			$add_field .= "country = '" . $GLOBALS['mysqli']->real_escape_string($country) . "',";

		$GLOBALS['mysqli']->query("UPDATE $db.contact SET $add_field verify_key = '" . $GLOBALS['mysqli']->real_escape_string($verify_key) . "' WHERE contact_id ='$contact_id' ") or die(mysqli_error($GLOBALS['mysqli']));
	}

	/**
	 * *******************************************************************************************************
	 * Zuweisung der Tags und anschließendes Versenden der Mail zur Bestätigung der Zuweisung der Email
	 * *******************************************************************************************************
	 */

	if (is_array($array_tag_id)) {

		// Prüfen ob Tag bereits dem contact zugewiesen worden ist
		foreach ($array_tag_id as $key => $tag_id) {

			$query = $GLOBALS['mysqli']->query("SELECT tag_id, activate from $db.contact2tag WHERE contact_id = '$contact_id' and tag_id = '$tag_id' ") or die(mysqli_error($GLOBALS['mysqli']));
			$array_check_tag_exists = mysqli_fetch_array($query);

			if ($array_check_tag_exists['activate'] == '0') {
				$count_exists_inactive++;
			} else {
				$count_exists_active++;
			}
			// Wenn Tag noch nicht existiert wird dieser zugewiesen
			if (!$array_check_tag_exists['tag_id']) {
				$count_set_new_tag++;
				$GLOBALS['mysqli']->query("INSERT into $db.contact2tag SET
			tag_id   = '$tag_id',
			contact_id = '$contact_id',
			activate   = '0',
			verify_key = '$verify_key'
		") or die(mysqli_error($GLOBALS['mysqli']));
			}
		}

		// 	$GLOBALS ['array'] = $newsletter_client;
		// 	$text_user_exists_active = preg_replace_callback ( '!{%(.*?)%}!', function ($matches) { $array = $GLOBALS ['array']; return $array [$matches [1]]; }, $text_user_exists_active );
		// 	$text_user_exists_inactive = preg_replace_callback ( '!{%(.*?)%}!', function ($matches) { $array = $GLOBALS ['array']; return $array [$matches [1]]; }, $text_user_exists_inactive );
		// 	$text_user_exists_set_inactive = preg_replace_callback ( '!{%(.*?)%}!', function ($matches) { $array = $GLOBALS ['array']; return $array [$matches [1]]; }, $text_user_exists_set_inactive );

		// Wenn User bereits angemeldet aber inaktiv gesetzt ist
		if ($contact_activate == 0 && !$count_set_new_tag) {
			echo "$('#context_form$layer_id,#newsletter_content$layer_id').append(\"<div align=center>$text_user_exists_set_inactive</div>\");";
			exit();
		} // Wenn tags vorhanden sind aber noch nicht aktiviert wurden
		elseif ($count_exists_inactive > 0 && !$count_set_new_tag) {
			echo "$('#context_form$layer_id,#newsletter_content$layer_id').append(\"<div align=center>$text_user_exists_inactive</div>\");";
			exit();
		} // Wenn tag(s) vorhanden und bereits aktiviert worden sind
		elseif ($count_exists_active > 0 && !$count_set_new_tag) {
			echo "$('#context_form$layer_id,#newsletter_content$layer_id').append(\"<div align=center>$text_user_exists_active</div>\");";
			exit();
		}

		if ($emailtitle_reg)
			$subject = $emailtitle_reg;
		if ($emailtext_reg) {
			$set_message = $emailtext_reg;
		}

		// $message = send_mail ( $form_id, $email_to, $subject, $set_message );
		$message = send_mail($camp_array['from_id'], $email_to, $subject, $set_message);

		// Followup-Sequenz durchforsten ob Folgemails für User anstehen

		// $message = 'ok'; // zum testen
	}
}

/**
 * **************************************************************************
 * Erweiternung am 30.07.2019
 * Prüfen ob Tag in Formular-Form bereits dem contact zugewiesen worden ist
 * Dieser wird automatisch "activate = true"
 * **************************************************************************
 */
if ($array_formular_tag_id) {
	foreach ($array_formular_tag_id as $key => $tag_id) {
		$query = $GLOBALS['mysqli']->query("SELECT tag_id, activate from $db.contact2tag WHERE contact_id = '$contact_id' and tag_id = '$tag_id' ") or die(mysqli_error($GLOBALS['mysqli']));
		$array_check_tag_exists = mysqli_fetch_array($query);

		// Wenn Tag noch nicht existiert wird dieser zugewiesen
		if (!$array_check_tag_exists['tag_id']) {

			$count_set_new_tag++;
			$GLOBALS['mysqli']->query("INSERT into $db.contact2tag SET
			tag_id   = '$tag_id',
			contact_id = '$contact_id',
			activate   = '1',
			verify_key = '$verify_key'
		") or die(mysqli_error($GLOBALS['mysqli']));
		}
	}
}
