<?php
/**
 * Sendet Email über MailJet oder PHPMailer
 * UPDATE 10.12.2017  - CC & BCC
 * 
 $MailConfig['delivery_system'] = $delivery_system;  //mailjet oder phpmailer
 $MailConfig['smtp_user'] = $smtp_user; //USER
 $MailConfig['smtp_password'] = $smtp_password; //PASSWORD

 //Diese Daten werden bei Mailjet nicht verwendet 
 $MailConfig['smtp_auth'] = TRUE; (Wird automatisch gewählt wenn User und Passwort vorhanden sind)
 $MailConfig['smtp_secure'] = 'ssl | tls ';
 $MailConfig['smtp_port'] = '25|110|...';
 $MailConfig['smtp_host'] = 'xxxxxxxxxx';

 $MailConfig['from_email'] = $from_email; //ABSENDER Email
 $MailConfig['from_name'] = $from_name; //ABSENDER Name
 $MailConfig['relay_email'] = $relay_email; //ZURÜCK AN Email (Kann leer bleiben)
 $MailConfig['relay_name'] = $relay_name; //ZURÜCK AN Email  (Kann leer bleiben)
 $MailConfig['to_email'] = $to_email; //AN Email
 $MailConfig['to_name'] = $to_name; //AN Name

 $MailConfig['cc_email'] = 'martin@ssi.at';
 $MailConfig['cc_name'] = 'Martin Mollay';

 $MailConfig['bcc_email'] = 'martin@ssi.at';
 $MailConfig['bcc_name']  = 'Martin Mollay

 $MailConfig['subject'] = $subject; //Betreff
 $MailConfig['text'] = $text; //Text
 $MailConfig['path'] = "$upload_dir/$session_id";  

 $MailConfig['addAttachment'][] = "bild1.png";
 $MailConfig['addAttachment'][] = "bild2.png";

 //Output ARRAY OR Value

 //Mailjet + $get_messageID
 if (is_array($mail_result)) {
 $mail_info = $mail_result['mail_info'];
 $MessageID = $mail_result['MessageID']; 
 }
 else 
 $mail_info = $mail_result;

 $MailConfig ....ConfigParameter
 $get_messageID übergibt Send MessageID von Mailjet

 */
date_default_timezone_set('Europe/Belgrade');

