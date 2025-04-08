<?php
include_once (__DIR__ . "/../../../gadgets/config.php");
include_once (__DIR__ . '/../../../smart_form/include_form.php');

// $query_group_array_title = $GLOBALS['mysqli']->query ( "SELECT * from ssi_faktura.tree_template INNER JOIN tree_template_lang ON tree_template.temp_id = tree_template_lang.temp_id WHERE chooseable = 1 " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
// while ( $fetch_array = mysqli_fetch_array ( $query_group_array_title ) ) {
// if ($fetch_array['fruit_type'])
// $choosable_type_array[$fetch_array['temp_id']] = $group_array_title[$fetch_array['tree_group']] . " (" . $fetch_array['fruit_type'] . ")";
// else
// $choosable_type_array[$fetch_array['temp_id']] = $group_array_title[$fetch_array['tree_group']];
// }

// $arr['field']['plant_id'] = array ( 'label' => "Sorte wählen" , 'type' => 'dropdown' , 'array' => $choosable_type_array , 'class' => 'search' );

$arr['field']['tree_panel'] = array ( 'label' => 'Baumtafeltext' , 'type' => 'input' , 'class' => '' , 'placeholder' => 'Schreibe deinen persönlichen Text' ,  'focus' => true );
$arr['field']['amount'] = array ( 'label' => 'Betrag' , 'type' => 'content' , 'text' => '50,- (inkl. Mwst.)' );
$arr['field']['tree_id'] = array ( 'type' => 'hidden' , 'value' => $_POST['tree_id'] );

$arr['field'][] = array ( 'tab' => '1' , 'type' => 'div' , 'class' => 'four fields' );
$arr['field']['gender'] = array ( 'class' => 'four wide' , 'tab' => '1' , 'type' => 'dropdown' , 'label' => 'Titel' , 'array' => array ( 'f' => 'Frau' , 'm' => 'Herr' ) , 'validate' => true );
$arr['field']['title'] = array ( 'class' => 'three wide' , 'tab' => '1' , 'type' => 'input' , 'label' => 'Titel' );
$arr['field']['firstname'] = array ( 'class' => 'four wide' , 'tab' => '1' , 'type' => 'input' , 'label' => 'Vorname' , 'validate' => true , 'placeholder' => 'Max' );
$arr['field']['secondname'] = array ( 'class' => 'five wide' , 'tab' => '1' , 'type' => 'input' , 'label' => 'Nachname' , 'validate' => true , 'placeholder' => 'Muster' );
$arr['field'][] = array ( 'tab' => '1' , 'type' => 'div_close' );
$arr['field']['email'] = array ( 'tab' => '1' , 'type' => 'input' , 'label' => 'Email' , 'validate' => true , 'placeholder' => 'deine@email.at' );

$arr['field'][] = array ( 'tab' => '1' , 'type' => 'div' , 'class' => 'two fields' );
$arr['field']['company_1'] = array ( 'tab' => '1' , 'type' => 'input' , 'label' => 'Firma (Optional)' );
$arr['field']['company_2'] = array ( 'tab' => '1' , 'type' => 'input' , 'label' => 'Firma(Zusatz)' );
$arr['field'][] = array ( 'tab' => '1' , 'type' => 'div_close' );

$arr['field']['street'] = array ( 'tab' => '1' , 'type' => 'input' , 'label' => 'Strasse' );
$arr['field'][] = array ( 'tab' => '1' , 'type' => 'div' , 'class' => 'three fields' );
$arr['field']['zip'] = array ( 'tab' => '1' , 'type' => 'input' , 'label' => 'PLZ' );
$arr['field']['city'] = array ( 'tab' => '1' , 'type' => 'input' , 'label' => 'Stadt' );
$arr['field']['country'] = array ( 'tab' => '1' , 'type' => 'dropdown' , 'array' => 'country' , 'label' => 'Land' , 'value' => 'at' );
$arr['field'][] = array ( 'tab' => '1' , 'type' => 'div_close' );

$arr['field'][] = array ( 'tab' => '1' , 'type' => 'div' , 'class' => 'three fields' );
$arr['field']['tel'] = array ( 'tab' => '1' , 'type' => 'input' , 'label' => 'Tel' );
$arr['field']['web'] = array ( 'tab' => '1' , 'type' => 'input' , 'label' => 'Internetadresse' );
$arr['field'][] = array ( 'tab' => '1' , 'type' => 'div_close' );

if ($_SESSION['admin_modus']) {
	$path = '../ssi_smart/gadgets/map/ajax/form_ordertree2.php';
}
else {
	$path = '../ajax/form_ordertree2.php';
}

$arr['form'] = array ( 'action' => "$path" , 'id' => 'form_edit' , 'inline' => 'list' );
$arr['ajax'] = array (  'success' => "$('.modal.ui') . modal ( 'hide' ); form_tree_save('{$_POST['tree_id']}',data); after_loadMap();" ,  'dataType' => "html" );

$arr['button']['submit'] = array ( 'value' => "<i class='save icon'></i>Antrag stellen" , 'color' => 'blue' );
$arr['button']['close'] = array ( 'value' => 'Abbrechen' , 'color' => 'gray' ,  'js' => "$('.modal.ui') . modal ( 'hide' ); " );

$output = call_form ( $arr );
echo $output['html'];
echo $output['js'];