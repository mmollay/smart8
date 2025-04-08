<?
header('Content-Type: text/html; charset=UTF-8');
session_start();
if (ob_get_level() == 0)
	ob_start();
// $file_ending = '.html';
// $file_ending = '.php';
/*
 * Generiert die Webseite
 * UPDATE 15-04-2017 MM
 */

if ($array_rm_all['set_update'])
	flush_text("Starte mit Erzeugung der Webseite", '1');
else
	flush_text("Starte dem Update der Webseite", '1');

$page_id = $_SESSION['smart_page_id'];

include_once('../../../login/config_main.inc.php');
// wird für den Shop verwendet
$company_id = $_SESSION['cart_company_id'];
$domain = $_SERVER['SERVER_NAME'];

include_once(__DIR__ . '/../../gadgets/function.inc.php');

//Hole page generate
if ($_POST['upload_hole_page'] == 'true') {
	set_update_site('all');
}

// include (__DIR__ . '/../inc/function_generate_dirstructure.php');
// include (__DIR__ . '/../../library/function_menu.php');

// Erzeugen einer htaccess-Structure - Zusätzlich zum title.hmtl /title/ - Bei Substructure /produkte/produkt/
// $menuData = generateMenuStructure ( $page_id, true );
// generate_dir_sturcture ( 0, $menuData );

// $content = "href=\"#\" onclick=\"CallContentSite('31')\"";
// $content = "<hr />Hole dir die neuesten Infos &uuml;ber die Angebote von <a href=# onclick=\"CallContentSite('268')\">SSI</a>.";
// $content = "<a class=\"button icon ui teal icon \" href=\"#\" onclick=\"CallContentSite('31')\">";
// // //$content = preg_replace_callback ( "/\?href/", change_link, $content );
// // //$content = preg_replace_callback ( "/\?site_select=(\w+)/", change_link, $content );
// $content = preg_replace_callback ( "/\"\#\" onclick=\"CallContentSite\(\'(\w+)\'\)\"/", change_link, $content );
// echo "$content";
// exit;

// Domainnamen auslesen

/* todo:Domainnamen aus der company_Liste auslesen !!! */

// Abrufen der Optionen (alt)
$query1 = $GLOBALS['mysqli']->query("SELECT * FROM smart_page WHERE page_id = '$page_id' ");
$array1 = mysqli_fetch_array($query1);
$domain_xml = $array1['smart_domain'];
$site_key = $array1['site_key'];
$secret_key = $array1['secret_key'];
$user_id = $array1['user_id'];
$set_ssl = $array1['set_ssl'];

// Abrufen der Optionen (neu)
call_smart_option($page_id, '', '', true);

// Nur nehmen wenn in den globalen Optionen noch nicht vorhanden ist
if (!$index_id)
	$index_id = $array1['index_id'];

$path_id_user = "../../../.." . $_SESSION['path_user'] . "user$user_id";
$path_id_path = "$path_id_user/page$page_id";
$path_id_compress_path = "$path_id_user/compress/$page_id";
$path_id_path_save = "$path_id_user/page$page_id" . "_save";

$user_path = "{$_SESSION['path_user']}user$user_id/explorer/$page_id";
$user_path_compress = "..{$_SESSION['path_user']}user$user_id/compress/$page_id/";
$change['pattern'] = array("[\.\./\.\.$user_path]", "[\.\.$user_path]", "[$user_path]");
$change['replace'] = 'explorer';

// Erzeugt Folder wenn diese noch nicht vorhanden sind

// exec ( "rm -rf $path_id_path " );
// Sicherung anlegen
// exec ( "mv $path_id_path $path_id_path_save" );

$query_rm_all = $GLOBALS['mysqli']->query("SELECT set_update FROM smart_page WHERE set_update = 1 AND page_id = '$page_id' ");
$array_rm_all = mysqli_fetch_array($query_rm_all);

// Soll nur aufgerufen werden wenn die alle Seite neu überschrieben werden soll
if ($array_rm_all['set_update']) {
	exec("rm $path_id_path/*"); // Löscht alle Seiten
	exec("rm $path_id_compress_path/*"); // Läscht alles Compress
}

