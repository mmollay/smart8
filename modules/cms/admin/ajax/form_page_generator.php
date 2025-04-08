<?
include ("../../../login/config_main.inc.php");
include ('../../smart_form/include_form.php');
include_once ('../../library/function_menu.php');

// Auflisten der dynamischen Seiten
$query = $GLOBALS['mysqli']->query ( "SELECT dynamic_name, site_id, title from smart_id_site2id_page, smart_langSite WHERE fk_id = site_id AND dynamic_site = 1 order by title" );
while ( $array = mysqli_fetch_array ( $query ) ) {
	$site_id = $array['site_id'];
	$dynamic_name = $array['dynamic_name'];
	if (! $array['dynamic_name'])
		$dynamic_name = $array['title'];
	$array_select_dynamic[$site_id] = "$dynamic_name($site_id)";
}

// Auslesen det Menüstruktur um einhängen der Seite
$menuData = generateMenuStructure ( $_SESSION['smart_page_id'] );
$array1 = buildMenuArray ( 0, $menuData, 0 );
if (! $array1)
	$array1 = array ();
$array1 += array ( 'end' => 'am Ende' );
$array_menu_values = $array1;

$arr['form'] = array ( 'action' => "admin/ajax/form_share_site2.php" );
$arr['field'][] = array ( 'label' => 'Dynamische Seite' , 'id' => 'site_dynamic_id' , 'type' => 'dropdown' , 'array' => $array_select_dynamic ,  'validate' => true );

$arr['field'][] = array ( 'type' => 'div' , 'class' => 'three fields' ); // 'label'=>'test'
$arr['field'][] = array ( 'id' => 'menu_text' , 'label' => 'Menüname' , 'type' => 'input' ,  'validate' => true );
$arr['field'][] = array ( 'id' => 'site_title' , 'label' => 'Seitenname' , 'type' => 'input' ,  'validate' => true );
$arr['field'][] = array ( 'id' => 'site_url' , 'label' => 'Url' , 'type' => 'input' ,  'validate' => true ,  'label_right' => '.html' );

// $arr['field'][] = array ( 'id' => 'menu_disable' , 'label' => 'Menüpunkt nicht anzeigen' , 'type' => 'checkbox' );
// $arr['field'][] = array ( 'id' => 'menu_position' , 'label' => 'Position' , 'type' => 'dropdown' , 'array' => $array_menu_values , 'value' => 'end' );
$arr['field'][] = array ( 'type' => 'div_close' );

// $form1->setField ( "menu_disable", array ( 'after' => menu_text , 'type' => 'checkbox' , 'title' =>'nicht sichtbar' ) );

$arr['hidden']['challenge_id'] = $_POST['update_id'];
$arr['buttons'] = array ( 'align' => 'center' );
$arr['button']['submit'] = array ( 'value' => 'Seite einhängen' , 'color' => 'blue' );
$arr['button']['close'] = array ( 'value' => 'Schließen' ,  'js' => "$('.modal.ui').modal('hide');" );

$output = call_form ( $arr );
echo $output['html'];
echo $output['js'];