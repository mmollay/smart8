<?php
/*
 * Abruf einer Datei, fuer neu angelegten Baum
 */
include ('../mysql_map.inc.php');

$tree_id = $GLOBALS['mysqli']->real_escape_string ( $_POST['tree_id'] );

$query = $GLOBALS['mysqli']->query ( "SELECT * from tree 
		LEFT JOIN client ON client.client_id = tree.client_faktura_id 
		LEFT JOIN (tree_template,tree_template_lang) ON tree.plant_id = tree_template.temp_id AND  tree_template_lang.temp_id = tree_template.temp_id 
		LEFT JOIN (tree_group_lang) ON tree_group_lang.group_id = tree_template.group_id AND tree_group_lang.lang = 'de' 
		WHERE  tree_id='$tree_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
// lang='$map_lang' AND

$array = mysqli_fetch_array ( $query );

/*
 * Content fuer das Window
 */

if (! $array['search_sponsor'] && ! $array['client_faktura_id']) {
	$data_window .= "<table border=0 style='width:100%' class='ui very basic collapsing compact celled table'>";
	$data_window .= "<tr><td>";
	$data_window .= "<div class='ui label green basic' style='width:100%'>Baumnummer: $tree_id</div><br><br>";
	$data_window .= "<span class=window_info_text>Dieser Baum ist vergeben. <br>Details folgen!</span><br><br>";
	// $data_window .= "$button_cart";
	$data_window .= "</td><td>";
	$data_window .= "<img src='https://www.obststadt.at/gadgets/map/img/logo.png' width=60 style='float:right;'>";
	$data_window .= "</td></tr></table>";
} elseif ($array['sponsor_progress']) {
	$data_window .= "<table border=0 style='width:100%' class='ui very basic collapsing compact celled table'>";
	$data_window .= "<tr><td>";
	$data_window .= "<div class='ui label green basic' style='width:100%'>Baumnummer: $tree_id</div><br><br>";
	$data_window .= "<span class=window_info_text>Ich bin noch zu haben!</span><br><br>";
	// $data_window .= "$button_cart";
	$data_window .= "<button class='button green ui mini' title='Baumpate werden' onclick=form_sponsing_mask($tree_id)>Baumpate werden</button>";
	$data_window .= "</td><td>";
	$data_window .= "<img src='https://www.obststadt.at/gadgets/map/img/logo.png' width=60 style='float:right;'>";
	$data_window .= "</td></tr></table>";
} elseif ($array['search_sponsor']) {
	// Sponsor gesucht
	$data_window .= "<table border=0 style='width:100%' class='ui very basic collapsing compact celled table'>";
	
	if ($_SESSION['mapcart'][$tree_id]) {
		$button_cart .= " <button onclick=cart_open()>Baum ist bereits im Warenkorb</button>";
	} elseif (! $_SESSION['client_user_id']) {
		$button_cart .= " <button class='button green ui mini' onclick=form_sponsing_mask($tree_id)>Baumpate werden</button>";
	} else
		$button_cart .= " <button class='button green ui mini' onclick=form_sponsing_mask($tree_id)>Baumpate werden</button>"; // form_sponsing_mask
	
	$data_window .= "<tr><td>";
	$data_window .= "<div class='ui label green basic' style='width:100%'>Baumnummer: $tree_id</div><br><br>";
	$data_window .= "<span class=window_info_text>Ich bin noch zu haben!</span><br><br>";
	// $data_window .= "$button_cart";
	$data_window .= "<button class='button green ui mini' title='Baumpate werden' onclick=form_sponsing_mask($tree_id)>Baumpate werden</button>";
	$data_window .= "</td><td>";
	$data_window .= "<img src='https://www.obststadt.at/gadgets/map/img/logo.png' width=60 style='float:right;'>";
	$data_window .= "</td></tr></table>";
} /*
   * Ausgaben des Baumpaten
   * im internen Bereich kann man ueber den Bearbeitungsmodus die Baumpaten direkt bearbeiten
   */

else {
	
	$data_window = "<div class='infowindow' style='min-width:350px' >";
	
	// Überschrift
	$data_window .= "<h3 class='ui header green'>";
	if ($array['title'])
		$data_window .= "" . $array['title'];
	if ($array['fruit_type'])
		$data_window .= " `{$array['fruit_type']}`";
	if ($array['latin']) {
		$data_window .= "<i>{$array['latin']}</i>";
	}
	$data_window .= "</h3>";
	
	if ($name != $array['tree_panel'] and $array['company_1'] != $array['tree_panel']) {
		// $data_window .= "<tr><td colspan=2><div class='message ui'>{$array['tree_panel']}<br></div></td></tr>";
		$data_window .= "<div class='message ui'>{$array['tree_panel']}<br></div>";
	}
	
	if ($array['wiki']) {
		$wiki = "<a href='{$array['wiki']}' title='Wikipedia' class=wiki><img src='$path" . "gadgets/map/images/wikipedia.png' height=20 width=20></a>";
	}
	
	$data_window .= "<table border=0 style='width:100%' class='ui very basic collapsing compact celled table'>";
	
	if ($array['tree_rm_reason'])
		$data_window .= "<tr><td><b>Status:</b></td><td>{$array['tree_rm_reason']} († {$array['tree_rm_date']})</td></tr>";
	
	if ($array['tree_id']) {
		$data_window .= "<tr><td><b>Pflanznummer:</b></td><td><b> {$array['tree_id']}</b></td></tr>";
	}
	if ($array['plant_date']) {
		$data_window .= "<tr><td><b>Pflanzdatum:</b></td><td> {$array['plant_date']}</td></tr>";
	}
	if ($array['ripe_for_picking']) {
		$data_window .= "<tr><td><b>Pfl&uuml;ckreife:</b></td><td> {$array['ripe_for_picking']}</td></tr>";
	}
	if ($array['mature_pleasure']) {
		$data_window .= "<tr><td><b>Genussreife:</b></td><td> {$array['mature_pleasure']}</td></tr>";
	}
	if ($array['taste']) {
		$data_window .= "<tr><td><b>Geschmack:</b></td><td> {$array['taste']}</td></tr>";
	}
	if ($array['worth_knowing'] or $wiki) {
		$data_window .= "<tr><td><b>Wissenswertes:</b></td><td> {$array['worth_knowing']} $wiki</td></tr>";
	}
	
	if ($array['firstname'] or $array['secondname']) {
		$name = $array['firstname'] . " " . $array['secondname'];
	}
	
	// $data_window .= "<tr><td colspan=2></td></tr>";
	if ($array['baum_pate'])
		$name = $array['baum_pate'];
	
	if ($name) {
		$data_window .= "<tr><td><b>Baumpate:</b></td><td>$name</td></tr>";
	}
	
	if ($array['company_1'] != $name) {
		$data_window .= "<tr><td><b>Firma/Organisation:&nbsp;</b></td><td>{$array['company_1']}</td></tr>";
	}
	
	if ($array['web']) {
		$data_window .= "<tr><td>&nbsp;</td><td> <a target='new' href='http://{$array['web']}'>" . $array['web'] . "</a></td></tr>";
	}
	// if ($name != $array['tree_panel'] and $array['company_1'] != $array['tree_panel']) {
	// //$data_window .= "<tr><td colspan=2><div class='message ui'>{$array['tree_panel']}<br></div></td></tr>";
	// $data_window .= "<div class='message ui'>{$array['tree_panel']}<br></div>";
	// }
}

$data_window .= "</table>";

$data_window .= "<div align='center'>";

if ($_SESSION['ssi_map_admin_modus']) {
	
	// $data_window .= "<div class='ui buttons mini icon' >";
	// if ($_SESSION['user_id'] == '40')
	$data_window .= "<div class='button red mini  ui' href='' onclick=call_semantic_form('$tree_id','modal_form_delete_tree','../ssi_map/ajax/form_delete.php','tree_remove','') ><i class='icon trash'></i>Löschen</div>";
	$data_window .= "<div class='button blue ui mini' href='' onclick=edit_tree('$tree_id')  id='$tree_id'><i class='edit icon'></i>Bearbeiten</div>";
	
	// $data_window .= "</div>";
	$data_window .= "<div class='ui modal small' id='modal_form_delete_tree'><div class='header'>Baum wirklich löschen?</div><div class='content'></div></div>";
}

$data_window .= "<div class='button ui mini' href='' onclick='callInfoWindow_close()'  id='$tree_id'><i class='close icon'></i>Schließen</div>";

$data_window .= "</div>";

echo $data_window;