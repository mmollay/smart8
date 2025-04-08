<?php
// eMail versandt - Verification
function send_verification($element, $MailConfig, $user_id)
{

	// $element [first_reg_success | verification ]

	/*
	 * mm@ssi.at am 17.09.2012
	 * Check SMTP-connect
	 * @test_smtp_server.php
	 */
	// require ('../login/config_mail.php');
	// require ('../login/config_main.inc.php');
	$query = $GLOBALS['mysqli']->query("SELECT * from ssi_company.user2company WHERE user_id = '$user_id' ") or die(mysqli_error($GLOBALS['mysqli']));
	$array = mysqli_fetch_array($query);
	//$array['firstname'] = $array['firstname'];
	//$array['secondname'] = $array['secondname'];
	$domain = $_SERVER['SERVER_NAME'];
	$verify_key = $array['verify_key'];
	$array['verify_key'] = "http://$domain?verify_key=" . $verify_key;
	$email = $array['user_name'];


	//Auslesen der Werte und in das Form übergeben;
	$array2 = call_company_option($_SESSION['smart_company_id'], array($element . "_title", $element . "_text", $smtp_email, $smtp_title));

	$title = $array2[$element . "_title"];
	$text = $array2[$element . "_text"];
	$from_email = $array2['smtp_email'];
	$from_name = $array2['smtp_title'];

	//$query2 = $GLOBALS['mysqli']->query ( "SELECT * from smart_information  WHERE element='$element' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	//$array2 = mysqli_fetch_array ( $query2 );

	// Basisdaten werden von confic_mail.php abgerufen
	// Spezifische Daten für Company von der Datenbank
	//$query_from = $GLOBALS['mysqli']->query ( "SELECT smtp_email,smtp_title from ssi_company.user2company a 
	//	LEFT JOIN ssi_company.tbl_company b ON a.company_id = b.company_id WHERE user_id = '$user_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	//$array_from = mysqli_fetch_array ( $query_from );

	// DEFAULT
	if (!$from_email)
		$from_email = 'response@ssi.at';
	if (!$from_name)
		$from_name = 'Nachricht';
	if (!$title) {
		$title = 'Anmeldebestätigung';
	}
	if (!$text) {
		$domain = $_SERVER['SERVER_NAME'];
		$text = "Link zum freischalten klicken: <a href='$verify_key' target='top'>$verify_key</a>";
	}

	//$subject = preg_replace ( '!{%(.*?)%}!e', '$array[ \1 ]', $title );
	$subject = preg_replace_callback('!{%(.*?)%}!', function ($matches) {
		global $array;
		return $array[$matches[1]];
	}, $title);
	//$text = preg_replace ( '!{%(.*?)%}!e', '$array[ \1 ]', $text );
	$text = preg_replace_callback('!{%(.*?)%}!', function ($matches) {
		global $array;
		return $array[$matches[1]];
	}, $text);
	$to_email = $email;
	$to_name = $email;

	// Remove Backspaces
	$subject = stripslashes($subject);
	$text = stripslashes($text);

	$MailConfig['from_email'] = $from_email; // ABSENDER Email
	$MailConfig['from_name'] = $from_name; // ABSENDER Name
	$MailConfig['to_email'] = $to_email; // AN Email
	$MailConfig['to_name'] = $to_name; // AN Name
	$MailConfig['subject'] = $subject; // Betreff
	$MailConfig['text'] = $text; // Text
	return smart_sendmail($MailConfig);
}

//Passwoert checker!
function pc_passwordcheck($user, $pass)
{
	$word_file = '/usr/share/dict/words';

	$lc_pass = strtolower($pass);
	// also check password with numbers or punctuation subbed for letters
	$denum_pass = strtr($lc_pass, '5301!', 'seoll');
	$lc_user = strtolower($user);

	// the password must be at least six characters
	if (strlen($pass) < 6) {
		return 'Das Passwort ist zu kurz (mind. 8 Zeichen verwenden)';
		// return 'The password is too short.';
	}

	// the password can't be the username (or reversed username)
	if (($lc_pass == $lc_user) || ($lc_pass == strrev($lc_user)) || ($denum_pass == $lc_user) || ($denum_pass == strrev($lc_user))) {
		return 'Das Passwort beinhaltet Teile vom Usernamen';
		// return 'The password is based on the username.';
	}

	// count how many lowercase, uppercase, and digits are in the password
	$uc = 0;
	$lc = 0;
	$num = 0;
	$other = 0;
	for ($i = 0, $j = strlen($pass); $i < $j; $i++) {
		$c = substr($pass, $i, 1);
		if (preg_match('/^[[:upper:]]$/', $c)) {
			$uc++;
		} elseif (preg_match('/^[[:lower:]]$/', $c)) {
			$lc++;
		} elseif (preg_match('/^[[:digit:]]$/', $c)) {
			$num++;
		} else {
			$other++;
		}
	}

	// the password must have more than two characters of at least
	// two different kinds
	$max = $j - 2;
	if ($uc > $max) {
		// return "The password has too many upper case characters.";
		return "Das Passwort hat zu viele Großbuchstaben.";
	}
	if ($lc > $max) {
		// return "The password has too many lower case characters.";
		return "Das Passwort hat zu viele Kleinbuchstaben.";
	}
	if ($num > $max) {
		// return "The password has too many numeral characters.";
		return "Das Passwort hat zu viele Ziffernzeichen.";
	}
	if ($other > $max) {
		// return "The password has too many special characters.";
		return "Das Passwort hat zu viele Sonderzeichen.";
	}

	// the password must not contain a dictionary word
// 	if (is_readable ( $word_file )) {
// 		if ($fh = fopen ( $word_file, 'r' )) {
// 			$found = false;
// 			while ( ! ($found || feof ( $fh )) ) {
// 				$word = preg_quote ( trim ( strtolower ( fgets ( $fh, 1024 ) ) ), '/' );
// 				if (preg_match ( "/$word/", $lc_pass ) || preg_match ( "/$word/", $denum_pass )) {
// 					$found = true;
// 				}
// 			}
// 			fclose ( $fh );
// 			if ($found) {
// 				// return 'The password is based on a dictionary word.';
// 				return 'Das Passwort basiert auf einem Wörterbuchwort.';
// 			}
// 		}
// 	}

	return false;
}