<?php

/*
 * REMOVE 21_CHALLENGE
 */
function del_challenge($user_id) {
	$sql[] = "DELETE FROM 21_groups WHERE user_id = '$user_id' "; // DOMAIN
	
	$query_challenge_id = $GLOBALS['mysqli']->query ( "SELECT challange_id FROM 21_groups where user_id = '$user_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	while ( $array_challenge_id = mysqli_fetch_array ( $query_challenge_id ) ) {
		$challenge_id = $array_challenge_id['challange_id'];
		$sql[] = "DELETE FROM 21_sessions WHERE group_id = '$challenge_id' ";
		$sql[] = "DELETE FROM 21_comment WHERE challenge_id = '$challenge_id' ";
		$sql[] = "DELETE FROM 21_like WHERE challenge_id = '$challenge_id' ";
	}
	return $sql;
}

/*
 * REMOVE HOLE PAGE
 */
function del_page($page_id) {
	$sql[] = "DELETE FROM ssi_company.domain WHERE page_id = '$page_id' AND user_id != '{$_SESSION['user_id']}'"; // Page
	$sql[] = "DELETE FROM smart_page WHERE page_id = '$page_id' "; // Page
	$sql[] = "DELETE FROM smart_layout WHERE page_id = '$page_id' "; // Layout
	$sql[] = "DELETE FROM smart_content WHERE page_id = '$page_id'"; // Content
	$sql[] = "DELETE FROM smart_explorer WHERE page_id = '$page_id'"; // Content
	$sql[] = "DELETE FROM smart_gadget_guestbook WHERE page_id = '$page_id' LIMIT 1";
	$sql[] = "DELETE FROM log_change_site WHERE page_id = '$page_id'"; //Logfiles
	$sql[] = "DELETE FROM smart_options WHERE page_id = '$page_id'"; //Logfiles
	
	$query_site_id = $GLOBALS['mysqli']->query ( "SELECT site_id FROM smart_id_site2id_page where page_id = '$page_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	while ( $array_site_id = mysqli_fetch_array ( $query_site_id ) ) {
		$site_id = $array_site_id['site_id'];
		$sql = del_site ( $site_id, $sql );
	}
	return $sql;
}

/*
 * Remove SITE
 */
function del_site($site_id, $sql = false) {
	
	// Del - Lang Elemente
	$sql[] = "DELETE FROM smart_site WHERE site_id = '$site_id'";
	// Del - Lang Elemente
	$sql[] = "DELETE FROM smart_langSite WHERE fk_id = '$site_id'";
	// Del - Verknuepfung
	$sql[] = "DELETE FROM smart_id_site2id_page WHERE site_id = '$site_id' ";
	// Del - Options
	$sql[] = "DELETE FROM smart_content WHERE site_id = '$site_id'";
	
	$sql[] = "DELETE FROM smart_layer WHERE site_id = '$site_id'";
	
	$mysql_query2 = $GLOBALS['mysqli']->query ( "
			SELECT * FROM smart_layer,smart_langLayer
			WHERE smart_layer.layer_id=smart_langLayer.fk_id
			AND site_id='$site_id'
			" );
	while ( $array = mysqli_fetch_array ( $mysql_query2 ) ) {
		$layer_id = $array['layer_id'];
		// $gadget = $array['gadget'];
		// $gadget_id = $array['gadget_id'];
		
		$sql = del_layer ( $layer_id, $sql );
	}
	
	//Löscht Seite aus dem Verzeichnis (damit diese nicht mehr im Netz gefunden werden kann
	exec("rm ");
	
	return $sql;
}

/*
 * Remove LAYER
 */
function del_layer($layer_id, $sql = false) {
	$query = $GLOBALS['mysqli']->query ( "SELECT gadget,gadget_id FROM smart_layer WHERE layer_id = '$layer_id '" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$array = mysqli_fetch_array ( $query );
	$gadget = $array['gadget'];
	$gadget_id = $array['gadget_id'];
	
	// REMOVE - GALLERY and txt
	/*
	 * if ($gadget == 'gallery') {
	 * $sql[] = "DELETE FROM smart_gadget WHERE gadget_id = '$gadget_id' ";
	 * //$file = $_SESSION['path_id_user']."gallery/".$gadget_id.".txt";
	 * //exec("rm $file");
	 * }
	 */
	$sql[] = "DELETE FROM smart_layer WHERE layer_id = '$layer_id' LIMIT 1";
	$sql[] = "DELETE FROM smart_langLayer WHERE fk_id = '$layer_id' LIMIT 1";
	$sql[] = "DELETE FROM smart_id_layer2id_page WHERE layer_id = '$layer_id' LIMIT 1";
	$sql[] = "DELETE FROM smart_id_layer2id_site WHERE layer_id = '$layer_id' LIMIT 1";
	$sql[] = "DELETE FROM smart_formular WHERE layer_id = '$layer_id' ";
	$sql[] = "DELETE FROM smart_element_options WHERE element_id = '$layer_id' ";
	$sql[] = "DELETE FROM smart_gadget_button WHERE layer_id = '$layer_id'";
	$sql[] = "DELETE FROM smart_gadget_ticker WHERE layer_id = '$layer_id'";
	
	return $sql;
}

// Ruft alle Layer in einer Struktur auf und gibt in einem Array den Wert zurück
// Wird zum löschen von Splittern verwendet
function call_splitter_sturcture($layer_id, $array_layer = false) {
	$query = $GLOBALS['mysqli']->query ( "SELECT layer_id FROM smart_layer WHERE splitter_layer_id = '$layer_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	while ( $array = mysqli_fetch_array ( $query ) ) {
		$array_layer[] = $array['layer_id'];
		call_splitter_sturcture ( $array['layer_id'], $array_layer );
	}
	return $array_layer;
}

?>