// Anlegen der Folder als noch nicht vorhanden
exec("mkdir ../$path_user");
exec("mkdir $path_id_user "); // Generater new userID
exec("mkdir $path_id_path "); // Generate a new pageID
exec("mkdir $path_id_path/explorer"); // Explorer - Folder erzeugen falls noch nicht vorhanden ist

// if (!is_dir("$path_id_user/compress")) mkdir ("$path_id_user/compress");

// Value for static-using (Bsp. für Link für Bilder bei Shop (siehe portal/cart/call_main.php
// exec ( "cp ../../config_public_copy2static.php $path_id_path/config_public.php" );

// $default_config = file_get_contents ( "$path_id_path/config_public.php" );
// $default_config = preg_replace ( "/set_user_id/", $user_id, $default_config );
// $default_config = preg_replace ( "/set_page_id/", $page_id, $default_config );
// $default_config = preg_replace ( "/set_secret_key/", $secret_key, $default_config ); // recaptcha
// $default_config = preg_replace ( "/set_site_key/", $site_key, $default_config ); // recaptcha
// $default_config = preg_replace ( "/set_company_id/", $company_id, $default_config );
// $default_config = preg_replace ( "/session_start\(\);/", "", $default_config );
// file_put_contents ( "$path_id_path/config_public.php", $default_config );

// Copy von Files
// $copy_array[] = "../../css";$copy_array[] = "../../js ";

if (!$index_off)
	$copy_array[] = "../../robots.txt";
else {
	exec("rm $path_id_path/robots.txt"); // Löscht alle Seiten
}

$copy_array[] = "../../js";
$copy_array[] = "../../set_client.php";
$copy_array[] = "../../smart_form";
$copy_array[] = "../../gadgets";
$copy_array[] = "../../php_functions";

foreach ($copy_array as $value) {
	exec("rsync -avz $value $path_id_path/ -rogpq --delete");
}


//Wurde ersetzt durch einen direkten Link damit die Einstellung nur über eine Config erfolgen
// $MailConfig[\'smtp_host\'] =  $MailConfig[\'smtp_server\'] = "' . $_SESSION['MailConfig']['smtp_host'] . '";
// $MailConfig[\'smtp_user\'] = "' . $_SESSION['MailConfig']['smtp_user'] . '";
// $MailConfig[\'smtp_password\'] = "' . $MailConfig['smtp_password'] . '";
// $MailConfig[\'smtp_port\'] = "' . $_SESSION['MailConfig']['smtp_port'] . '";
// $MailConfig[\'smtp_secure\'] = "' . $_SESSION['MailConfig']['smtp_secure'] . '";
// $MailConfig[\'mailjet_smtp_user\'] = "' . $_SESSION['MailConfig']['mailjet_smtp_user'] . '";
// $MailConfig[\'mailjet_smtp_password\'] = "' . $MailConfig['mailjet_smtp_password'] . '";

