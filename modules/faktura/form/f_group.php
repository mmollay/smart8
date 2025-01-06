<?php
if ($_SESSION ['explorer_folder'] and is_dir ( "../" . $_SESSION ['explorer_folder'] )) {
	$array_folder = array_reverse ( directoryToArray ( "../" . $_SESSION ['explorer_folder'], true, '' ) );
}

// Call groups for Subgroups
$query_groups = $GLOBALS ['mysqli']->query ( "SELECT group_id, title from article_group WHERE company_id = '{$_SESSION['faktura_company_id']}' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
while ( $fetch_groups = mysqli_fetch_array ( $query_groups ) ) {
	$array_groups [$fetch_groups ['group_id']] = $fetch_groups ['title'];
}

$arr ['ajax'] = array ('success' => "$('#modal_form_edit').modal('hide'); table_reload();",'dataType' => "html" );
$arr ['tab'] = array ('tabs' => [ "first" => "Stammdaten","sec" => "Internet","inside" => "Insite" ] );
$arr ['sql'] = array ('query' => "SELECT * from article_group WHERE group_id = '{$_POST['update_id']}' " );

$arr ['field'] ['title'] = array ('tab' => 'first','type' => 'input','label' => 'Titel','validate' => true,'focus' => true );
$arr ['field'] ['sort'] = array ('tab' => 'first','type' => 'input','label' => 'Reihenfolge' );
$arr ['field'] ['text'] = array ('tab' => 'first','type' => 'textarea' );
$arr ['field'] ['parent_id'] = array ('tab' => 'first','label' => 'In Gruppe zuweisen','type' => 'dropdown',"array" => $array_groups );
$arr ['field'] ['parent_id2'] = array ('tab' => 'first','label' => 'In Gruppe zuweisen','type' => 'dropdown',"array" => $array_groups );

$ck_editor = "entities:false, resize_enabled : false,autoDetectPasteFromWord: true,pasteFromWordRemoveStyles: true,filebrowserBrowseUrl : '../ssi_smart/admin/ckeditor_link.php', height:'250px',removePlugins : 'elementspath,resize,autogrow'";
$arr ['field'] ['internet_show'] = array ('tab' => 'sec','type' => 'toggle','label' => 'Im Internet anzeigen' );
$arr ['field'] ['internet_title'] = array ('tab' => 'sec','type' => 'input','label' => 'Titel' );
$arr ['field'] ['internet_text'] = array ('tab' => 'sec','type' => 'ckeditor','config' => $ck_editor );

if ($array_folder)
	$arr ['field'] ['gallery'] = array ('tab' => 'sec','type' => 'dropdown','array' => $array_folder );