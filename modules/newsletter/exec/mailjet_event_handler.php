<?php
//muss noch auf die neue Datenbank angepasst werden

include (__DIR__ . '/../n_config.php');

/* Einstellungen */
$sql_host = $host;
$sql_user = $username;
$sql_pass = $password;
$sql_db = $dbname;

$db = mysqli_connect($sql_host, $sql_user, $sql_pass) or die("Could not connect to server $sql_host");
mysqli_select_db($db, $sql_db) or die("Could not select database $sql_db");

/**
 * * MAIN **
 */
// TESTEN
//log_result ( $db, 'sent', '', '42221317615906400', '' );
// exit;

/* Im HTTP Body ist ein JSON-Array von Objekten oder ein einzelnes Objekt */
$post_body = file_get_contents('php://input');
$json = json_decode($post_body);

// zum testen schreibt $post_body in txt
// $myfile = fopen ( "post.txt", "w" ) or die ( "Unable to open file!" );
// $txt = "John Doe\n";
// fwrite ( $myfile, $post_body );

// Datenbanken abrufen und event der MessageID zuweisen
foreach ($array_db_nl as $sql_db) {
	if (is_array($json)) {
		for ($i = 0; $i < count($json); ++$i) {
			log_result($db, $json[$i]->event, $json[$i]->time, $json[$i]->MessageID, $json[$i]->email);
		}
	} else if (is_object($json)) {
		log_result($db, $json->event, $json->time, $json->MessageID, $json->email);
	} else {
		echo ("Invalid response<br>");
	}
}

mysqli_close($db);

fwrite($myfile, '\n gespeichert');
fclose($myfile);

/**
 * Mail Event in die Datenbank schreiben
 */
function log_result($db, $event, $time, $message_id, $email)
{
	if ($message_id) {
		// mysqli_query($db, "INSERT INTO logfile(email, timestamp, MessageID, status)" .
		// " values(\"$email\", FROM_UNIXTIME($time), \"$message_id\", \"$event\")");
		mysqli_query($db, "INSERT INTO status_log SET event='$event', timestamp=NOW(), message_id='$message_id', email = '$email' ") or die(mysqli_error($db));

		// Geöffntet
		if ($event == 'open') {
			$add_mysql_event = "AND status !='click' ";
		}

		mysqli_query($db, "UPDATE logfile SET status='$event', timestamp=NOW() WHERE MessageID='$message_id' AND status != 'unsub' $add_mysql_event") or die(mysqli_error($db));
		mysqli_query($db, "UPDATE followup_mail_logfile SET status='$event', timestamp=NOW() WHERE MessageID='$message_id' AND status != 'unsub' $add_mysql_event") or die(mysqli_error($db));

		// Check ob in ssi_fakture eingesetzt wird
		//Default - SSI
		mysqli_query($db, "UPDATE ssi_faktura.logfile SET status='$event', time_stamp=NOW() WHERE MessageID='$message_id' AND status != 'unsub' $add_mysql_event") or die(mysqli_error($db));

		//Obststadt
		mysqli_query($db, "UPDATE ssi_faktura1287.logfile SET status='$event', time_stamp=NOW() WHERE MessageID='$message_id' AND status != 'unsub' $add_mysql_event") or die(mysqli_error($db));



		// User über mailjet abgemeldet
		if ($event == 'unsub') {
			$query = mysqli_query($db, "SELECT client_id,session_id FROM logfile WHERE MessageID='$message_id'") or die(mysqli_error($db));
			$array = mysqli_fetch_array($query);
			$session_id = $array['session_id'];
			$client_id = $array['client_id'];

			mysqli_query($db, "UPDATE contact SET activate  = 0  WHERE contact_id = '$client_id' ") or die(mysqli_error($db));

			// Eintrag in das Logfile
			mysqli_query($db, "INSERT INTO user_logfile SET
		contact_id='$client_id',
		session_id='$session_id' ,
		remote_ip ='{$_SERVER['REMOTE_ADDR']}',
		msg = 'unsub by mailjet' ,
		status_id = '3',
		modul='$modul',
		system='mailjet'
		") or die(mysqli_error($db));
		}
	}
}
?>