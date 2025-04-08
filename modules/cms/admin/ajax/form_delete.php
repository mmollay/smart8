<?php
include_once ('../../../login/config_main.inc.php');
include ('../../smart_form/include_form.php');

if (! $_POST['delete_id']) {
	
	// Aufruf nach löschen in einer Struktur
	if ($_POST['list_id'] == 'site_structure') {
		$arr['ajax'] = array (  'success' => "$('#modal_small').modal('hide'); reload_gadget_class('menu_field'); $('#menu_jstree_modal #{$_POST['update_id']}').remove();" ,  'dataType' => "html" );
		// Allgemeiner Aufruf nach löschen von Elementen (Version mit Link zum aufrufen)
	} elseif ($_POST['list_id'] == 'site_structure_sitebar') {
		$arr['ajax'] = array (  'success' => "$('#modal_small').modal('hide'); reload_gadget_class('menu_field'); $('#menu_jstree #{$_POST['update_id']}').remove(); " ,  'dataType' => "html" );
		// Allgemeiner Aufruf nach löschen von Elementen
	} else {
		$arr['ajax'] = array (  'success' => "table_reload(); $('#modal_form_delete,#modal_form_delete2').modal('hide');",  'dataType' => "html" );
	}
	
	$arr['hidden']['delete_id'] = $_POST['update_id'];
	$arr['hidden']['list_id'] = $_POST['list_id'];
	$arr['button']['submit'] = array ( 'value' => 'Löschen' , 'color' => 'red' );
	$arr['button']['close'] = array ( 'value' => 'Abbrechen' , 'color' => 'gray' ,  'js' => "$('#modal_small').modal('hide'); " );
	$output = call_form ( $arr );
	echo $output['html'];
	echo $output['js'];
	exit ();
}

switch ($_POST['list_id']) {
	
	case 'archive_list' :
		require ('../../config.inc.php');
		include ('../inc/function_del.inc.php');
		$abfrage = del_layer ( $_POST['delete_id'] );
		for($i = 0; $i < count ( $abfrage ); $i ++) {
			$GLOBALS['mysqli']->query ( $abfrage[$i] ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		}
		break;
	
	case 'site_list' :
	case 'site_structure' :
	case 'site_structure_sitebar':
		require ('../../config.inc.php');
		include ('../inc/function_del.inc.php');
		if ($_POST['delete_id']) {			
			$abfrage = del_site ( $_POST['delete_id'] );
			for($i = 0; $i < count ( $abfrage ); $i ++) {
				$GLOBALS['mysqli']->query ( $abfrage[$i] ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
			}
		}
		
		set_update_site ( 'all' );
		break;
}