<?php
if (is_dir ( "../" . $_SESSION['explorer_folder'] )) {
	include ('../inc/gallery.inc.php');
	$array_folder = array_reverse ( directoryToArray ( "../" . $_SESSION['explorer_folder'], true, '' ) );
	// echo $_SESSION['explorer_folder'];
}


// call groups for the User
$mysql_group_query = $GLOBALS['mysqli']->query ( "SELECT * FROM article2group where article_id = '{$_POST['update_id']}' " );
while ( $mysql_group_fetch = mysqli_fetch_array ( $mysql_group_query ) ) {
	$group_id = $mysql_group_fetch['group_id'];
	$group_selected_array[] = $group_id;
}

$ck_editor = "entities:false,resize_enabled : false,autoDetectPasteFromWord: true,pasteFromWordRemoveStyles: true,filebrowserBrowseUrl : '../ssi_smart/admin/ckeditor_link.php', height:'250px',removePlugins : 'elementspath,resize,autogrow'";
$ck_editor2 = "entities:false,resize_enabled : false,autoDetectPasteFromWord: true,pasteFromWordRemoveStyles: true, filebrowserBrowseUrl : '../ssi_smart/admin/ckeditor_link.php', height:'250px',removePlugins : 'elementspath,resize,autogrow' ";

// $arr['ajax'] = array (  'success' => "after_submit( data )" ,  'dataType' => "html" );
$arr['ajax'] = array (  'success' => "$('#modal_form').modal('hide'); table_reload();" ,  'dataType' => "html" );
$arr['tab'] = array ( 'tabs' => [ "first" => "Stammdaten" , "sec" => "Internet" , "inside" => "Insite" ] );
$arr['sql'] = array ( 'query' => "SELECT * from article_temp WHERE temp_id = '{$_POST['update_id']}'" );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'two fields' );
$arr['field']['art_title'] = array ( 'tab' => 'first' , 'type' => 'input' , 'label' => 'Titel' ,  'validate' => true ,  'focus' => true );
$arr['field']['art_nr'] = array ( 'tab' => 'first' , 'type' => 'input' , 'label' => 'Artikel-Nr.' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field']['art_text'] = array ( 'tab' => 'first' , 'type' => 'textarea' , 'label' => 'Beschreibung' );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'fields' );
$arr['field']['netto'] = array ( 'tab' => 'first' , 'type' => 'input' , 'label' => 'Netto' , 'class' => 'two wide field' ,  'validate' => true  );
$arr['field']['count'] = array ( 'tab' => 'first' , 'type' => 'input' , 'label' => 'Anzahl' , 'class' => 'two wide field' ,  'validate' => true );
$arr['field']['format'] = array ( 'tab' => 'first' , 'type' => 'input' , 'label' => 'Format' , 'class' => 'three wide field' );
$arr['field']['account'] = array ( 'tab' => 'first' , 'type' => 'dropdown' , 'label' => 'Konto' , 'array' => $account_array , 'class' => 'six wide field search',  'validate' => true );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

$arr['field']['groups'] = array ( 'tab' => 'first' , 'type' => 'multiselect' , 'label' => 'Gruppen' , 'array' => $group_array , 'value' => $group_selected_array );

// $arr['field']['internet_show'] = array ( 'tab' => "sec" , 'type' => 'toggle' , 'label' => 'Im Internet sichbar machen' );
$arr['field']['internet_show'] = array ( 'tab' => 'sec' , 'type' => 'toggle' , 'label' => 'Im Internet sichbar machen' );
$arr['field']['free'] = array ( 'tab' => 'sec' , 'type' => 'toggle' , 'label' => 'Artikel frei verfügbar machen' );
$arr['field']['internet_title'] = array ( 'tab' => 'sec' , 'type' => 'input' , 'label' => 'Titel' );
$arr['field']['internet_text'] = array ( 'tab' => 'sec' , 'type' => 'ckeditor' , 'config' => $ck_editor );
$arr['field']['pdf'] = array ( 'tab' => 'sec' , 'label' => 'PDF' , 'type' => 'explorer' );
$arr['field']['gallery'] = array ( 'tab' => 'sec' , 'type' => 'dropdown' , 'label' => 'Bildergalerie' , 'array' => $array_folder );
// $arr['field']['internet_inside_title'] = array ( 'tab' => 'inside' , 'type' => 'input' , 'label' => 'Titel' );
// $arr['field']['internet_inside_text'] = array ( 'tab' => 'inside' , 'type' => 'ckeditor' , 'config' => $ck_editor2 );
// $arr['field']['gallery_inside'] = array ( 'tab' => 'inside' , 'type' => 'dropdown' , 'label' => 'Bildergalerie' , 'array' => $array_folder );
