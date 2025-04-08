<?php
include ("../../login/config_main.inc.php");

echo "Version - Change 7.0<hr>";
exit ();
/**
 * Menüs alle löschenu
 */
// $query_del = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_layer WHERE gadget = 'menu' " );
// while ( $array_del = mysqli_fetch_array ( $query_del ) ) {
// 	$GLOBALS['mysqli']->query ( "DELETE FROM smart_layer WHERE layer_id = {$array_del['layer_id']} " );
// 	$GLOBALS['mysqli']->query ( "DELETE FROM smart_layer WHERE fk_id = {$array_del['layer_id']} " );
// }

//neue Menüs anlegen
$query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_page " );
while ( $array = mysqli_fetch_array ( $query ) ) {	
	$page_id = $array['page_id'];
	//Prüft ob bereits angelegt wurde
	$query_check = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_layer WHERE page_id = '$page_id' AND gadget = 'menu' " ) or die( mysqli_error ($GLOBALS['mysqli'])  );
	if (! mysqli_num_rows ($query_check )) {
		
		// Anlegen von einem Layer
		$GLOBALS['mysqli']->query ( "INSERT INTO smart_layer SET
		page_id       = '$page_id',
		site_id       = '{$array['site_id']}',
	    position       = 'header2',
		gadget        = 'menu'
		" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		
		// Auslesen der Menu_ID
		$layer_id = mysqli_insert_id ( $GLOBALS['mysqli'] );
		
		// SprachLayer einspielen
		$GLOBALS['mysqli']->query ( "INSERT INTO smart_langLayer SET
		fk_id = '$layer_id',
		lang  = 'de' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		echo "<font color = green>Menue für Page $page_id angelegt</font><br>";
	}
	else 
		echo "<font color = grey>Menue für Page $page_id bereits angelegt</font><br>";
}

exit ();

// WURDE BEREITS AUSGEFÜHRT
change_smart_content2smart_layer ( 'smart_content_footer', 'footer' );
change_smart_content2smart_layer ( 'smart_content_footer2', 'footer' );
change_smart_content2smart_layer ( 'smart_content_header', 'header' );

echo "fertig";

// Auslesen der Seiten welche geändert werden sollen
$query = $GLOBALS['mysqli']->query ( "SELECT site_id FROM smart_id_site2id_page WHERE (split_representation = '' or split_representation = 'double') " );
while ( $array = mysqli_fetch_array ( $query ) ) {
	$site_id = $array['site_id'];
	// Anlegen eines neuen elementes (Splitter)
	$GLOBALS['mysqli']->query ( "INSERT INTO smart_layer SET
	site_id       = '$site_id',
	gadget        = 'splitter',
	position      = 'left',
	gadget_array  = 'column_relation=11'
	" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	$splitter_layer_id = $GLOBALS['mysqli']->insert_id ();
	
	// verschieben der layer in den Splitter
	$GLOBALS['mysqli']->query ( "UPDATE smart_layer SET splitter_layer_id = '$splitter_layer_id' WHERE site_id = '$site_id' AND layer_id != '$splitter_layer_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	// split_representation zurück setzen
	// $GLOBALS['mysqli']->query ("UPDATE smart_id_site2id_page SET split_representation = '' WHERE site_id = '$site_id' ") or die (mysqli_error());
	echo "$site_id - $splitter_layer_id - Fertig<br>";
}


function change_smart_content2smart_layer($id_old, $id_new) {
	$query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_content WHERE content_id = '$id_old' " );
	while ( $array = mysqli_fetch_array ( $query ) ) {
		
		// Anlegen von einem Layer
		$GLOBALS['mysqli']->query ( "INSERT INTO smart_layer SET
	matchcode     = '{$_POST['layer_matchcode']}',
	page_id       = '{$array['page_id']}',
	site_id       = '{$array['site_id']}',
	gadget        = 'textfield',
	position      = '$id_new'
	" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		
		// Auslesen der Menu_ID
		$layer_id = mysqli_insert_id ( $GLOBALS['mysqli'] );
		
		// SprachLayer einspielen
		$GLOBALS['mysqli']->query ( "INSERT INTO smart_langLayer SET
	fk_id = '$layer_id',
	lang  = 'de',
	text  = '{$array['content']}' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		
		echo "$layer_id<br>";
	}
}
