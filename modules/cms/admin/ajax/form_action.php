<?
session_start ();
$action = $_POST ['action'];
$id = $_POST ['ID'];
include ('../../../login/config_main.inc.php');
include ('../../smart_form/include_form.php');
include ('../rights.inc.php');

$array_sites = GenerateArraySql ( "SELECT * FROM smart_langSite INNER JOIN smart_id_site2id_page ON smart_langSite.fk_id = smart_id_site2id_page.site_id and lang='{$_SESSION['page_lang']}' AND page_id='{$_SESSION['smart_page_id']}' AND NOT site_id = '{$_SESSION['site_id']}' ORDER BY timestamp desc, title  ", 'title' ); // %var% die ausgegeben werden soll

$arr ['form'] = array ('id' => 'form_action','action' => 'admin/ajax/form_action2.php','inline' => 'list' );

if ($action == 'clone') {
	$arr ['field'] ['count'] = array ('type' => 'dropdown','label' => 'Anzahl der Klone','min' => 1,'max' => 10,'step' => 1,'value' => 1 );
	$arr ['button'] ['submit'] = array ('value' => 'Element klonen','color' => 'blue' );
}
if ($action == 'hidden') {
	// Feld archiveren
	$arr ['sql'] = array ('query' => "SELECT hidden from smart_layer WHERE layer_id = '$id'" );
	$arr ['field'] ['hidden'] = array ('label' => 'Diese Element öffentlich verbergen','type' => 'checkbox' );
	$arr ['button'] ['submit'] = array ('value' => 'Speichern','color' => 'blue' );
} elseif ($action == 'archive') {
	// Feld archiveren
	$arr ['sql'] = array ('query' => "SELECT matchcode from smart_layer WHERE layer_id = '$id'" );
	$arr ['field'] ['matchcode'] = array ('label' => 'Bezeichnung','info' => 'Sorgfältig wählen um Wiedererkennbarkeit sicher zu stellen','type' => 'input','focus' => true,'validate' => 'Bitte Bezeichung eingeben' );
	$arr ['button'] ['submit'] = array ('value' => 'Archivieren','color' => 'blue' );
} elseif ($action == 'copy') {
	// Feld kopieren
	$arr ['sql'] = array ('query' => "SELECT matchcode from smart_layer WHERE layer_id = '$id'" );
	$arr ['field'] ['move_site_id'] = array ('label' => 'Feld kopieren in','class' => 'search','focus' => true,'array' => $array_sites,'type' => 'dropdown','placeholder' => '--Seite wählen--','validate' => 'Bitte die gewünschte Seite wählen' );
	$arr ['button'] ['submit'] = array ('value' => 'Element kopieren','color' => 'blue' );
} else if ($action == 'clonemove') {

	// include_once ('../../../login/inc/domain_select.inc.php');
	// $array_page = smart_select_domain($_SESSION['user_id'], $_SESSION['smart_page_id'], false, '');

	// Verschieben des Feldes auf eine andere Seite
	// $arr['field']['move_page_id'] = array(
	// 'label' => 'Feld verschieben in',
	// 'class' => 'search',
	// 'focus' => true,
	// 'array' => $array_page,
	// 'type' => 'dropdown',
	// 'placeholder' => '--Seite wählen--',
	// 'validate' => 'Bitte die gewünschte Seite wählen'
	// );

	// Verschieben des Feldes auf eine andere Seite
	$arr ['field'] ['move_site_id'] = array ('label' => 'Feld verschieben in','class' => 'search','focus' => true,'array' => $array_sites,'type' => 'dropdown','placeholder' => '--Seite wählen--','validate' => 'Bitte die gewünschte Seite wählen' );

	$arr ['button'] ['submit'] = array ('value' => 'Klonen & Verschieben','color' => 'blue' );
} else if ($action == 'move') {
	// Verschieben des Feldes auf eine andere Seite
	$arr ['field'] ['move_site_id'] = array ('label' => 'Feld verschieben in','class' => 'search','focus' => true,'array' => $array_sites,'type' => 'dropdown','placeholder' => '--Seite wählen--','validate' => 'Bitte die gewünschte Seite wählen' );
	$arr ['button'] ['submit'] = array ('value' => 'Verschieben','color' => 'blue' );
} else if ($action == 'delete') {
	// Löschen des Feldes
	$arr ['field'] [] = array ('type' => 'header','text' => 'Soll das Element tatsächlich gelöscht werden?' );
	$arr ['button'] ['submit'] = array ('value' => 'Löschen','color' => 'red' );
}

$arr ['ajax'] = array ('datatype' => 'script' );
$arr ['hidden'] ['action'] = $action;
$arr ['hidden'] ['id'] = $id;
$arr ['button'] ['close'] = array ('value' => 'Abbrechen','color' => 'gray','js' => "$('#modal_small').modal('hide'); " );

$output = call_form ( $arr );
echo $output ['html'];
echo $output ['js'];
?>