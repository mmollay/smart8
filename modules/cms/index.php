<?php
error_reporting(E_ALL ^ E_NOTICE);
error_reporting(1);
session_start();
use MatthiasMullie\Minify;
include_once("lang/de.php");
$_SESSION['version_smart'] = '8.2';

// Testmodus
$set_autopopup = 'off'; // on

// $scrollup_style = 'tab';
$scrollup_style = 'pill';

$_SESSION['load_js'] = '';
// $url = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// zum testen
// $_GET['public'] = 1; $_GET['site_id'] = 1204;

if (($_GET['public'] or $_GET['preview']) and $_GET['site_id']) {

	$_SESSION['admin_modus'] = '';
	$site_id = $_SESSION['site_id'] = $_GET['site_id'];
	setcookie("site_id", $_SESSION['site_id'], time() + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST']);

	if ($_GET['company'])
		$company = $_SESSION['company'] = $_GET['company'];
	else
		$company = $_SESSION['company'] = 'ssi';

	// Call Content
	include_once('../login/config_main.inc.php');

	$query = $GLOBALS['mysqli']->query("SELECT
			site_key, smart_page.user_id user_id, secret_key, smart_page.FacebookPixel FacebookPixel, smart_page.TrackingCode TrackingCode, appID, smart_domain, site_url,  smart_id_site2id_page.page_id , lang, OptimizeCode
			FROM smart_langSite,smart_id_site2id_page,smart_page
				WHERE smart_id_site2id_page.site_id = smart_langSite.fk_id
				AND smart_page.page_id = smart_id_site2id_page.page_id
				AND smart_langSite.fk_id = '{$_SESSION['site_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));

	$array = mysqli_fetch_array($query);
	$page_id = $array['page_id'];
	$layout_id = $array['layout_id'];
	$lang = $array['lang'];
	$smart_domain = $array['smart_domain'];
	// $TrackingCode = $array['TrackingCode'];
	$OptimizeCode = $array['OptimizeCode'];
	$FacebookPixel = $array['FacebookPixel'];
	$appID = $array['appID'];
	$no_smartphone_modus = $array['no_smartphone_modus'];

	call_smart_option($page_id, '', '', true);
	// Ruft site settings aus
	call_smart_option($page_id, $site_id, '', true);

	// Diese Werte werden in "config_public.php übertragen und zur Verfügung gestellt
	$_SESSION['user_id'] = $user_id = $array['user_id'];

	$_SESSION['site_key'] = $array['site_key']; // Recaptcha - Google
	$_SESSION['secret_key'] = $array['secret_key']; // Recaptcha - Google
	$_SESSION['smart_page_id'] = $page_id; // Page_ID
	$_SESSION['page_lang'] = $lang; // Lang

	include_once('config.inc.php');
	include_once("inc/load_css.php");
	include_once("inc/load_content.php");
	// include_once ('library/css_umwandler.inc');
	include_once('trackingCode.inc.php');

	if ($TrackingCode) {
		$head_add .= call_analytics_old($TrackingCode, $OptimizeCode);
		// $head_add .= call_analytics_old ( $TrackingCode, $OptimizeCode );
	}

	// New Version of Anlaytics V4
	if ($TrackingCodeV4) {
		$head_add .= call_analytics($TrackingCodeV4, $OptimizeCode);
		// $head_add .= call_analytics_old ( $TrackingCode, $OptimizeCode );
	}

	if ($GoogleTagManager) {
		$arrayTagMangaer = call_googletag($GoogleTagManager);
		$head_add .= $arrayTagMangaer['header'];
		$GoogleTagBody = $arrayTagMangaer['body'];
	}

	// $GLOBALS['add_js2'] .= call_analytics ( $TrackingCode,$OptimizeCode );

	if ($GoogleAds) {
		$head_add .= "
		<script async src='//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'></script>
		<script>
		(adsbygoogle = window.adsbygoogle || []).push({
			google_ad_client: '$GoogleAds',
			enable_page_level_ads: true
		});
		</script>";
	}

	if ($FacebookPixel) {
		$output_js['FacebookPixel'] = "
		<script type='text/javascript'>
		!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
		n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
		document,'script','https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '$FacebookPixel');
		fbq('track', 'PageView');
		fbq('track', 'Search');
		fbq('track', 'ViewContent');
		</script>
		<noscript><img height=\"1\" width=\"1\" style=\"display:none\" src=\"https://www.facebook.com/tr?id=$FacebookPixel&ev=PageView&noscript=1\"/></noscript>";
		// $output_js['FacebookPixel2'] = "<script>fbq('track', 'Search', { search_string: 'leather sandals', content_ids: ['1234', '2424', '1318'], content_type: 'product' }); </script>";
	}

	// Recaptcha - Google
	if ($site_key) {
		$output_js['Recaptcha'] = "\n<script src='https://www.google.com/recaptcha/api.js'></script>";
	}

	// Muss an dieser Position bleiben damit die Darstellung richtig ist (Layer verschwinden sonst)
	$output_content = load_content($_SESSION['site_id']);
	// style='height:auto;' -> Damit bei Pickadate nicht das Formular nach oben springt

	$set_output['head'] .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//DE\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">";

	$set_output['head'] .= "
	<!--
	*
	* This homepage is generated with the Smart-Kit from SSI
	* Copyright(c) 2008-2018 by Martin Mollay
	* All rights reserved.
	*
	* Company: SSI - Service-Support-Internet
	* Webite: https://www.ssi.at
	*
	-->";

	$set_output['head'] .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"de\" xml:lang=\"de\">";
	$set_output['head'] .= "\n<head>";
	if ($head_add)
		$set_output['head'] .= "\n$head_add";

	if (!$_GET['preview'])
		$set_output['head'] .= "\n{%metatag%}"; // Wird beim erzeugen in generate page_generate.php als include fuer BAZAR eingebunden

	$set_output['head'] .= "\n<meta http-equiv='Pragma' content='no-cache'>";
	$set_output['head'] .= "\n<meta http-equiv='expires' content='-1'>";
	$set_output['head'] .= "\n<meta http-equiv='cache-control' content='no-cache'>";
	$set_output['head'] .= "\n<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
	$set_output['head'] .= "\n<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1' />";

	// $set_output['head'] .= "\n<meta name='msapplication-TileColor' content='#ffffff'>";
	// $set_output['head'] .= "\n<meta name='msapplication-TileImage' content='explorer/favicon/ms-icon-144x144.png'>";
	// $set_output['head'] .= "\n<meta name='theme-color' content='#ffffff'>";

	// Wenn die Seiten nicht indexiert werden sollen (gLobal - Options)
	if ($index_off or $no_index)
		$set_output['head'] .= "\n<meta name='robots' content='noindex, nofollow'>";
	else
		$set_output['head'] .= "\n<meta name='robots' content='follow, index' />";

	$set_output['head'] .= "\n<meta name='generator' content='SmartKit v{$_SESSION['version_smart']}' />";

	if (!$no_smartphone_modus)
		$set_output['head'] .= "\n<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0'>";

	$set_output['head'] .= $output_js['FacebookPixel'];

	/**
	 * ***********************************************************
	 * Cookie - Generator
	 * https://cookieconsent.insites.com/download/
	 * ***********************************************************
	 */

	if ($cookie_consent) {
		if (!$cookie_text)
			$cookie_text = "Diese Webseite benutzt Cookies für die bestmögliche Nutzung.";

		if (!$cookie_button_color)
			$cookie_button_color = "#8ec760";

		$set_output['css'] .= '<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.css" />';

		$set_output['body_end'] = '
    
    <script src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.1/cookieconsent.min.js"></script>
    <script>
    window.addEventListener("load", function(){
        window.cookieconsent.initialise({
            palette: {
                popup: { background: "#efefef", text: "#000000" },
                button: { background: "' . $cookie_button_color . '", text: "#ffffff" }
            },
            theme: "edgeless",
            content: { message: "' . $cookie_text . '", dismiss: "Verstanden!" }
        });
    });
    </script>';
	}
	/**
	 * *************************************************************************************************
	 * MINIFY - CSS AND JS
	 * *************************************************************************************************
	 */

	$strtotime = strtotime("now");

	$path_compress = "../smart_users/{$_SESSION['company']}/user{$_SESSION['user_id']}/compress/";
	if (!is_dir($path_compress))
		mkdir("$path_compress");
	if (!is_dir("$path_compress$page_id"))
		mkdir("$path_compress$page_id");
	$path_compress_file_css = "$path_compress$page_id/compress$site_id.css";
	$path_compress_file_form_css = "$path_compress$page_id/compress_form.css";
	$path_compress_file_js = "$path_compress$page_id/compress.js";
	$path_compress_file_site_js = "$path_compress$page_id/compress_site$site_id.js";
	// $path_compress_file_upload = "$path_compress$page_id/compress_upload.js";

	// Datei entfernt compress Compress CSS
	// exec ( "rm $path_compress_file_css" );
	// exec ( "rm $path_compress_file_js" );

	if (is_array($_SESSION['load_js'])) {

		// checkt nach welche js geladen werden sollen
		foreach ($_SESSION['load_js'] as $key => $value) {

			// Ladet js fuer folgende Module
			if (in_array($key, array('hideme', 'bazar', 'day21', 'formular')) && !$set_include_1) {
				// $set_output['js'] .= $include_js1['upload'];

				$output_js['upload'] .= "\n" . '<script src="smart_form/smart_form.js"></script>';
				$output_js['upload'] .= "\n" . '<script src="smart_form/timedropper/timedropper.min.js"></script>';
				$output_js['upload'] .= "\n" . '<script src="smart_form/jquery-upload/js/vendor/jquery.ui.widget.js" type="text/html" ></script>';
				$output_js['upload'] .= "\n" . '<script src="smart_form/jquery-upload/js/load-image.min.js"></script>';
				$output_js['upload'] .= "\n" . '<script src="smart_form/jquery-upload/js/canvas-to-blob.min.js"></script>';
				// $output_js['upload'] .= "\n".'<script src="smart_form/js/gallery/js/jquery.blueimp-gallery.min.js" type="text/html"></script>';
				$output_js['upload'] .= "\n" . '<script src="smart_form/jquery-upload/js/jquery.iframe-transport.js"></script>';
				$output_js['upload'] .= "\n" . '<script src="smart_form/jquery-upload/js/jquery.fileupload.js"></script>';
				$output_js['upload'] .= "\n" . '<script src="smart_form/jquery-upload/js/jquery.fileupload-process.js"></script>';
				$output_js['upload'] .= "\n" . '<script src="smart_form/jquery-upload/js/jquery.fileupload-image.js"></script>';
				$output_js['upload'] .= "\n" . '<script src="smart_form/jquery-upload/js/jquery.fileupload-audio.js"></script>';
				$output_js['upload'] .= "\n" . '<script src="smart_form/jquery-upload/js/jquery.fileupload-video.js"></script>';
				$output_js['upload'] .= "\n" . '<script src="smart_form/jquery-upload/js/jquery.fileupload-validate.js"></script>';
				$output_js['upload'] .= "\n" . '<script src="smart_form/jquery-upload/js/jquery.fileupload-ui.js"></script>';

				$minifier_css_form = new Minify\CSS();
				$minifier_css_form->add('smart_form/smart_form.css');

				// $minifier_css_form->add ( 'smart_form/pickadate/themes/default.css' );
				// $minifier_css_form->add ( 'smart_form/pickadate/themes/default.date.css' );
				$minifier_css_form->add('smart_form/timedropper/timedropper.min.css');
				// $minifier_css_form->add ( 'smart_form/datedropper/datedropper.min.css' );

				$minifier_css_form->add('smart_form/jquery-upload/css/jquery.fileupload.css');
				$minifier_css_form->minify($path_compress_file_form_css);
				$set_output['css_form'] .= "\n<link rel='stylesheet' type='text/css' href='$path_compress_file_form_css?v={$strtotime}'>"; // Compress - CSS
				$set_include_1 = true;
			}

			if ($key == 'day21' && !$set_include_3) {
				// SOUND
				$output_js['sound'] .= "\n<script type='text/javascript' src='smart_form/js/ion.sound/ion.sound.min.js'></script>";
				// $minifier_css->add ( 'smart_form/js/ion.sound/ion.sound.min.js' );
				$set_include_3 = true;
			}
		}
	}

	// erzeugt einen neuen
	$minifier_css = new Minify\CSS();
	$minifier_css->add("css/style_first.css");
	$minifier_css->add($set_style);
	$minifier_css->add('smart_form/smart_list.css');
	$minifier_css->add('css/style_second.css');
	$minifier_css->minify($path_compress_file_css);

	$user_path = "{$_SESSION['path_user']}user$user_id/explorer/$page_id";
	$user_path_compress = "..{$_SESSION['path_user']}user$user_id/compress/$page_id/";
	$change['pattern'] = array("[\.\.$user_path]", "[$user_path]");
	$change['replace'] = 'explorer';
	change_path_inner_file("$path_compress_file_css", $change['pattern'], $change['replace']);

	// $set_output['css'] .= "\n<link rel='icon' href='explorer/favicon.ico' type='image/x-icon'>";
	$set_output['css'] .= "\n<link rel='stylesheet' type='text/css' href='smart_form/semantic/dist/semantic.min.css'>";
	$set_output['css'] .= "\n<link rel='shortcut icon' href='explorer/favicon.png' type='image/x-icon'>";
	$set_output['css'] .= "\n<link rel='stylesheet' type='text/css' href='gadgets/gallery/fancybox3/jquery.fancybox.css'>";
	$set_output['css'] .= "\n<link rel='stylesheet' type='text/css' href='gadgets/gallery/fleximages/jquery.flex-images.css'>";
	$set_output['css'] .= "\n<link rel='stylesheet' type='text/css' href='gadgets/gallery/carousel/assets/owl.carousel.min.css'>";
	$set_output['css'] .= "\n<link rel='stylesheet' type='text/css' href='gadgets/gallery/carousel/assets/owl.theme.default.min.css'>";
	$set_output['css'] .= "\n<link rel='stylesheet' type='text/css' href='js/scrollup/css/themes/$scrollup_style.css'>";
	$set_output['css'] .= $set_output['css_google'];
	$set_output['css'] .= $set_output['css_form'];
	$set_output['css'] .= $GLOBALS['add_css2'];
	$set_output['css'] .= "\n<link rel='stylesheet' type='text/css' href='$path_compress_file_css?v={$strtotime}'>"; // Compress - CSS
	/*
	 * if ($_SESSION['show_loginbar'])
	 * $include_js2 .= "\n<script type='text/javascript' src='gadgets/login_bar/login_bar.js'></script>";
	 */

	/**
	 * *************************************************************************
	 * JS - Frameworks UI
	 * ************************************************************************
	 */
	$output_js['ui'] .= "\n<script type='text/javascript' src='smart_form/jquery-ui/jquery.min.js'></script>";
	$output_js['ui'] .= "\n<script type='text/javascript' src='smart_form/jquery-ui/jquery-ui.min.js'></script>";
	$output_js['ui'] .= "\n<script type='text/javascript' src='smart_form/semantic/dist/semantic.min.js'></script>";
	/**
	 * *************************************************************************
	 * JS - Defaults
	 * ************************************************************************
	 */
	$minifier_js = new Minify\JS();
	$minifier_js->add("var smart_form_wp = 'smart_form/';");
	$minifier_js->add("js/scrollup/jquery.scrollUp.min.js");
	$minifier_js->add("js/paroller/dist/jquery.paroller.min.js");
	$minifier_js->add("gadgets/gallery/fancybox3/jquery.fancybox.js");
	$minifier_js->add("gadgets/gallery/carousel/owl.carousel.min.js");
	$minifier_js->add("gadgets/gallery/fleximages/jquery.flex-images.min.js");
	// $minifier_js->add ( "js/parallaxie.js" );
	$minifier_js->add("js/smart.js");
	$minifier_js->add("smart_form/js.cookie.js");
	$minifier_js->minify($path_compress_file_js);
	$output_js['default'] = "\n<script type='text/javascript' src='$path_compress_file_js?v={$strtotime}'></script>"; // Compress - JS
	$output_js['default'] .= "\n<script type='text/javascript' src='smart_form/js/smart_list.js'></script>";
	// $output_js ['default'] .= "\n<script src='https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.min.js'></script>";
	$output_js['default'] .= "\n<script type='text/javascript' src='gadgets/marquee/jquery.marquee.min.js'></script>";
	$output_js['default'] .= "\n<script type='text/javascript' src='gadgets/gallery/fancybox3/jquery.fancybox.js'></script>";
	$output_js['default'] .= "\n<script type='text/javascript' src='gadgets/gallery/carousel/owl.carousel.min.js'></script>";
	$output_js['default'] .= "\n<script type='text/javascript' src='gadgets/gallery/fleximages/jquery.flex-images.min.js'></script>";
	$output_js['default'] .= "\n<script type='text/javascript' src='smart_form/TouchSwipe/jquery.touchSwipe.js'></script>";
	$output_js['default'] .= "\n<script src='https://cdn.jsdelivr.net/npm/simple-parallax-js@5.1.0/dist/simpleParallax.min.js'></script>";

	/**
	 * *************************************************************************
	 * JS - Uebergabe von den Gadgets .
	 * ************************************************************************
	 */
	$output_js['gadgets'] .= $GLOBALS['add_path_js'];

	/**
	 * *************************************************************************
	 * JS - Speziell für die jeweilige Seite
	 * ************************************************************************
	 */
	// Entfernt "script" falls vorhanden - Wurde gemacht weil im publicbereich, js -> in file gegeben wird (mit minify)
	$GLOBALS['add_js2'] = preg_replace("[<script>|</script>]", "", $GLOBALS['add_js2']);
	$minifier_js2 = new Minify\JS($GLOBALS['add_js2']);
	$minifier_js2->minify($path_compress_file_site_js);

	$output_js['site_id'] = "\n<script>" . $GLOBALS['add_js2'] . "</script>";

	if ($set_autopopup == 'on')
		$output_js['site_id'] .= "\n<script type='text/javascript'>$(document).ready( function() { open_autopopup('$site_id'); }) </script>";

	echo $set_output['head'];
	echo $set_output['css'];
	echo $output_js['ui'];
	echo $output_js['upload'];
	echo $output_js['ckeditor'];
	echo $output_js['sound'];
	echo $output_js['default'];
	echo $output_js['site_id'];
	echo $output_js['gadgets'];
	echo $output_js['Recaptcha'];
	echo "\n</head>";
	echo $output_js['FacebookPixel2'];
	// echo "\n<body style='opacity: 0; transition: none; ' >";
	echo "\n<body>";
	// echo "\n $facebook_plugin";
	echo $GoogleTagBody;
	echo $output_content;
	echo $set_output['body_end'];
	echo "\n </body>";
	echo "\n</html>";
} else {

	include('index_admin.php');
}