// Config-Daten beim erzeugen Webseite
// !!Achten, dass keine Zeilenumbrüche mit erzeugt werden (Formularübergabewert stimmt sonst nicht 04-03-2017)
$config_smart = '<?
//Upate 17.02.2025
@session_start();
$MailConfig[\'return_path\'] = $MailConfig[\'error_email\'] = "' . $_SESSION['MailConfig']['return_path'] . '";
$MailConfig[\'from_email\'] = "' . $_SESSION['MailConfig']['from_email'] . '";
$MailConfig[\'from_title\'] = "' . $_SESSION['MailConfig']['from_title'] . '";

include (__DIR__.\'/../../../../../smart7/login/config_mail.php\');

$cfg_mysql[\'user\'] = "' . $_SESSION['mysql']['user'] . '";
$cfg_mysql[\'password\'] = "' . $_SESSION['mysql']['password'] . '";
$cfg_mysql[\'server\'] = "' . $_SESSION['mysql']['server'] . '";
$cfg_mysql[\'db\'] = "' . $_SESSION['mysql']['db'] . '";
$cfg_mysql[\'db_nl\'] = "' . $_SESSION['mysql']['db_nl'] . '";
$cfg_mysql[\'db_map\'] = "' . $_SESSION['mysql']['db_map'] . '";
$cfg_mysql[\'db_bazar\'] =  "' . $_SESSION['mysql']['db_bazar'] . '";
$cfg_mysql[\'db_21\'] = "' . $_SESSION['mysql']['db_21'] . '";
$cfg_mysql[\'db_learning\'] = "' . $_SESSION['mysql']['db_learning'] . '";
$cfg_mysql[\'db_faktura\'] = "' . $_SESSION['mysql']['db_faktura'] . '";
$_SESSION[\'admin_modus\'] = \'\';
$admin_modus = \'\';
		
$smart_company_id = "' . $_SESSION['smart_company_id'] . '";
$company = "' . $_SESSION['company'] . '";
$smart_user_id = "' . $_SESSION['user_id'] . '";
$site_key = "' . $site_key . '";
$secret_key = "' . $secret_key . '";
$user_id = "' . $_SESSION['user_id'] . '";
$page_id = "' . $_SESSION['smart_page_id'] .
	'";
		
if ($_SESSION[\'client_token\']) {
	$client_token = $_SESSION[\'client_token\'];
}
elseif ($_COOKIE[\'client_verify_key\']) {
	$client_token = $_SESSION[\'client_token\'];
}
		
$GLOBALS[\'mysqli\'] = mysqli_connect ( $cfg_mysql[\'server\'], $cfg_mysql[\'user\'], $cfg_mysql[\'password\'],$cfg_mysql[\'db\'] ) or die ( \'Could not open connection to server\' );
		
$TEMPLATES_INTRO2 = array ( \'m\' => \'Lieber\' , \'f\' => \'Liebe\' , \'c\' => \'Liebe Firma\',\'e\'=>\'Hallo\' );
$TEMPLATES_INTRO3 = array ( \'m\' => \'Sehr geehrter Herr\' , \'f\' => \'Sehr geehrte Frau\' , \'c\' => \'Sehr geehrte Firma\', \'e\' => \'Sehr geehrte Damen und Herren\');
?>';

f_copy_file("$path_id_path/gadgets/config.php", $config_smart);

// function kopiert Inhalte in eine Datei
function f_copy_file($pfad, $inhalt)
{
	exec("touch $pfad");
	$fp = fopen($pfad, "w+");
	@fwrite($fp, $inhalt);
	@fclose($fp);
}

// Alle Seiten
// $query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_langSite INNER JOIN smart_id_site2id_page ON site_id = fk_id WHERE page_id = '$page_id' AND lang = '{$_SESSION['page_lang']}' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
// Nur Seiten welche verändert wurden
$query = $GLOBALS['mysqli']->query("SELECT * FROM smart_langSite INNER JOIN smart_id_site2id_page ON site_id = fk_id WHERE page_id = '$page_id' AND set_update = 1 AND lang = '{$_SESSION['page_lang']}' ") or die(mysqli_error($GLOBALS['mysqli']));
while ($array = mysqli_fetch_array($query)) {

	$site_id = $array['site_id'];

	// Setzt die Endung des Skriptes html oder php
	$file_ending = check_php_script($site_id);

	if (!$array['site_url'])
		$array['site_url'] = $array['fk_id'];

	$site_url = $array['site_url'] . "$file_ending";

	flush_text("Erzeuge: $site_url");

	$company = $_SESSION['company'];
	$title = $array['title'];
	$meta_title = $array['meta_title'];
	$meta_text = $array['meta_text'];
	$meta_keywords = $array['metal_keywords'];
	$meta_author = $array['meta_author'];
	$fb_title = $array['fb_title'];
	$fb_text = $array['fb_text'];
	$fb_image = $array['fb_image'];

	// Wandelt die Domain um damit Webseiten ohne HTTPS ausgeselesen werden - für moderne Server
	// $domain = preg_replace("[center.]","webcenter.",$domain);

	// Liest den Pfad aus für das erzeugen der Seite(n)
	$SitePathArray = explode("ssi_smart", $_SERVER['HTTP_REFERER']);

	// $site_pfad = "http://$domain/ssi_smart/index.php?site_id=$site_id&public=true&company=$company";
	$site_pfad = $SitePathArray['0'] . "ssi_smart/index.php?site_id=$site_id&public=true&company=$company";

	// echo $site_pfad;
	// exit;
	if ($index_id == $site_id) {
		$save_path = "$path_id_path/" . "index$file_ending";
		$site_url = "index$file_ending"; // fuer Sitemapå
	} else
		$save_path = "$path_id_path/$site_url";

	// Inhalt auslesen
	$content = file_get_contents($site_pfad);

	$content = preg_replace("/index.php/", "", $content);

	// $content = preg_replace_callback("/value=\'(\w+)\'/", change_link2, $content);

	// Umwandeln interner Links (Version mit site_select)
	$content = preg_replace_callback("/\?site_select=(\w+)/", 'change_link', $content);

	// Neue Version mit ajax
	$content = preg_replace_callback("/\"\#\" onclick=\"CallContentSite\(\'(\w+)\'\)\"/", 'change_link', $content);

	// Austauschen der der Pfade
	$content = preg_replace($change['pattern'], $change['replace'], $content);
	$content = preg_replace("[$user_path_compress]", '', $content);

	/**
	 * *************************************************************************
	 * check ob das Bazarmodul aufgerufen wird oder nicht
	 * *************************************************************************
	 */
	$sql = $GLOBALS['mysqli']->query("SELECT gadget FROM smart_layer WHERE gadget = 'bazar' AND site_id = '$site_id' ");
	if (mysqli_num_rows($sql)) {
		// Aufruf von indivduellen METATAGS für die einzelen Artikel
		$metatag = '<? $site_id =' . $site_id . '; $lang = ' . $_SESSION['page_lang'] . '; $company_id = ' . $_SESSION['smart_company_id'] . '; include ("gadgets/bazar/call_metatag.php") ?>';
	} else {
		// $url = $_SESSION['url'] = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		// $metatag .= "\n<meta property='og:url' content='$url' />";

		if ($set_ssl) {
			$fb_image_ssl = "https://www.$domain_xml";
		} else
			$fb_image_ssl = "http://www.$domain_xml";

		if (!$fb_title and $title)
			$fb_title = $title;

		$metatag = "\n<title>$title</title>";

		if ($fb_title)
			$metatag .= "\n<meta property='og:title' content='$fb_title' />";
		if ($fb_text)
			$metatag .= "\n<meta property='og:description' content='$fb_text'/>";
		if ($fb_image) {
			$metatag .= "\n<meta property='og:image' content='$fb_image_ssl$fb_image' />";
			$metatag .= "\n<meta property='og:image:width' content='640'>";
			$metatag .= "\n<meta property='og:image:height' content='300'>";
			$metatag .= "\n<link itemprop='thumbnailUrl' href='$fb_image_ssl$fb_image'>";
			$metatag .= "\n<link rel='image_src' type='image/jpeg' href='$fb_image_ssl$fb_image' />";
		}

		if ($meta_keywords)
			$metatag .= "\n<meta name='keywords' content='$meta_keywords'>";
		if ($meta_text)
			$metatag .= "\n<meta name='description' content='$meta_text'>";
		if ($meta_author)
			$metatag .= "\n<meta name='author' content='$meta_author $php_date'>";
	}

	//header(\"Expires: date ('F d Y H:i:s') \");
	//header(\"Cache-Control: max-age=2592000\"); 

	if ($file_ending == '.php') {
		/**
		 * ************************************************************
		 * mm@ssi.at 14.12.2020
		 * laden von PHP Parametern für die Funktionalität des Modules
		 * ************************************************************
		 */
		$sql = $GLOBALS['mysqli']->query("SELECT * FROM smart_layer INNER JOIN smart_element_options ON element_id = layer_id WHERE gadget = 'other' AND site_id = '$site_id' AND option_name = 'placeholder' ");
		while ($option_array = mysqli_fetch_array($sql)) {
			$option_value = $option_array['option_value'];
			if (is_file(__DIR__ . '/../../gadgets/' . $option_value . '/page_generate.inc'))
				include(__DIR__ . '/../../gadgets/' . $option_value . '/page_generate.inc');
		}


		$content_header = "
		<?php 
		header(\"Cache-Control: no-cache, must-revalidate\");
  		header(\"Pragma: no-cache\");
		header(\"Expires: 0\");
		$add_php_parameter
		?>\n";
	} else
		$content_header = "";

	$metatag .= "\n<meta name=\"date\" content='" . date("F d Y H:i:s") . "'>";
	$metatag .= "\n<meta property='og:type' content='website' />";
	$metatag .= "\n<meta property='og:url' content='$site_url' />";

	$content = preg_replace("[{%metatag%}]", $metatag, $content);

	// CONTENT Speichern
	file_put_contents($save_path, $content_header . $content);

}
sleep(1);

/**
 * ****************************************
 * Erzeugt Sitemap
 * Wird nur erzeugt wenn index_off = 0 ist
 * ****************************************
 */

if (!$index_off) {
	$query = $GLOBALS['mysqli']->query("SELECT * FROM smart_langSite LEFT JOIN smart_id_site2id_page ON site_id = fk_id WHERE page_id = '$page_id' AND lang = '{$_SESSION['page_lang']}' ") or die(mysqli_error($GLOBALS['mysqli']));
	while ($array = mysqli_fetch_array($query)) {
		$site_id = $array['site_id'];

		//Prüft ob indexierung für Suchmaschine erfolgen soll (Ausführung weiter unten
		$set_no_index = call_smart_option($page_id, $site_id, 'no_index');

		//Nur anlegen wenn die Seite auf nicht auf No_index gesetzt wurde
		if (!$set_no_index) {
			// Setzt die Endung des Skriptes html oder php
			$file_ending = check_php_script($site_id);
			if (!$array['site_url'])
				$array['site_url'] = $array['fk_id'];

			if ($index_id == $site_id) {
				$save_path = "$path_id_path/" . "index$file_ending";
				$site_url = "index$file_ending"; // fuer Sitemapå
			} else {
				$site_url = $array['site_url'] . "$file_ending";
			}

			if ($set_ssl)
				$sitemap_xml_liste .= "\n<url><loc>https://www.$domain_xml/$site_url</loc><changefreq>weekly</changefreq></url>";
			else
				$sitemap_xml_liste .= "\n<url><loc>http://www.$domain_xml/$site_url</loc><changefreq>weekly</changefreq></url>";
		}
	}

	// Gerneriert sitemap
	$sitemap_xml = '<?xml version="1.0" encoding="UTF-8"?>
	<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $sitemap_xml_liste . '</urlset>';

	fu_txt_writer($sitemap_xml, "$path_id_path/sitemap.xml");
	flush_text("Erzeuge: sitemap.xml", "1");
}

// Add one bei GoogleAds werbung
if ($GoogleAds) {
	fu_txt_writer("google.com, $GoogleAds , DIRECT, f08c47fec0942fa0", "$path_id_path/ads.txt");
}

exec("rsync -avz $path_id_user/explorer/$page_id/ $path_id_path/explorer -rogpq --delete");
exec("rsync -avz $path_id_user/compress/$page_id/* $path_id_path/ -rogpq --delete");

// Change paths inner file
// change_path_inner_file ( "$path_id_path/compress.css", $change['pattern'], $change['replace'] );

// set_public status auf 0 setzen
$GLOBALS['mysqli']->query("UPDATE smart_page  SET set_public =  0, set_update = 0, set_public_timestamp = '0000-00-00 00:00:00' WHERE page_id = '$page_id' ") or die(mysqli_error($GLOBALS['mysqli']));

// set upgedatete Seite auf 0 zurück
$GLOBALS['mysqli']->query("UPDATE smart_id_site2id_page SET set_update = 0 WHERE page_id = '$page_id' ") or die(mysqli_error($GLOBALS['mysqli']));

flush_text("Webseitenerzeugung wird abgeschlossen...", "3");

flush_text("successful_generate");

//exec ( "chmod 440 $path_id_path/*  -R  |  find $path_id_path/* -type d -exec chmod 550 {} \; ");
function flush_text($text, $sleep = 0)
{
	echo $text;
	ob_flush();
	flush();
	if ($sleep > 0)
		sleep($sleep);
}
