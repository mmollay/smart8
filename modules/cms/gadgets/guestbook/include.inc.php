<?php
if (!$guestbook_id && $_SESSION['admin_modus']) {
	
	// Prüfen ob Buttons bereits vorhanden sind und legt gegebenenfalls gleich einen Button an
	$query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_gadget_guestbook WHERE page_id = '{$_SESSION['smart_page_id']}' " );
	$array = mysqli_fetch_array ( $query );
	$guestbook_id = $array['guestbook_id']; 
	
	if (!$guestbook_id) {
		$GLOBALS['mysqli']->query ( "INSERT INTO smart_gadget_guestbook SET
			page_id      = '{$_SESSION['smart_page_id']}',
			title        = 'Guestbook'
		" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		$guestbook_id = mysqli_insert_id($GLOBALS['mysqli']);
	}
}

if (! $guestbook_id) {
	$output = "<div class='ui message'><div align=center>Bitte Gästbuch definieren!</div></div>";
	return;
}

include ("gb_config.php");

// Check for banned IP
if (! (file_exists ( $ip_file ))) {
	@fopen ( $ip_file, "w" ) or $error = 1;
	if ($error == 1) {} // echo"Can't open the file $ip_file";
else
		$data = file ( $ip_file );
} else {
	fopen ( $ip_file, "r" );
	$data = file ( $ip_file );
}
$userIP = $_SERVER['REMOTE_ADDR'];

if (is_array($data)) {
    for($i = 0; $i < sizeof ( $data ); $i ++) {
    	$bannedIP = trim ( $data[$i] );
    	if ($bannedIP == $userIP) {
    		echo ("<h3>$la34</h3>");
    		return 0;
    	}
    } // end for
}

$add_css .= "\n\t<link rel=stylesheet type='text/css' href='$httpd_path/gb_style.css' media='screen' />";

$token = md5 ( uniqid ( rand (), true ) );
$pcode = md5 ( $spam_protection_code );
if ($disable_gb == "1") {
	$ausgabe .= ("<center><br><br><br><br><br><br><b>$la33</b><br><br><br><br><br><br></center>");
	exit ();
}

include_once (__DIR__ . "/../../smart_form/include_form.php");

$smily_gallery = "
<a href=\"javascript:smiley(' :p ');\"><img src='$httpd_path/images/s1.gif' alt=':p' border='0'></a> 
<a href=\"javascript:smiley(' :) ');\"><img src='$httpd_path/images/s2.gif' alt=':)' border='0'></a> 
<a href=\"javascript:smiley(' :a ');\"><img src='$httpd_path/images/s3.gif' alt=':a' border='0'></a> 
<a href=\"javascript:smiley(' :s ');\"><img src='$httpd_path/images/s5.gif' alt=':s' border='0'></a> 
<a href=\"javascript:smiley(' :r ');\"><img src='$httpd_path/images/s6.gif' alt=':r' border='0'></a> 
<a href=\"javascript:smiley(' :v ');\"><img src='$httpd_path/images/s7.gif' alt=':v' border='0'></a> 
<a href=\"javascript:smiley(' :h ');\"><img src='$httpd_path/images/s8.gif' alt=':h' border='0'></a> 
<a href=\"javascript:smiley(' ;) ');\"><img src='$httpd_path/images/s9.gif' alt=';)' border='0'></a> 
<a href=\"javascript:smiley(' :m ');\"><img src='$httpd_path/images/s10.gif' alt=':m' border='0'></a>
<a href=\"javascript:smiley(' :o ');\"><img src='$httpd_path/images/s4.gif' alt=':o' border='0'></a>
";

$user_id = $_SESSION['user_id'];

$arr['form'] = array ( 'id' => 'signgb' , 'action' => false );
$arr['ajax'] = array ( 'success' => "ajax('POST','$httpd_path/gb_sign.php?guestbook_id=$guestbook_id&user_id=$user_id','action','signgb');" , 'beforeSend' => '' , 'datatype' => 'html' );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'two fields' ); // 'label'=>'test'
$arr['field']['name'] = array ( 'label' => 'Name' , 'type' => 'input' , 'placeholder' => 'Name' ,  'validate' => true );
$arr['field']['email'] = array ( 'label' => 'Email' , 'type' => 'input' , 'placeholder' => 'Email' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field']['homepage'] = array ( 'label_left' => 'http://' , 'label' => 'Homepage' , 'type' => 'input' , 'placeholder' => 'Webseite' );
$arr['field']['message'] = array ( 'label' => 'Nachricht' , 'type' => 'textarea' , 'cols' => 10 ,  'validate' => true );
$arr['field']['smily'] = array ( 'type' => 'content' ,  'text' => $smily_gallery );

$arr['hidden']['guestbook_id'] = $guestbook_id;
$arr['hidden']['user_id'] = $user_id;

$arr['buttons'] = array ( 'align' => 'center' );
$arr['button']['submit'] = array ( 'value' => 'Eintragen' , 'color' => 'green' );
$arr['button']['reset'] = array ( 'value' => 'Zurücksetzen' ,  'js' => "$('#name').focus();" );
$arr['button']['close'] = array ( 'value' => 'Schließen' ,  'js' => "$('#entries').show(); $('#signform').hide();" );

$output_form = call_form ( $arr );

$add_js2 = $output_form['js'];

$output = "
<script language='javascript' type='text/javascript'>
var loadtext   = '$la32';
var c_minute   = $flood_protection;
var httpd_path = '$httpd_path';
</script>
<script language='javascript' src='$httpd_path/gb_ajax.js' type='text/javascript'></script>

<div id=set_guestbook></div>
<div class='container'>

<div id='signform' style='display:none;'>
	{$output_form['html']}
	<br><div id='signdiv'></div>
</div>

<div id='entries'></div>

<script language='Javascript' type='text/javascript'>
var pcode = '$pcode';
ajax('POST','$httpd_path/gb_view.php?guestbook_id=$guestbook_id&user_id=$user_id','page','1','$token');
</script>
</div>
";

?>