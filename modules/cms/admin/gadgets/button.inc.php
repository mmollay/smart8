<?php
$array_sites = GenerateArraySql ( "SELECT * FROM smart_langSite INNER JOIN smart_id_site2id_page ON smart_langSite.fk_id = smart_id_site2id_page.site_id and lang='{$_SESSION['page_lang']}' AND page_id='{$_SESSION['smart_page_id']}' order by timestamp desc ", 'title' ); // %var% die ausgegeben werden sol
                                                                                                                                                                                                                                                     // $arr['field']["button_url$i"] = array ( 'tab' => 'first' , 'label' => '' , 'type' => 'dropdown' , 'array' => $array_sites , 'value' => $button_url[$i] );                                                                                                                                                                                                                                                     // $arr['field']["button_link$i"] = array ( 'tab' => 'first' , 'label' => '' , 'label_left' => 'oder' , 'type' => 'input' , 'value' => $button_link[$i] , 'placeholder' => 'http://' )
$query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_gadget_button WHERE layer_id = '$update_id' order by sequence" );
while ( $button_array = mysqli_fetch_array ( $query ) ) {
	$i ++;
	$button_tooltip[$i] = $button_array['tooltip'];
	$button_text[$i] = $button_array['title'];
	$button_icon[$i] = $button_array['icon'];
	$button_color[$i] = $button_array['color'];
	$button_url[$i] = $button_array['url'];
	$button_link[$i] = $button_array['link'];
	$button_target[$i] = $button_array['target'];
}

if (! check_fixed_gadget ( $_SESSION['site_id'], $_POST['update_id'] ) or $layer_fixed) {
	$arr['field']['layer_fixed'] = array ( 'tab' => 'first' , 'type' => 'toggle' , 'label' => 'Feld fixieren' , 'info' => 'Feld wird am unteren Ende angeheftet' , 'value' => $layer_fixed );
}

$arr['field']['button_line'] = array ( 'tab' => 'first' , 'label' => 'Gruppiert' , 'type' => 'toggle' , 'value' => $button_line );
$arr['field']['no_fluid'] = array ( 'tab' => 'first' , 'label' => 'Nicht strecken' , 'type' => 'toggle' , 'value' => $no_fluid );
$arr['field']['button_cirular'] = array ( 'tab' => 'first' , 'label' => 'abgerundet' , 'type' => 'toggle' , 'value' => $button_cirular );
$arr['field']['button_size'] = array ( 'tab' => 'first' , 'label' => 'Größe' , 'type' => 'dropdown' , "array" => $array_size , 'value' => $button_size );
$arr['field']['align'] = array ( 'tab' => 'first' , 'label' => "Ausrichtung" , "type" => "select" , 'array' => array ( 'left' => 'links' , 'center' => 'mittig' , 'right' => 'rechts' ) , 'value' => $align );

for($i = 1; $i <= 4; $i ++) {
	
	if (! $set_active) {
		/* ACCORD1 */
		$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui accordion' , 'text' => "<div class='title active'><i class='icon dropdown'></i>Button $i</div>" );
		$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'content active' );
	} else {
		$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'text' => "<div class='title'><i class='icon dropdown'></i>Button $i</div>" );
		$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'content ui' );
	}
	$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui message' );
	
	$set_active = true;
	
	// $button_text[$i] = $GLOBALS["button_text$i"];
	// $button_color[$i] = $GLOBALS["button_color$i"];
	// $button_icon[$i] = $GLOBALS["button_icon$i"];
	// $button_url[$i] = $GLOBALS["button_url$i"];
	// $button_link[$i] = $GLOBALS["button_link$i"];
	
	// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'header' , 'text' => "Button $i" , 'class' => 'dividing' );
	// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'fields two' );
	$arr['field']["button_text$i"] = array ( 'tab' => 'first' , 'label' => "" , 'type' => 'input' , 'value' => $button_text[$i] , 'placeholder' => 'Buttontext' );
	$arr['field']["button_tooltip$i"] = array ( 'tab' => 'first' , 'label' => '' , 'type' => 'input' , 'value' => $button_tooltip[$i] , 'placeholder' => 'Popup-Info' );
	$arr['field']["button_icon$i"] = array ( 'tab' => 'first' , 'type' => 'icon' , 'value' => $button_icon[$i] );
	// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
	// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'fields two' );
	$arr['field']["button_color$i"] = array ( 'tab' => 'first' , 'label' => '' , 'type' => 'dropdown' , 'array' => 'color' , 'value' => $button_color[$i] , 'placeholder' => 'Farben' );
	$arr['field']["button_color_custorm$i"] = array ( 'tab' => 'first' , 'label' => "Farbe (Custorm)" , "type" => "color" );
	$arr['field']["button_url$i"] = array ( 'tab' => 'first' , 'class' => 'search' , 'label' => '' , 'type' => 'dropdown' , 'array' => $array_sites , 'value' => $button_url[$i] , 'placeholder' => 'zu Seite verlinken', 'clearable'=> true );
	$arr['field']["button_link$i"] = array ( 'tab' => 'first' , 'label' => '' , 'type' => 'input' , 'value' => $button_link[$i] , 'placeholder' => 'zu externen Link' );
	$arr['field']["button_target$i"] = array ( 'tab' => 'first' , 'label' => 'in neuer Seite öffnen' , 'type' => 'checkbox' , 'value' => $button_target[$i] );
	
	$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
	
	/* END-ACCORD1 */
	// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
	$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
}

/* END-ACCORD-Gesamt */

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

?>