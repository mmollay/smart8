<?
$delentry = $_REQUEST ['delentry'];
if ($_REQUEST ['user_id'])
	$user_id = $_REQUEST ['user_id'];
$guestbook_id = $_REQUEST ['guestbook_id'];

include ("gb_config.php");

// löscht Eintrag
if ($delentry != '') {
	cutline ( $data_file, $delentry );
}
function scheck($value) {
	$value = strip_tags ( $value );
	$value = stripslashes ( $value );
	$value = trim ( $value );
	
	return $value;
}
function cutline($filename, $line_no = -1) {
	$strip_return = FALSE;
	$data = file ( $filename );
	$pipe = fopen ( $filename, 'w' );
	$size = count ( $data );
	
	if ($line_no == - 1)
		$skip = $size - 1;
	else
		$skip = $line_no - 1;
	
	for($line = 0; $line < $size; $line ++)
		
		if ($line != $skip) {
			flock ( $pipe, LOCK_EX );
			fputs ( $pipe, $data [$line] );
			flock ( $pipe, LOCK_UN );
		} else {
			$strip_return = TRUE;
		}
	
	fclose ( $pipe );
	return $strip_return;
}

$page = $_REQUEST ['page'];
if (! (file_exists ( $data_file ))) {	
	@fopen ( $data_file, "w" ) or die ( "<div class='ui message'><div align=center>Bitte Gästbuch definieren! <br>$data_file</div></div>" );
	$data = file ( $data_file );
} else {
	fopen ( $data_file, "r" );
	$data = file ( $data_file );
}

$count = count ( $data );

$total_pages = ceil ( $count / $messages_per_page );

//echo ("$la2 $count $la3. <a class='button icon ui' href=\"javascript:showdiv('signform');\"><i class='icon write'></i> $la1</a><br><br>");
echo ("<a class='button icon ui' href=\"javascript:showdiv('signform');\"><i class='icon write'></i> $la1</a> $la2 $count $la3<br>");



// Make pages
if (isset ( $page ) and $page > 0) {
	if ($page > $total_pages) {
		$page = $total_pages;
	} else {
		$begin = $page * $messages_per_page - $messages_per_page;
	}
} else {
	$page = 1;
	$begin = 0;
}

$next_page = $page + 1;
$prev_page = $page - 1;

if ($prev_page < 1) {
	$prev_page = 1;
}

if ($next_page > $total_pages) {
	$next_page = $total_pages;
}

if ($newest_on_top == 1)
	$data = array_reverse ( $data );

$entries = array_slice ( $data, $begin, $messages_per_page );

$cont1 = count ( $entries ) - 1;
$cont2 = 0;
$line_num = count ( $data ) - ($page * $messages_per_page) + $messages_per_page;

date_default_timezone_set ( 'Europe/Vienna' );

