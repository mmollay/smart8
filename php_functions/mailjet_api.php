<?php

/**
 * MailJet API test
 *
 */
 

/**
 * MailJet API client class - calls the MailJet REST API to send an e-mail.
 * Designed to be widely compatible with PHPMailer.
 */
class MailJetMailer {
	
	/* Private */
	private $myMail;
	private $myHtml;
	
	private function addRecipient($addr, $name, $to) {
		if ($name != "") {
			$addr = "$name <$addr>";
		}
		
		if (array_key_exists($to, $this->myMail)) {
			$this->myMail[$to] = $this->myMail[$to] . ", $addr";
		} else {
			$this->myMail[$to] = $addr;
		}
	}
	
	/* Public */
	public $Host;			// Dummy - ignored
	public $SMTPAuth;		// Dummy - ignored
	public $SMTPDebug;		// Dummy - ignored
	public $SMTPSecure;		// Dummy - ignored
	public $Username;		// Mapped to MailJet API-Key (public)
	public $Password;		// Mapped to MailJet API-Key (private)
	public $Port;			// Dummy - ignored
	
	public $Subject;		// The Mail Subject
	public $Body;			// The Mail Body
	public $AltBody;		// Dummy - ignored
	
	public $ErrorInfo;		// Description of last error
	public $Sent;			// Recipients and the according mail IDs (in case of success)
	
	
	public function __construct() {
		$this->clear();
	}
	
	public function clear() {
		$this->myMail = array();
		$this->myMail['Attachments'] = array();
		$this->myHtml = false;
		
		$this->Host = "";
		$this->SMTPAuth = false;
		$this->SMTPSecure = "";
		$this->SMTPDebug = 0;
		$this->Username = "";
		$this->Password = "";
		$this->Port = 0;
		$this->Subject = "";
		$this->Body = "";
		$this->AltBody = "";
		
		$this->ErrorInfo = "";
		$this->Sent = array();
	}
	
	public function setFrom($from, $name = "") {
		$this->myMail['FromEmail'] = $from;
		if ($name != "") {
			$this->myMail['FromName'] = $name;
		}
	}
	
	public function addAddress($addr, $name = "") {
		$this->addRecipient($addr, $name, 'To');
	}
	
	public function addCC($addr, $name = "") {
		$this->addRecipient($addr, $name, 'Cc');
	}
	public function addBCC($addr, $name = "") {
		$this->addRecipient($addr, $name, 'Bcc');
	}
	
	public function addReplyTo($addr, $name = "") {
		if ($name != "") {
			$addr = "$name <$addr>";
		}
		if (! array_key_exists('Headers', $this->myMail)) {
			$this->myMail['Headers'] = array();
		}
		$this->myMail['Headers']['Reply-To'] = $addr;
	}
	
	public function addAttachment($file, $name = "") {
		$data = file_get_contents($file);
		if ($name == "") {
			$name = basename($file);
		}
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $file);
		finfo_close($finfo);
		
		$att = array(
			'Content-type' => $mime,
			'Filename' => $name,
			'content' => base64_encode($data)
		);
		
		array_push($this->myMail['Attachments'], $att);
	}
	
	public function isHTML($html = true) {
		$this->myHtml = $html;
	}
	
	public function isSMTP() {
		/* DUMMY */
	}
	
	public function send() {
		/* Fill in subject and body */
		$this->myMail['Subject'] = $this->Subject;
		if ($this->myHtml) {
			$this->myMail['Html-part'] = $this->Body;
		} else {
			$this->myMail['Text-part'] = $this->Body;
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3/send");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_USERPWD, "$this->Username:$this->Password");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->myMail));
		
		$result = curl_exec($ch);
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if ($status_code != 200) {
			//$this->ErrorInfo = $status_code + ": " + $result;
		    $this->ErrorInfo = $status_code;
			return false;
		} else {
			$json = json_decode($result);
			$this->Sent = $json->Sent;
			return true;
		}
	}
	
	function getMailInfo($id) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3/REST/message/$id");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_USERPWD, "$this->Username:$this->Password");
		
		$result = curl_exec($ch);
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if ($status_code != 200) {
			return array();
		} else {
			$json = json_decode($result);
			return $json->Data;
		}
	}
	
}

 
/*** MAIN ***/

// $mail = new MailJetMailer();

// $mail->Username = '452e5eca1f98da426a9a3542d1726c96';		// MailJet Public key
// $mail->Password = '55b277cd54eaa3f1d8188fdc76e06535';		// MailJet Private key

// $mail->setFrom('newsletter@ssi.at', 'SSI Newsletter');		// Set the sender
// $mail->addAddress('bk@ssi.at', 'Bert Klauninger');			// Add a recipient
// $mail->addAddress('dengibzned@xxx.at', 'Franz Sauschedl');
// //$mail->addAddress('ellen@example.com');					// Name is optional
// //$mail->addReplyTo('info@example.com', 'Information');
// //$mail->addCC('cc@example.com');
// //$mail->addBCC('bcc@example.com');

// //$mail->addAttachment('/var/tmp/file.tar.gz');				// Add attachments
// //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');		// Optional name

// $mail->addAttachment('./logoacidat.png');
// $mail->isHTML(true);										// Set email format to HTML

// $mail->Subject = 'Test Newsletter';
// $mail->Body    = 'Test 2';

// if(! $mail->send()) {
//     echo("Message could not be sent. Mailer Error: $mail->ErrorInfo\n");
// } else {
//     echo("Message has been sent to the following recipients:\n" . json_encode($mail->Sent) . "\n");
// }

/* MailInfo Test: */
//$status = $mail->getMailInfo("19140423357568413");
//echo("Status Recipient 1: " . json_encode($status) . "\n");

//$status = $mail->getMailInfo("19140423357652414");
//echo("Status Recipient 2: " . json_encode($status) . "\n");



?>
