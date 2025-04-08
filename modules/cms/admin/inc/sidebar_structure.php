<?php
/**********************
 * Aufrufen der Struktur für Funnnel und für alle anderen Seiten
 **********************/

include_once ('../../../login/config_main.inc.php');
include_once (__DIR__ . '/../../library/function_menu.php');

$set = $_POST['set'];
if ($set != 'funnel') {
	$set = 'menu';
	include ('../../smart_form/include_form.php');
	$arr['form'] = array ('action' => "admin/ajax/edit_menu2.php" , 'id' => 'form_jstree_structure' , 'size' => 'small' );
	$arr['ajax'] = array (  'onLoad' => "" ,  'success' => "" ,  'dataType' => "html" );
	//$arr['field'][] = array ('type'=>'div', 'class'=>'fields');
	$arr['field']['hole_structure'] = array ( 'label' => 'Nur Menüstruktur' , 'type' => 'checkbox' , 'onchange' => "$('#form_jstree_structure').submit(); call_sitebar_content('$set')" , 'value' => $_SESSION['hole_structure'] );
	//$arr['field'][] = array ('class'=>'right floated', 'class_button'=>'icon','type'=>'button', value=>"<i class='icon refresh'></i>", 'onclick'=>"call_sitebar_content('$set')");
	//$arr['field'][] = array ('type'=>'div_close');
	$arr['field']['list_structure'] = array ( 'label' => 'Als Liste darstellen', 'info'=>'Wenn Knotenpunkte einer Struktur entfernt werden, können mit dieser Einstellung alle Seiten angezeigt werden' , 'type' => 'checkbox' , 'onchange' => "$('#form_jstree_structure').submit(); call_sitebar_content('$set')" , 'value' => $_SESSION['list_structure'] );
	
	$output = call_form ( $arr );
	$menuData = generateMenuStructureList ( $_SESSION['smart_page_id'], true );
	//echo "<pre>".var_export($menuData)."</pre>";
	//$menuData = generateLineaStructure ( $_SESSION[smart_page_id], true );
	$output_menu = buildMenuAdmin ( 0, $menuData );
	//$set_script = 'call_jstree_structure();';
	//$menu_id = 'menu_jstree';
} else {
	// find funnel ID
	$base_site_id = mysql_singleoutput ( "SELECT fk_id FROM smart_langSite LEFT JOIN smart_id_site2id_page ON site_id = fk_id WHERE page_id ='{$_SESSION['smart_page_id']}' AND site_url = 'funnel' " );
	$menuData = generateMenuStructure ( $_SESSION['smart_page_id'], true, $add_mysql = " AND funnel_id " );
	$output_menu = buildMenuFunnelAdmin ( $base_site_id, $menuData );
	
	//$set_script = 'call_jstree_amazon_structure();';
	//$set_script = 'call_jstree_structure();';
	//$menu_id = 'menu_amazon_jstree';
	//$menu_id = 'menu_jstree';
	//echo "<div align=right><div class='button icon ui' onclick='call_sitebar_content(\"$set\")'><i class='icon refresh'></i></div></div><br>";
}

echo "<div class='ui mini message'>";
echo $output['html'];
echo "<form id='s'>";
echo "<div class='ui fluid input'><input type='text' type='text' id='q' placeholder='Suchen...'><button type='button' onclick='call_sitebar_content(\"$set\")' class='ui icon button'><i class='icon refresh'></i></button></div>";
echo"</form>";
echo "</div>";

//echo "<form id='s' ><div class='ui icon input'><input type='search' id='q' placeholder='Search...' type='text'><i class='search link icon'></i></div></form>";
echo "<div id='container' class='tree_container' style='overflow:auto; overflow-x: hidden;'><div class='$set' id='menu_jstree'>$output_menu</div></div>";
echo $output['js'];
echo "<script>appendScript('admin/js/jstree_structure.js')</script>";
echo "<script>call_jstree_structure('$set');</script>";