function smart_sendmail($MailConfig, $get_messageID = false)
{

    // Ausgabe
    // echo "<pre>" . print_r ( $MailConfig ) . "</pre>"; exit ();
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        $MailConfig['delivery_system'] = 'phpmailer';
    } else if (! $MailConfig['delivery_system'])
        $MailConfig['delivery_system'] = 'mailjet';

    // MAILJET
    if ($MailConfig['delivery_system'] == 'mailjet') {
        require_once ('mailjet_api.php');
        $mail = new MailJetMailer();
        $mail->Username = $MailConfig['smtp_user'];
        $mail->Password = $MailConfig['smtp_password'];
        $mail->setFrom($MailConfig['from_email'], $MailConfig['from_name']);
        if ($MailConfig['replay_email'])
            $mail->addReplyTo($MailConfig['relay_email'], $MailConfig['relay_name']);
        $mail->addAddress($MailConfig['to_email'], $MailConfig['to_name']);
        // Versendet an CC
        if ($MailConfig['cc_email'])
            $mail->addCC($MailConfig['cc_email'], $MailConfig['cc_name']);
        // Versente an BCC
        if ($MailConfig['bcc_email'])
            $mail->addBCC($MailConfig['bcc_email'], $MailConfig['bcc_name']);
        // Attachments werde einzelnd übergeben
        if (is_array($MailConfig['addAttachment'])) {
            foreach ($MailConfig['addAttachment'] as $key => $value) {
                $mail->addAttachment($value);
            }
        }

        // Attachment wird aus Folder gelesen
        if (is_dir($MailConfig['path'])) {
            $handle = opendir($MailConfig['path']);
            while ($datei = readdir($handle)) {
                if ($datei != "." && $datei != ".." && $datei != "thumbnail") {
                    $mail->addAttachment($MailConfig['path'] . "/$datei");
                }
            }
            closedir($handle);
        }

        $mail->isHTML(true);
        $mail->Subject = $MailConfig['subject'];
        $mail->Body = $MailConfig['text'];

        if (! $mail->send()) {
            $mail_info = "Message could not be sent. Mailer Error: $mail->ErrorInfo\n";
        } else {
            $mail_info = 'ok';
            // echo("Message has been sent to the following recipients:\n" . json_encode($mail->Sent) . "\n");
            $MessageID = $mail->Sent[0]->MessageID;
        }
        if ($get_messageID)
            return array(
                'MessageID' => $MessageID,
                'mail_info' => $mail_info
            );
        else
            return $mail_info;
    } else {
        // PHPMAILER
        require_once ('phpmailer2/src/SMTP.php');
        require_once ('phpmailer2/src/PHPMailer.php');

        if ($MailConfig['smtp_user'] or $MailConfig['smtp_password']) {
            $MailConfig['smtp_auth'] = true;
        } else {
            // Ausser der Wert wurde manuell eingestellt
            if (! $MailConfig['smtp_auth'])
                $MailConfig['smtp_auth'] = false;
        }
        // error_reporting ( E_STRICT );
        $mail = new PHPMailer();
        $mail->SMTPDebug = 0;
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->SMTPAuth = $MailConfig['smtp_auth']; // enable SMTP authentication
        $mail->CharSet = "UTF-8";
        // $mail->Encoding = '8bit';
        $mail->Host = $MailConfig['smtp_host']; // SMTP server
        $mail->Username = $MailConfig['smtp_user']; // Username
        $mail->Password = $MailConfig['smtp_password']; // Password
        $mail->SMTPSecure = $MailConfig['smtp_secure']; // sets the prefix to the servier
        $mail->Port = $MailConfig['smtp_port']; // set the SMTP port for the GMAIL server

        // $mail->Sender = $MailConfig['from_email'];
        $mail->SetFrom($MailConfig['from_email'], $MailConfig['from_name']);
        if ($MailConfig['replay_email'])
            $mail->AddReplyTo($MailConfig['replay_email'], $MailConfig['replay_name']);
        $mail->AddAddress($MailConfig['to_email'], $MailConfig['to_name']);
        // Versendet an CC
        if ($MailConfig['cc_email'])
            $mail->addCC($MailConfig['cc_email'], $MailConfig['cc_name']);
        // Versente an BCC
        if ($MailConfig['bcc_email'])
            $mail->addBCC($MailConfig['bcc_email'], $MailConfig['bcc_name']);
        $mail->Subject = $MailConfig['subject'];
        // if ($modus == 'html') {
        $mail->ContentType = 'text/html; charset=utf-8\r\n';
        $mail->MsgHTML($MailConfig['text']);
        // } else {
        // $mail->Body = $MailConfig['text'];
        // }

        // Attachments werde einzelnd übergeben
        if (is_array($MailConfig['addAttachment'])) {
            foreach ($MailConfig['addAttachment'] as $key => $value) {
                $mail->AddAttachment($value);
            }
        }

        /**
         * ********************************************
         * Dateien einbinden in das Mail
         * ********************************************
         */
        if (is_dir($MailConfig['path'])) {
            $handle = opendir($MailConfig['path']);
            while ($datei = readdir($handle)) {
                if ($datei != "." && $datei != ".." && $datei != "thumbnail") {
                    $mail->AddAttachment($MailConfig['path'] . "/$datei");
                    // echo $MailConfig['path'] . "/$datei";
                }
            }
            closedir($handle);
        }

        if (! $mail->Send()) {
            $mail_info = $mail->ErrorInfo;
        } else {
            $mail_info = "ok";
        }
        return $mail_info;
    }
}