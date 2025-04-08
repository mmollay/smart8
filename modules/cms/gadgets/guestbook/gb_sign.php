<?

$guestbook_id = $_GET ['guestbook_id'];
$user_id = $_GET ['user_id'];

include ("gb_config.php");
$dosign = $_REQUEST ['dosign'];
$pcode = md5 ( $spam_protection_code );
$scheck = $pcode {0} . $pcode {2} . $pcode {4} . $pcode {1} . $pcode {5};

if ($dosign == $scheck) {
	
	$page = $_REQUEST ['page'];
	$name = $_POST ['name'];
	$homepage = $_POST ['homepage'];
	$email = $_POST ['email'];
	$message = $_POST ['message'];
	
	$ip = $_SERVER ['REMOTE_ADDR'];
	//$browser = getBrowser ( $_SERVER ['HTTP_USER_AGENT'] );
	//$ua=getBrowser();
	//$browser = $ua['name'] . " " . $ua['version'] . " on " .$ua['platform'] . " reports: <br >" . $ua['userAgent'];
	
	$stime = time ();
	
	if (trim ( $name ) && trim ( $message )) {
		
		if ($flood_protection != "0") {
			if ($_COOKIE ['signed'] == "yes") {
				echo ("<div class='message ui red'>$la21</div>");
				exit ();
			}
		}
		
		// Cut name's length
		$name = cut_str ( $name, 35 );
		
		// Check for valid email
		if (strpos ( $email, '@' ) === false) {
			$email = "";
		}
		
		if (! (file_exists ( $data_file ))) {
			fopen ( $data_file, "w" );
		}
		
		$message = scheck ( $message );
		$name = scheck ( trim ( $name ) );
		$email = scheck ( trim ( $email ) );
		$homepage = scheck ( trim ( $homepage ) );
		
		$message = preg_replace ( "/\r/", "", $message );
		$message = preg_replace ( "/\n/", "<br> ", $message );
		
		$new_entry = $name . "|" . $homepage . "|" . $email . "|" . $message . "|" . $ip . "|" . $stime . "|" . $browser . "\n";
		
		$data = fopen ( $data_file, "a+" );
		flock ( $data, LOCK_EX );
		fwrite ( $data, $new_entry );
		flock ( $data, LOCK_UN );
		fclose ( $data );
		
		// End writing entry
	} else {
		echo ("$la22");
	}
} else { // end dosign
	echo ("$la23");
}



function scheck($value) {
	global $bad_words;
	$value = strip_tags ( $value );
	$value = stripslashes ( $value );
	$value = preg_replace ( "/\|/", "", $value );
	
	if (trim ( $bad_words ) != '') {
		$filter = explode ( ',', $bad_words );
		foreach ( $filter as $badword ) {
			$value = preg_replace ( "/$badword/", " **** ", $value );
		}
	}
	
	return $value;
}

// Get Browser Type Function by Daniel.
function getBrowser($userAgent) {
	function getBrowser()
	{
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";
		
		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		}
		elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		}
		elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}
		
		// Next get the name of the useragent yes seperately and for good reason
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
		{
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		}
		elseif(preg_match('/Firefox/i',$u_agent))
		{
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		}
		elseif(preg_match('/OPR/i',$u_agent))
		{
			$bname = 'Opera';
			$ub = "Opera";
		}
		elseif(preg_match('/Chrome/i',$u_agent))
		{
			$bname = 'Google Chrome';
			$ub = "Chrome";
		}
		elseif(preg_match('/Safari/i',$u_agent))
		{
			$bname = 'Apple Safari';
			$ub = "Safari";
		}
		elseif(preg_match('/Netscape/i',$u_agent))
		{
			$bname = 'Netscape';
			$ub = "Netscape";
		}
		
		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
		')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}
		
		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version= $matches['version'][0];
			}
			else {
				$version= $matches['version'][1];
			}
		}
		else {
			$version= $matches['version'][0];
		}
		
		// check if we have a number
		if ($version==null || $version=="") {$version="?";}
		
		return array(
				'userAgent' => $u_agent,
				'name'      => $bname,
				'version'   => $version,
				'platform'  => $platform,
				'pattern'    => $pattern
		);
	} 
	return 'Unknown';
}

// Cut the length of a string
function cut_str($str, $length) {
	if (strlen ( $str ) > $length) {
		for($i = 0; $i < $length; $i ++) {
			$cut_name = $cut_name . $str [$i];
		}
		return $cut_name;
	} else {
		return $str;
	}
}

?>