<?php
$add_js = '';
$add_css = '';

ini_set ( 'display_errors', 1 );
ini_set ( 'display_startup_errors', 1 );
error_reporting ( E_ERROR | E_PARSE );

/*
 * $form1 = new ssiPlattform("kmlist","SSI-Kilometer");
 * $form1->setConfig("logo","../image/logo.png");
 *
 * $form->setConfig("version","0.1"); //Angabe der Version
 * $form->setConfig("hideMainMenu",true); //Verkleinert das Menu Bsp.: Smart
 * $form->setConfig("hideVersionBorder",true); //Versteckt VersionsBorder am FUSS
 *
 *
 * $output = $form->getHTML();
 *
 * $form->login();
 */
class ssiPlattform {
	private $modul;
	private $title;
	private $version;
	private $login;
	private $add_css;
	private $add_js;
	private $text;
	private $menu;
	private $menu_top_left;
	private $menu_top_right;
	public function __construct($modul, $title) {
		// connect zu Datenbank laden
		include_once ('config_main.inc.php');

		$this->modul = $modul;
		$this->title = $title;

		// setcookie("modul", $modul, time() + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST']);

		// date_default_timezone_set('Europe/Berlin');

		// Prüfen ob User sich in der Datenbank befindet
		$query = $GLOBALS ['mysqli']->query ( "SELECT user_id FROM ssi_company.user2company WHERE verify_key = '{$_SESSION['verify_key']}' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		if (mysqli_num_rows ( $query )) {
			$_SESSION ['login_user_id'] = $_SESSION ['user_id'];
			$this->login = true;
		}

		// else
		// session_destroy ();

		// Laden der gueltigen Domains
		include (__DIR__ . '/../login/inc/domain_select.inc.php');
		// $this->user_domains = select_domain ($_SESSION['user_id']);
	}
	public function setConfig($name, $value) {
		switch ($name) {
			// Uebergabe Logoparameter
			case "version" :
				$this->version = $value;
				break;
			case "hideMainMenu" :
				$this->hideMainMenu = $value;
				break;
			case "hideVersionBorder" :
				$this->hideVersionBorder = $value;
				break;
		}
	}
	public function setCss($value) {
		$this->add_css = $value;
	}
	public function setJs($value) {
		$this->add_js = $value;
	}
	public function setContent($name, $value, $align = 'left') {
		switch ($name) {
			case "logo" :
				$this->logo = $value;
				break;
			case "menu" :
				if (! $_SESSION ['service_offline']) {
					$this->menu = $value;
				}
				break;
			case "menu_top" :
				{
					if ($align == 'left')
						$this->menu_top_left = $value;
					else if ($align == 'right')
						$this->menu_top_right = $value;
				}
				break;

			case "sidebar" :
				$this->sidebar = $value;
				break;
			case "text" :

				if ($_SESSION ['service_offline'] == true) {
					$this->text = "
					<br><br><div align=center><i class='icon ui disabled protect massive'></i><br><br><div class='ui compact error huge message'>
					<p>Dieser Dienst ist momentan nicht verfügbar!<br>Grund: {$_SESSION['service_offline_reason']}<br><br>
					</div></div>";
				} else {
					$this->text = $value;
				}
				break;
			case "body_begin" :
				$this->body_begin = $value;
		}
	}
	public function login() {
		if ($_SESSION ['verify_key'] and $_SESSION ['user_id'])
			return true;
	}
	public function getHTML() {
		// Wenn User nicht angemeldet ist
		if (! $_SESSION ['verify_key'] or ! $_SESSION ['user_id']) {
			header ( "location: ../ssi_smart/gadgets/login/index.php?lp=center" );
			exit ();
		} else {
			include_once (__DIR__ . '/../information/function.php');
			include_once (__DIR__ . '/inc/menu_top.php');
			include (__DIR__ . '/../login/config_main.inc.php');
		}

		$add_css = "\n<link rel='stylesheet' type='text/css' href='../ssi_smart/smart_form/semantic/dist/semantic.min.css' />";
		$add_css .= "\n<link rel='stylesheet' type='text/css' href='../login/css/main.css'>";
		$add_css .= "\n<link rel='stylesheet' type='text/css' href='../ssi_smart/smart_form/jquery-upload/css/jquery.fileupload.css'>";
		$add_css .= "\n<link rel='stylesheet' type='text/css' href='../ssi_smart/smart_form/jquery-ui/jquery-ui.min.css'>";
		$add_css .= "\n<link rel='stylesheet' type='text/css' href='../ssi_smart/smart_form/timedropper/timedropper.min.css'>";
		$add_css .= "\n<link rel='stylesheet' type='text/css' href='../ssi_smart/smart_form/colorpicker-master/dist/css/default-picker/light.min.css'>";
		$add_css .= "\n<link rel='stylesheet' type='text/css' href='../ssi_smart/smart_form/smart_list.css' />";
		$add_css .= "\n<link rel='stylesheet' type='text/css' href='../ssi_smart/smart_form/smart_form.css' />";
		$add_css .= "\n<link rel='stylesheet' type='text/css' href='../login/css/basis.css'>";
		$add_css .= "\n<link rel='stylesheet' type='text/css' href='../ssi_smart/gadgets/gallery/fancybox3/jquery.fancybox.css'>";
		$add_css .= $this->add_css;

		$add_js = '';

		if ($_SESSION ['user_id']) {
			$add_js .= "
			<script type='text/javascript' >
				var user_id = '{$_SESSION['user_id']}';
                var modul = '{$this->modul}';
				var smart_form_wp = '../ssi_smart/smart_form/';
			</script>";
		}

		$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/jquery-ui/jquery.min.js'></script>";
		$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/jquery-ui/jquery-ui.min.js'></script>";
		$add_js .= "\n<script type='text/html' src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js'></script>";

		$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/js.cookie.js'></script>";
		$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/semantic/dist/semantic.min.js'></script>";
		$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/js/smart_list.js'></script>";
		$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/js/smart_form.js'></script>";
		$add_js .= "\n<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?key=AIzaSyAgoO9CQxiF6tddu1WIKqB5vrONEHsoLTM&region=AT'></script>";
		$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/timedropper/timedropper.min.js'></script>";
		//$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/js/tablesort.js'></script>";

		$add_js .= "\n" . '<script type="text/html" src="../ssi_smart/smart_form/jquery-upload/js/vendor/jquery.ui.widget.js"></script>';
		// $add_js .= "\n".'<script src="../ssi_smart/smart_form/jquery-upload/js/tmpl.min.js"></script>';
		$add_js .= "\n" . '<script src="../ssi_smart/smart_form/jquery-upload/js/load-image.min.js"></script>';
		$add_js .= "\n" . '<script src="../ssi_smart/smart_form/jquery-upload/js/canvas-to-blob.min.js"></script>';

		$add_js .= "\n" . '<script src="https://blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js"></script>';
		$add_js .= "\n" . '<script src="https://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>';

		$add_js .= "\n" . '<script src="../ssi_smart/smart_form/jquery-upload/js/jquery.iframe-transport.js"></script>';
		$add_js .= "\n" . '<script src="../ssi_smart/smart_form/jquery-upload/js/jquery.fileupload.js"></script>';
		$add_js .= "\n" . '<script src="../ssi_smart/smart_form/jquery-upload/js/jquery.fileupload-process.js"></script>';
		$add_js .= "\n" . '<script src="../ssi_smart/smart_form/jquery-upload/js/jquery.fileupload-image.js"></script>';
		$add_js .= "\n" . '<script src="../ssi_smart/smart_form/jquery-upload/js/jquery.fileupload-audio.js"></script>';
		$add_js .= "\n" . '<script src="../ssi_smart/smart_form/jquery-upload/js/jquery.fileupload-video.js"></script>';
		$add_js .= "\n" . '<script src="../ssi_smart/smart_form/jquery-upload/js/jquery.fileupload-validate.js"></script>';
		$add_js .= "\n" . '<script src="../ssi_smart/smart_form/jquery-upload/js/jquery.fileupload-ui.js"></script>';

		$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/ckeditor/ckeditor.js'></script>";
		$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/ckeditor/adapters/jquery.js'></script>";
		
		$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/jquery.message.js'></script>";
		// $add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/mColorPicker/mColorPicker.js'></script>";
		$add_js .= "\n<script src='../ssi_smart/smart_form/colorpicker-master/dist/js/default-picker.min.js'></script>";

		$add_js .= "\n<script type='text/javascript' src='../ssi_smart/gadgets/gallery/fancybox3/jquery.fancybox.js'></script>";

		$add_js .= "\n<script src='https://www.google.com/recaptcha/api.js'></script>";
		$add_js .= "\n<script type='text/javascript' src='../login/js/duplicate.js'></script>";
		$add_js .= "\n<script type='text/javascript' src='../login/js/main.js'></script>";
		
		// $add_js .= "\n<script type='text/javascript' src='../login/js/intro.js/intro.js'></script>";

		//$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/selectize/dist/js/standalone/selectize.js'></script>";
		//$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/js/jquery.validate.js'></script>";
		$add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/js/jquery.keyz.js'></script>";
		// $add_js .= "\n<script type='text/javascript' src='../ssi_smart/smart_form/jquery_ssi/jquery.ssi_list.js'></script>";

		// Elemente aus dem System übergeben (Bsp.: verification function für nicht bestätigte Anmeldungen)
		$add_js .= "\n<script type='text/javascript'>{$GLOBALS['smart_add_js']}</script>";

		$add_js .= $this->add_js;

		$add_js .= "\n
		<script type='text/javascript'>
			$(document).ready(function() {
				$('.hideAll').css({'display':'block'});
				$('body').css({'opacity':'1','transition':'1s opacity'});
				$('html').css({'background':''});
			});
		</script>";

		$menu_first = '';

		// $menu_first = '';
		// if ($this->title)
		// $menu_first .= "<br><div align=center><a href='index.php' class='ui header large grey'>{$this->title}</a></div>";
		// if ($this->logo) {
		// $menu_first .= "<br><div align=center><img src='{$this->logo}'></div>";
		// }

		/**
		 * *
		 * WEB - Login
		 * style='height:auto;' -> Damit bei Pickadate nicht das Formular nach oben springt
		 */
		$output = "
		<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//DE\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
		<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"de\" xml:lang=\"de\" style='background: url(\"../ssi_smart/smart_form/img/loading.gif\") no-repeat center'>
		<head>
		<meta name='generator' content='Smart-{$this->modul} {$this->version}'>
		<title>{$_SESSION['company_title']} {$this->title} {$this->version}	</title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1' />
		<meta http-equiv='expires' content='0'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0'>
		<meta property='og:title' content='SSI-Center' />
		<meta property='og:type' content='website' />
		<meta property='og:image' content=''/>
		<meta property='og:image:width' content='640'>
		<meta property='og:image:height' content='300'>
		<meta property='og:description' content='Smartkit vielseitig einsetzbar wenn es um Erstellung von Webseiten geht'/>
		<link itemprop='thumbnailUrl' href=''>
		<link rel='image_src' type='image/jpeg' href='' />		
		$add_css
		$add_js
		</head>";
		$output .= "<body style='opacity: 0; transition: none;' >";
		// $output .= "<body >";

		$output .= $this->sidebar ?? '';

		// Pusher - Content für sidebars - OPEN
		$output .= "<div class='pusher'>";
		// $output .= "<div style='display: none' class='hideLoader ui active inverted dimmer'><br><br><br><div class='ui large text loader'>Seite wird geladen</div></div>";
		// $output .= "<div style='opacity: 0; transition: none;' class='hideAll'>";
		$output .= $this->body_begin ?? '';

		if ($this->menu) {
			$output .= "
				<button class='tooltip button orange ui icon' title='Menü anzeigen' id=resize_button><i class='angle double right icon'></i></button>
				<button class='tooltip button orange ui icon' id=resize_button_hide title='Menü verstecken'><i class='angle double left icon'></i></button>
				<div class=center_left>$menu_first{$this->menu}";
			if (! isset ( $this->hideVersionBorder ))
				$output .= "<div class='version'><div class='ui basic label mini'>Version {$this->version}</div> - <a href='https://www.ssi.at' target='new'>Powered by SSI</a></div>";
			$output .= "</div>";
			$style_right_content = '';
		} else {
			$style_right_content = "style='padding-left:0px'";
		}

		$output .= $this->hideMainMenu ?? $main_menu;

		$output .= "
		<div class='right_content' $style_right_content>
			<div class='center_content' $style_right_content>
				{$this->text}
			</div>
		</div>";

		$output .= "
		<div id=window_form></div>
		<div id='dialog-confirm'></div>
		<div id=message></div>
		<div id=wait></div>
		<span id=ProzessBarBox></span>";

		// $output .= "</div>";

		// Pusher - Content für sidebars - CLOSE
		$output .= "\n</div>";

		$output .= "
		</body>
		</html>";

		return $output;
	}
}

