<?php
$arr ['tab'] = array ('tabs' => [ "step1" => "Allgemein","step2" => "Facebook","google" => 'Google',"cookie" => 'Cookie' ],'active' => 'step1' );
$arr ['form'] = array ('action' => "admin/ajax/form_edit2.php",'id' => 'form_edit_option','size' => 'small' );

// Wird deaktiviert wenn alle Seiten umgestellt
$arr ['sql'] = array ('query' => "SELECT * from smart_page WHERE page_id = '{$_SESSION['smart_page_id']}'" );

$arr ['value'] = call_smart_option ( $_SESSION ['smart_page_id'] );

$arr ['field'] ['index_id'] = array ('tab' => 'step1','label' => 'Startseite','type' => 'dropdown','array' => $array_sites,'validate' => true );
// $arr['field']['generate_dirpath'] = array ( 'tab' => 'first1' , 'label' => 'DIR-PFAD erzeugen (Bsp.: //Domain/Ebene1/Seitename)' , 'type' => 'checkbox' );
$arr ['field'] ['no_smartphone_modus'] = array ('tab' => 'step1','label' => 'Smart-Phone Modus ausschalten','type' => 'checkbox','info' => 'Webseite wird regulär angezeigt' );

$arr ['field'] ['menu_logo'] = array ('tab' => 'step1','label' => 'Logo/Bild','type' => 'finder','info' => 'Dieses Image wird auf Smartphones neben den Menü angzeigt' );
$arr ['field'] ['menu_disable'] = array ('tab' => 'step1','label' => 'Menü ausblenden','type' => 'checkbox','info' => 'Falls das Menü über ein Element eingeblendet wird' );

$arr ['field'] [] = array ('tab' => 'step1','type' => 'line','text' => 'Indexierung und Dynamisierung' );
$arr ['field'] [] = array ('tab' => 'step1','type' => 'div','class' => 'message orange ui' );
$arr ['field'] ['index_off'] = array ('tab' => 'step1','label' => 'Für gesamte Webseite Indexierung verbieten','type' => 'toggle','info' => 'verhindert, dass Suchmaschinen die Webseite indexieren' );
$arr ['field'] ['global_set_dynamic'] = array ('tab' => 'step1','label' => 'Gesamte Webseite dynamisieren','type' => 'toggle','info' => 'die gesamte Webseite wie dynamisch erzeugt (.php)' );
$arr ['field'] [] = array ('tab' => 'step1','type' => 'div_close' );

if ($right_analytics or ! $right_id) {
	$arr ['field'] ['FacebookPixel'] = array ('tab' => 'step2','label' => 'Facebook Pixel-ID','type' => 'input' );
	$arr ['field'] [] = array ('tab' => 'step2','type' => 'div','class' => 'two fields' );
	$arr ['field'] ['appID'] = array ('tab' => 'step2','label' => 'App-ID','type' => 'input' );
	$arr ['field'] ['appSecret'] = array ('tab' => 'step2','label' => 'App-Secret','type' => 'input' );
	$arr ['field'] [] = array ('tab' => 'step2','type' => 'div_close' );

	// $arr['field']['GoogleAdsense'] = array ( 'tab' => 'google' , 'label' => 'Google AdSense' , 'type' => 'input' );
	$arr ['field'] ['GoogleAds'] = array ('tab' => 'google','label' => 'Google Werbung','type' => 'input' );

	//$arr ['field'] ['TrackingCode'] = array ('tab' => 'google','label' => 'Google Analystic (wird 01.2023 eingestellt)','type' => 'input' );
	$arr ['field'] ['TrackingCodeV4'] = array ('tab' => 'google','label' => 'Google Analystic (V4)','type' => 'input' );
	$arr ['field'] ['GoogleTagManager'] = array ('tab' => 'google','label' => 'Google Tag Manager','type' => 'input', 'placeholder' =>'GTM-57G7KR7' );
	
	$arr ['field'] ['OptimizeCode'] = array ('tab' => 'google','label' => 'Google Optimize','type' => 'input' );
	$arr ['field'] [] = array ('tab' => 'google','type' => 'header','text' => 'Recaptcha <a href="https://www.google.com/recaptcha/intro/android.html" target="_new">[Code holen]</a>' );

	//$arr ['field'] [] = array ('tab' => 'google','type' => 'div','class' => 'two fields' );
	$arr ['field'] ['site_key'] = array ('tab' => 'google','label_right' => 'Site Key','type' => 'input' );
	$arr ['field'] ['secret_key'] = array ('tab' => 'google','label_right' => 'Secret Key','type' => 'input' );
// 	$arr ['field'] [] = array ('tab' => 'google','type' => 'div_close' );
}

// https://cookieconsent.insites.com/download/
$arr ['field'] ['cookie_consent'] = array ('tab' => 'cookie','label' => 'Cookie-Einverständnisbanner aktivieren','type' => 'checkbox' );
$arr ['field'] ['cookie_text'] = array ('tab' => 'cookie','label' => 'Anzeigetext','type' => 'input','placeholder' => 'Diese Webseite benutzt Cookies für die bestmögliche Nutzung.' );
$arr ['field'] ['cookie_button_color'] = array ('tab' => 'cookie','label' => 'Buttonfarbe','type' => 'color','placeholder' => '#8ec760' );

$arr ['ajax'] = array ('dataType' => "html",'success' => "
			if (data == 'ok') {
				$('#option_global').modal('hide');
				$('#option_global').flyout('hide');
				$('#ProzessBarBox').message({ type:'success',title:'Info', text: 'Allgemeine Optionen wruden gespeichert.' });
			}
		" );

?>