<?php

// Verbindung zu Email herstellen
date_default_timezone_set ( 'Europe/Belgrade' );

/*
 * Submit - Emails
 */

$mail = new PHPMailer ();
$mail->IsSMTP (); // telling the class to use SMTP
$mail->SMTPAuth = true; // enable SMTP authentication
$mail->CharSet = "UTF-8";
$mail->Host = $MailConfig ['smtp_host']; // SMTP server
$mail->Username = $MailConfig ['smtp_user']; // Username
$mail->Password = $MailConfig ['smtp_password']; // Password
$mail->SMTPSecure = $MailConfig ['smtp_secure']; // sets the prefix to the servier
$mail->SMTPDebug = 0; // enables SMTP debug information (for testing)
$mail->Port = $MailConfig ['smtp_port']; // set the SMTP port for the GMAIL server

// ReturnPath
$mail->Sender = $error_email;

$mail->SetFrom ( $MailConfig ['from_email'], $MailConfig ['from_email'] );
$mail->AddReplyTo ( $MailConfig ['return_path'], $MailConfig ['return_path'] );
$mail->AddAddress ( $MailConfig ['to_email'], $MailConfig ['to_email']);

$mail->Subject = $MailConfig ['subject'];
$mail->Body = $MailConfig ['body'];
// $mail->AltBody = "Hier kommt noch mehr text Möllay"; // optional, comment out and test

if (! $mail->Send ()) {
	$mail_info = "Verbindung fehl geschlagen<br>" . $mail->ErrorInfo;
} else {
	echo "ok";
}
?>