while ( $cont1 >= $cont2 ) {
	$entry = $entries [$cont2];
	$entry = explode ( "|", $entry );
	$name = $entry [0];
	$homepage = $entry [1];
	$email = $entry [2];
	$message = $entry [3];
	// $ip
	$stime = $entry [5];
	$browser = $entry [6];
	
	// $stime = date('m/d/y - g:i A', $stime);
	$stime = date ( 'd.m.Y', $stime );
	
	$message = wordwrap ( $message, $word_wrap, "<br>", true );
	
	// Smiley
	$message = str_replace ( ":p", " <img src=\"$httpd_path/images/s1.gif\" alt=\":p\" border=\"0\"> ", $message );
	$message = str_replace ( ":)", " <img src=\"$httpd_path/images/s2.gif\" alt=\":)\" border=\"0\"> ", $message );
	$message = str_replace ( ":a", " <img src=\"$httpd_path/images/s3.gif\" alt=\":a\" border=\"0\"> ", $message );
	$message = str_replace ( ":o", " <img src=\"$httpd_path/images/s4.gif\" alt=\":o\" border=\"0\"> ", $message );
	$message = str_replace ( ":s", " <img src=\"$httpd_path/images/s5.gif\" alt=\":s\" border=\"0\"> ", $message );
	$message = str_replace ( ":r", " <img src=\"$httpd_path/images/s6.gif\" alt=\":r\" border=\"0\"> ", $message );
	$message = str_replace ( ":v", " <img src=\"$httpd_path/images/s7.gif\" alt=\":v\" border=\"0\"> ", $message );
	$message = str_replace ( ":h", " <img src=\"$httpd_path/images/s8.gif\" alt=\":h\" border=\"0\"> ", $message );
	$message = str_replace ( ";)", " <img src=\"$httpd_path/images/s9.gif\" alt=\";)\" border=\"0\"> ", $message );
	$message = str_replace ( ":m", " <img src=\"$httpd_path/images/s10.gif\" alt=\":m\" border=\"0\"> ", $message );
	
	// Fix the "&" bug
	$message = str_replace ( "^amp^", "&", $message );
	$name = str_replace ( "^amp^", "&", $name );
	
	
	// URL
	if ($convert_link == 1)
		$message = str_replace ( "[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a href=\"\\0\" target=\"_blank\">\\0</a>", $message );
	
	if ($homepage != 'http://') {
		$vhomepage = "<a href=\"http://$homepage\" target=\"_blank\" title='$homepage' ><i class='icon grey home'></i></a>";
	} else {
		$vhomepage = "<i class='icon grey disabled home'></i>";
	}
	
	if ($email) {
		$email = str_replace ( '@', '[at]', $email );
		$vemail = "<a href=\"mailto:$email\" target=\"_blank\" title='$email'><i class='icon mail grey'></i></a>";
	} else {
		$vemail = "<i class='icon mail grey disabled'></i>";
	}
	
	echo "<div class='segment ui'>";
	
	echo ("<div class=\"guestbook_top\">");
	
	/* DELETE Fields */
	if ($_SESSION ['admin_modus'])
		echo ("<a href=\"JavaScript:ajax('POST','$httpd_path/gb_view.php?guestbook_id=$guestbook_id&user_id=$user_id&delentry=$line_num','page','1')\"><i class='icon red delete'></i></a>");
	
	echo "<div align=\"right\" style=\"float:right; padding:2px;\">$vhomepage $vemail</div>";
	
	echo "<i class='icon grey user'></i> <b>$name</b> <font size = 1>$la26 $stime </font></div><br>";
	echo "<div class=\"guestbook\" >";
	//echo "<a href=\"$httpd_path/gb_admin.php\"><img src=\"$httpd_path/images/ip.gif\" alt=\"$ip\" title=\"$la16\" border=\"0\"></a>";
	echo ("$message<br></div>");
	echo "</div>";
	$cont2 ++;
	$line_num --;
}

if ($count != 0) {
	echo ("$la15 ");
	
	// Print out page number
	
	if (($page - 10) <= 0)
		$rp = ($page - 11) * - 1;
	
	if (($total_pages - $page) < 10)
		$lp = 10 + ($total_pages - $page) * - 1;
	
	if ($page != 1 && $page > 11)
		echo ("<a href=\"JavaScript:ajax('POST','$httpd_path/gb_view.php?guestbook_id=$guestbook_id&user_id=$user_id','page','1')\">1</a> ... ");
	
	for($i = $page - (10 + $lp); $i < $page; $i ++) {
		
		if ($i < $page && $i > 0) {
			echo ("<a href=\"JavaScript:ajax('POST','$httpd_path/gb_view.php?guestbook_id=$guestbook_id&user_id=$user_id','page','$i')\">$i</a> ");
		}
	}
	
	for($i = $page; $i <= $page + 10 + $rp; $i ++) {
		if ($page == $i) {
			echo ("<b>$i</b> ");
		} else if ($i >= $page && $i <= $total_pages) {
			echo ("<a href=\"JavaScript:ajax('POST','$httpd_path/gb_view.php?guestbook_id=$guestbook_id&user_id=$user_id','page','$i')\">$i</a> ");
		}
	}
	
	if ($page != $total_pages && $page < $total_pages - 10)
		echo (" ... <a href=\"JavaScript:ajax('POST','$httpd_path/gb_view.php?guestbook_id=$guestbook_id&user_id=$user_id','page','$total_pages')\">$total_pages</a>");
}

?>