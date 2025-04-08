<?
include_once (__DIR__ . '/../../../login/config_main.inc.php');
include_once (__DIR__ . '/../../config.inc.php');
include_once (__DIR__ . '/../../smart_form/include_form.php');

$arr ['value'] = call_smart_option ( $_SESSION ['smart_page_id'], $_POST ['update_id'] );

$arr ['field'] [] = array ('type' => 'div','class' => 'content ui message' );

$arr ['field'] [] = array ('type' => 'content','text' => "<i style='display:none; right:10px; position:absolute;' id='save_icon' class='icon green save'></i>" );

$arr ['field'] ['popup_modal_size'] = array ('type' => 'dropdown','label' => 'Fenstergröße','array' => array ('mini' => 'mini','tiny' => 'klein','small' => 'mittel','large' => 'groß' ) );

$arr ['field'] ['popup_modal_inverted'] = array ('type' => 'checkbox','label' => 'Fensterfarbe invertieren' );

$arr ['field'] ['popup_modal_scrolling'] = array ('type' => 'checkbox','label' => 'gesamte Bildschirmbreite (Scrolling)','info' => 'Ein Modal kann die gesamte Bildschirmgröße nutzen' );

$arr ['field'] ['popup_time'] = array ('type' => 'slider','label' => "<i class='icon expand arrows alternate'></i> Popup-Zeit",'min' => 5,'max' => 120,'step' => 1,'unit' => 'sec','value' => $textfield_div_margin,'info' => 'Wie lange das Popupfenster sichtbar sein soll' );

$arr ['field'] [] = array ('type' => 'div_close' );

$arr ['form'] = array ('id' => 'form_autopopup','inline' => 'list','size' => 'mini' );

$arr ['ajax'] = array ('success' => $success,'beforeSend' => "",'datatype' => 'html','onLoad' => " load_autosave_popup('{$_POST['update_id']}');" );

$arr ['hidden'] ['update_id'] = $_POST ['update_id'];
$arr ['hidden'] ['edit_layer_id'] = $_POST ['update_id'];
$arr ['hidden'] ['gadget'] = $gadget;

$output = call_form ( $arr );
echo $output ['html'];
echo $output ['js'];
echo $add_gadgets_js;
echo $add_other_form;