// Erzeugt eine Menüstructur + div für Content
// $array_menu['test.php'] = array ( 'Testseite' , 'icon' , 'active' );
// $array_menu[] = 'hr';
// $array_menu_structure = call_menu_structure($array_menu)
// $setMenu = $array_menu_structure['menu'];
// $setContent = $array_menu_structure['content'];
function call_menu_structure($array_menu, $version = false) {
	$menuHtml = '';
	$contentHtml = '';

	foreach ( $array_menu as $key => $value ) {
		if ($value === 'hr') {
			$menuHtml .= '<hr>';
		} else {
			$data_tab = $key;
			$title = $value [0];
			$icon = $value [1];

			$active = isset ( $value [2] ) ? ($value [2] ? ' active' : '') : '';
			$icon_id = isset ( $value ['icon_id'] ) ? "id = '{$value['icon_id']}' " : '';
			$id = isset ( $value ['id'] ) ? "id = '{$value['id']}' " : '';

			$menuHtml .= "\n<a $id class='item$active' data-tab='$data_tab'><i $icon_id class='$icon icon'></i>$title</a>";

			if (! $version) {
				$contentHtml .= "\n<div class='ui tab$active' data-tab='$data_tab'></div>";
			}
		}
	}

	$result = [ 'menu' => '','content' => $contentHtml ];

	if (! $version) {
		$result ['menu'] = "<div style='position:relative; left:6px;' class='menu_structure ui vertical fluid tabular menu'>$menuHtml</div>";
	}

	return $result;
}
