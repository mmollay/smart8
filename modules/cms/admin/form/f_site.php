<?php
$arr ['form'] = array ('action' => "admin/ajax/form_edit2.php",'id' => 'form_edit','size' => 'small' );

if ($_POST ['clone_id']) {
	$arr ['sql'] = array (
			'query' => "
			SELECT site_id, fb_title, fb_text, fb_image, site_dynamic_id,dynamic_site,dynamic_name, menu_disable, menubar_disable, breadcrumb_disable,
			column_width, meta_title, meta_text,meta_keywords, menu_url, menu_newpage,
			CONCAT(title,'_clone') as site_title, CONCAT(menu_text,'_clone') menu_text, CONCAT(site_url,'_clone') site_url
			FROM smart_langSite, smart_id_site2id_page where fk_id = site_id AND site_id = '{$_POST['update_id']}' " );
} elseif ($_POST ['update_id']) {
	$arr ['sql'] = array (
			'query' => "
			SELECT site_id, fb_title, fb_text, fb_image, site_dynamic_id,dynamic_site,dynamic_name, menu_disable, menubar_disable, breadcrumb_disable,
			column_width,  meta_title, meta_text,meta_keywords,menu_url, menu_newpage,
			title as site_title, menu_text, site_url
			FROM smart_langSite, smart_id_site2id_page where fk_id = site_id AND site_id = '{$_POST['update_id']}' " );
}

// Array werte für Layout auslesen
// 		$sql = $GLOBALS ['mysqli']->query ( "SELECT layout_array from smart_id_site2id_page WHERE site_id = '{$_POST['update_id']}'" );
// 		$array = mysqli_fetch_array ( $sql );
// 		$layout_array = $array ['layout_array'];

// 		$layout_array_n = explode ( "|", $layout_array );
// 		foreach ( $layout_array_n as $array ) {
// 			$array2 = explode ( "=", $array );
// 			$GLOBALS [$array2 [0]] = $array2 [1];
// 		}

$arr ['value'] = call_smart_option ( $_SESSION ['smart_page_id'], $_POST ['update_id'] );

$global_settings = call_smart_option ( $_SESSION ['smart_page_id'], '', array ('index_off','global_set_dynamic' ) );

if ($global_settings ['index_off']) {
	$set_disable_index_off = 'true';
}

if ($global_settings ['global_set_dynamic']) {
	$set_disable_global_set_dynamic = 'true';
}

if ($set_disable_index_off or $set_disable_global_set_dynamic)
	$info_disalbe = 'Auf Grund einer globalen Einstellung ist diese Funktion deaktiviert!';

// $arr['field'][] = array ( 'type' => 'header', 'text' => 'Allgemein' , 'size' => '3' , 'class' => 'dividing' );

$arr ['tab'] = array ('tabs' => [ "first1" => "Allgemein","meta" => "Metatexte (Facebook,usw..)" ],'active' => 'first1' );

if (! $_POST ['clone_id'] && ! $_POST ['update_id']) {
	// Wenn eine neue Seite angelegt wird, kann man aus den Vorlagen wählen - erzeugt erste Elemente
	$array_template = array ('title_text_2col' => "<img src='admin/images/templates/title_text_2col.png' class='ui image tiny tooltip' title='2 spaltige Inhalte erzeugen'>",
			'title_text_1col' => "<img src='admin/images/templates/title_text_1col.png' title='1 spaltige Inhalte erzeugen' class='ui image tiny tooltip' >",
			'title_text_3col' => "<img src='admin/images/templates/title_text_3col.png' title='3 spaltige Inhalte erzeugen' class='ui image tiny tooltip' >" );

	$arr ['field'] ['template'] = array ('tab' => 'first1','label' => 'Vorlage wählen','type' => 'radio','array' => $array_template,'value' => 'title_text_2col' );
}

$arr ['field'] [] = array ('tab' => 'first1','type' => 'div','class' => 'fields three' );
$arr ['field'] ['site_title'] = array ('tab' => 'first1','label' => 'Titel der Seite','type' => 'input','focus' => true,'validate' => true );
$arr ['field'] ['site_url'] = array ('tab' => 'first1','label' => 'URL','label_right' => '.html','type' => 'input','validate' => true,'info' => 'Sprechende URL dient der Suchmaschinenoptimierung.' );
$arr ['field'] ['menu_text'] = array ('tab' => 'first1','label' => 'Menü-Titel','type' => 'input','validate' => true );
$arr ['field'] [] = array ('tab' => 'first1','type' => 'div_close' );
$arr ['field'] ['menu_text'] = array ('tab' => 'first1','label' => 'Menü-Titel','type' => 'input','validate' => true );
$arr ['field'] ['menu_url'] = array ('tab' => 'first1','label' => 'URL','type' => 'input','placeholder' => 'https://www.andereseite.at','info' => 'Wenn dieser Link angeführt ist, sind alle anderen Parameter wie interene Link inaktiv' );

// $arr['field'][] = array ( 'tab' => 'first1' , 'type' => 'header', 'text' => '' , 'size' => '3' , 'class' => 'dividing' );

$arr ['field'] ['menu_newpage'] = array ('tab' => 'first1','type' => 'toggle','label' => 'In neuer Seite öffnen' );
$arr ['field'] ['menu_disable'] = array ('tab' => 'first1','type' => 'toggle','label' => 'Diese Seite im Menü ausblenden' );

$arr ['field'] [] = array ('tab' => 'first1','type' => 'line','text' => 'Indexierung und Dynamisierung' );
$arr ['field'] [] = array ('tab' => 'first1','type' => 'div','class' => 'message orange ui' );
$arr ['field'] ['no_index'] = array ('tab' => 'first1','type' => 'toggle','label' => 'Indexierung für Suchmaschinen auf für Seite verhindern','disabled' => $set_disable_index_off );
$arr ['field'] ['set_dynamic'] = array ('tab' => 'first1','type' => 'toggle','label' => 'Diese Seite dynamisch laden','info' => 'Inhalte werde immer neu geladen','disabled' => $set_disable_global_set_dynamic );
$arr ['field'] [] = array ('tab' => 'first1','type' => 'content','text' => $info_disalbe,'color' => 'blue' );
$arr ['field'] [] = array ('tab' => 'first1','type' => 'div_close' );

//$array_split = array ('' => 'klassische Anzeige','1' => 'einspaltige Anzeige','reverse' => 'Spalten vertauschen' );

$arr ['field'] [] = array ('tab' => 'first1','type' => 'line','text' => 'Layout-Erweiterung' );
$arr ['field'] [] = array ('tab' => 'first1','type' => 'div','class' => 'message ui' );
$arr ['field'] ['menubar_disable'] = array ('tab' => 'first1','type' => 'toggle','label' => 'Gesamte Menü ausblenden' );
$arr ['field'] ['breadcrumb_disable'] = array ('tab' => 'first1','type' => 'toggle','label' => 'Brotkrümmelleiste ausblenden' );

$arr ['field'] [] = array ('tab' => 'first1','type' => 'div','class' => 'fields two' );
$arr ['field'] ['body_backgroundimage_site'] = array ('tab' => 'first1','label' => 'Body-Hintergrund','type' => 'finder' );
$arr ['field'] ['header_backgroundimage_site'] = array ('tab' => 'first1','label' => 'Kopf-Hintergrund','type' => 'finder' );
$arr ['field'] [] = array ('tab' => 'first1','type' => 'div_close' );

$arr ['field'] [] = array ('tab' => 'first1','type' => 'div_close' );

// $array_split = array ( "double" => "Zweispaltig" , "single" => "Einspaltig" );
$arr ['field'] [] = array ('tab' => 'meta','type' => 'content','class' => 'message ui','text' => "Dieser Text erscheint beim Teilen einer Seite auf Facebook oder What's App" );
$arr ['field'] ['fb_title'] = array ('tab' => 'meta','label' => 'Titel','type' => 'input' );
$arr ['field'] ['fb_text'] = array ('tab' => 'meta','label' => 'Beschreibung','type' => 'textarea','rows' => '3' );
$arr ['field'] ['fb_image'] = array ('tab' => 'meta','label' => 'Bild','type' => 'finder' );
$arr ['field'] ['meta_keywords'] = array ('tab' => 'meta','label' => 'Keywords','type' => 'textarea','rows' => '3' );

// $arr['field']['meta_title'] = array ( 'tab' => 'thi' , 'label' => 'Titel' , 'type' => 'input' );
// $arr['field']['meta_text'] = array ( 'tab' => 'thi' , 'label' => 'Beschreibung' , 'type' => 'textarea' , 'rows' => '3' );

// $arr['field'][] = array ( 'tab' => 'four' , 'type' => 'div' , 'class' => 'message ui' );
// $arr['field']['dynamic_name'] = array ( 'tab' => 'four' , 'label' => 'Interne Beschreibung' , 'type' => 'input' );
// $arr['field']['dynamic_site'] = array ( 'tab' => 'four' , 'label' => 'Als dynamische Seite definieren' , 'type' => 'toggle' , 'info' => 'Dieses Textelement kann in anderen Seiten eingebunden werden, somit ändert sich der Inhalt, bei anderen eingebundenen Seiten dynamisch mit.' );
// $arr['field'][] = array ( 'tab' => 'four' , 'type' => 'div_close' );

// $arr['field']['meta_keywords'] = array ( tab=>'', 'label' => '', 'type' =>'');
$arr ['ajax'] = array ('onLoad' => "onload_site_form()",'success' => "update_site_form(data)",'dataType' => "html" );
$arr ['hidden'] ['clone_id'] = $_POST ['clone_id'];
$add_js = "<script type='text/javascript' src='admin/js/form_site.js'></script>";

if ($_POST ['clone_id']) {
	$send_button ['text'] = 'Seite klonen';
	$send_button ['color'] = 'blue';
	$send_button ['icon'] = 'clone';
} elseif ($_POST ['update_id']) {
	$send_button ['text'] = 'Speichern';
	$send_button ['color'] = 'green';
	$send_button ['icon'] = 'save';
} else {
	$send_button ['text'] = 'Seite anlegen';
	$send_button ['color'] = 'green';
	$send_button ['icon'] = 'save';
}

?>