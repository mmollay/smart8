<?
include_once ('../../../login/config_main.inc.php');
include_once (__DIR__ . '/../../library/function_menu.php');
include ('../../smart_form/include_form.php');

$arr ['form'] = array ('action' => "admin/ajax/edit_menu2.php",'id' => 'form_jstree_structure1','size' => 'small' );

$arr ['ajax'] = array ('onLoad' => "",'success' => "",'dataType' => "html" );

$arr ['field'] ['hole_structure'] = array ('class' => 'hole_structure','label' => 'Nur Menüstruktur anzeigen','info' => 'Zeigt nur Seiten an, die in der Menüstruktur sichtbar gemacht wurden.','type' => 'checkbox',
		'onchange' => "$('#form_jstree_structure1').submit(); reload_gadget_class('menu_field'); editMenuStructure();",'value' => $_SESSION ['hole_structure'] );

// $arr['field']['add_node'] = array(
//     'type' => 'button',
//     'text' => 'Neuen Knoten anlegen',
//     'onclick' => "jstree_create(); reload_gadget_class('menu_field');",
//     'value' => $_SESSION['hole_structure']
// );

$output = call_form ( $arr );

$menuData = generateMenuStructure ( $_SESSION ['smart_page_id'], true );
$output_menu = buildMenuAdmin1 ( 0, $menuData );

echo "<div class='ui message info mini'>
<i class='icon write'></i> Zum bearbeiten, mit 'rechten Mausklick' auf das gewünschte Feld klicken.<br>
<i class='icon arrows alternate'></i> Um die Struktur verändern, mit 'geklickter Maus', dass gewünschte Feld ziehen.
</div>";

echo $output ['html'];

// echo "<script type=\"text/javascript\" src=\"admin/js/jstree_structure.js\"></script>";

echo "<form id='s1' class='ui message'><div class='ui icon input'><input type='search' id='q1' placeholder='Search...' type='text'><i class='search link icon'></i></div></form>";
echo "<div id='container'><div id='menu_jstree_modal'>$output_menu</div></div>";
echo $output ['js'];
echo "<script>appendScript('admin/js/jstree_structure1.js')</script>";
echo "<script>call_jstree_structure1();</script>";
