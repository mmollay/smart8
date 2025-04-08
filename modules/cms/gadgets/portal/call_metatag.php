<?php
include ('gadgets/bazar/mysql.php');

if ($_GET['code']) {
	$url = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$url2 = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'];

	$query = $GLOBALS['mysqli']->query ( "SELECT * FROM ssi_bazar.article WHERE article_id = '{$_GET['code']}' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$array = mysqli_fetch_array ( $query );
	$price = number_mysql2german ( $array['price'] );
	
	$image_url = "$url2/smart_users/ssi/user{$array['user_id']}/bazar/{$_GET['code']}/{$array['image']}";
	$title = str_replace ( "\n", "", $array['title'] );
	$title = trim ( $title )." | $price ";
	
	$text = str_replace ( "/\h\h+/", " &bull; ", $array['text_clear'] );
	//$text = substr($text,0,200)."Kosten: $price"; //max 200 Zeichen
	
	echo "<title>$title</title>";
	echo "\n<meta property='og:url' content='$url' />";
	echo "\n<meta property='og:type' content='website' />";
	echo "\n<meta property='og:title' content='$title' />";
	echo "\n<meta property='og:description' content='$text'/>";
	if ($array['image']) {
		echo "\n<meta property='og:image' content='$image_url'/>";
		echo "\n<link rel='image_src' type='image/jpeg' href='$image_url' />";
		echo "\n<meta property='og:image:width' content='605'/>";
		echo "\n<meta property='og:image:height' content='605'/>";
	}
	echo "\n<meta name='title'  content='$title' />";
	echo "\n<meta name='description' content='$text'>\n";
} else {
	
	function check_php_script($site_id) {
		global $company_id;
		// Check ob Bazar in Seite verwendet wird
		$sql = $GLOBALS['mysqli']->query ( "SELECT gadget FROM ssi_smart{$company_id}.smart_layer WHERE gadget = 'bazar' AND site_id = '$site_id' " );
		$set_bazar = mysqli_num_rows ( $sql );
		
		if ($set_bazar) {
			return '.php';
		} else
			return '.html';
	}
	
	// Ruft
	$query = $GLOBALS['mysqli']->query ( "SELECT * FROM ssi_smart{$company_id}.smart_langSite WHERE fk_id = '$site_id' AND lang = '$lang' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$array = mysqli_fetch_array ( $query );
	
	// Setzt die Endung des Skriptes html oder php
	$file_ending = check_php_script ( $site_id );
	
	if (! $array['site_url'])
		$array['site_url'] = $array['fk_id'];
	
	$site_url = $array['site_url'] . "$file_ending";
	$company = $_SESSION['company'];
	$title = $array['title'];
	
	$meta_title = $array['meta_title'];
	$meta_text = $array['meta_text'];
	$meta_keywords = $array['metal_keywords'];
	$meta_author = $array['meta_author'];
	$fb_title = $array['fb_title'];
	$fb_text = $array['fb_text'];
	$fb_image = $array['fb_image'];
	
	$metatag = "\n<title>$title</title>";
	$metatag .= "\n<meta property='og:type' content='website' />";
	$metatag .= "\n<meta property='og:url' content='$site_url' />";
	
	if (! $fb_title and $title)
		$fb_title = $title;
	
	if ($fb_title)
		$metatag .= "\n<meta property='og:title' content='$fb_title' />";
	if ($fb_text)
		$metatag .= "\n<meta property='og:description' content='$fb_text'/>";
	if ($fb_image) {
		$metatag .= "\n<meta property='og:image' content='$fb_image' />";
		$metatag .= "\n<link rel='image_src' type='image/jpeg' href='$fb_image' />";
	}
	if ($meta_keywords)
		$metatag .= "\n<meta name='keywords' content='$meta_keywords'>";
	if ($meta_text)
		$metatag .= "\n<meta name='description' content='$meta_text'>";
	if ($meta_author)
		$metatag .= "\n<meta name='author' content='$meta_author'>";
	
	echo $metatag;
}