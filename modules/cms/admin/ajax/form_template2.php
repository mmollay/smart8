<?
/*
 * TRUNCATE `smart_content`;
 * TRUNCATE `smart_id_layer2id_page`;
 * TRUNCATE `smart_id_layer2id_site`;
 * TRUNCATE `smart_id_site2id_page`;
 * TRUNCATE `smart_langLayer`;
 * TRUNCATE `smart_langSite`;
 * TRUNCATE `smart_layer`;
 * TRUNCATE `smart_layout`;
 * TRUNCATE `smart_page`;
 * TRUNCATE `smart_site`;
 * TRUNCATE `smart_explorer`;
 * TRUNCATE `smart_gadget_button`;
 * TRUNCATE `smart_gadget_guestbook`;
 * TRUNCATE `smart_formular`;
 * /*
 * Generate Pages "smart-kit
 * New 01.06.2014
 * UPDATE 15.04.2017 - Add splitter_layer_id
 * UPDATE 20.04.2017 - überschreiben der Werte in layer_options (Bsp.: guestbook, Buttons)
 */

// Datenbankverbindung herstellen
include_once ('../../../login/config_main.inc.php');

$page_id = $_SESSION ['smart_page_id'];
$user_id = $_SESSION ['user_id'];
$user_page_path = $_SESSION ['path_template'];

$replace_template_id = $GLOBALS ['mysqli']->real_escape_string ( $_POST ['template_id'] );
$title = $GLOBALS ['mysqli']->real_escape_string ( $_POST ['template_title'] );
$text = $GLOBALS ['mysqli']->real_escape_string ( $_POST ['template_text'] );
$url = $GLOBALS ['mysqli']->real_escape_string ( $_POST ['template_url'] );
$set_public = $GLOBALS ['mysqli']->real_escape_string ( $_POST ['set_public'] );

if (isset ( $_POST ['propose_public'] ))
	$propose_public = 1;

/**
 * **************************************************************************************************
 * Aktuelle Daten werden aus der Datenbank ausgelesen umgeformt und wieder in einem txt abgespeichert
 * ***************************************************************************************************
 */
$mysql_query2 = $GLOBALS ['mysqli']->query ( "SELECT site_id FROM smart_id_site2id_page WHERE page_id='$page_id' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
while ( $array2 = mysqli_fetch_array ( $mysql_query2 ) ) {
	$set_site_id = $array2 ['site_id'];
	$mysql_add_site .= "OR site_id = $set_site_id ";
	$mysql_add_fk_site .= "OR fk_id= $set_site_id ";
	// Layer fuer einzelne Seiten setzen

	// Layere auslesen

	$mysql_query2_2 = $GLOBALS ['mysqli']->query ( "SELECT layer_id FROM smart_layer WHERE site_id='$set_site_id' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	while ( $array2_2 = mysqli_fetch_array ( $mysql_query2_2 ) ) {
		$set_layer_id = $array2_2 ['layer_id'];
		$mysql_add_layer .= "OR layer_id = $set_layer_id ";
		$mysql_add_fk_layer .= "OR fk_id= $set_layer_id ";
		$mysql_add_option_layer .= "OR element_id= $set_layer_id ";
	}

	// $mysql_query2_1 = $GLOBALS['mysqli']->query("SELECT layer_id FROM smart_id_layer2id_site WHERE site_id='$set_site_id' ") or die(mysqli_error($GLOBALS['mysqli']));
	// while ($array2_1 = mysqli_fetch_array($mysql_query2_1)) {
	// $set_layer_id = $array2_1['layer_id'];
	// $mysql_add_layer .= "OR layer_id = $set_layer_id ";
	// $mysql_add_fk_layer .= "OR fk_id= $set_layer_id ";
	// $mysql_add_option_layer .= "OR element_id = $set_layer_id ";
	// }
}

// for header and bottom
$mysql_query4 = $GLOBALS ['mysqli']->query ( "SELECT layer_id FROM smart_layer WHERE page_id='$page_id' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
while ( $array4 = mysqli_fetch_array ( $mysql_query4 ) ) {
	$set_layer_id = $array4 ['layer_id'];
	$mysql_add_layer .= "OR layer_id = $set_layer_id ";
	$mysql_add_fk_layer .= "OR fk_id= $set_layer_id ";
	$mysql_add_option_layer .= "OR element_id= $set_layer_id ";
}

// Layer auslesen
// $mysql_query3 = $GLOBALS['mysqli']->query("SELECT layer_id FROM smart_id_layer2id_page WHERE page_id='$page_id' ") or die(mysqli_error($GLOBALS['mysqli']));
// while ($array3 = mysqli_fetch_array($mysql_query3)) {
// $set_layer_id = $array3['layer_id'];
// $mysql_add_layer .= "OR layer_id = $set_layer_id ";
// $mysql_add_fk_layer .= "OR fk_id= $set_layer_id ";
// $mysql_add_option_layer .= "OR element_id= $set_layer_id ";
// }

/**
 * ****************************WICHTIGE INFO*****************************************
 * Reihenfolge beachten, da Abhängigkeiten zueinander bestehen
 * Bsp.: smart_site muss vor smart_layer erzeugt werden, damit die ID zugewiesen werden kann
 * cp_db (
 * 'smart_site', .........................Name des Tables
 * $mysql_add_site, ......................WHERE - also welche der Zeilen
 * array ( 'site_id' => 'new_site_id' ), ..Zuweisung als wenn neues ID (anders Bsp.) 'splitter_layer_id' zu 'layer_id' dann wird layer_id wert gesetzt
 * site_id ................................Orderby (Wird benötigt, wenn ID als Parent in dem gleichen Table verwendet wird (Bsp.: splitter_layer_id)
 *
 * *********************************************************************************
 */
// Seiten erzeugen
$template_sql .= cp_db ( 'smart_site', $mysql_add_site, array ('site_id' => 'new_site_id' ) );

$template_sql .= cp_db ( 'smart_langSite', $mysql_add_fk_site, array ('fk_id' => 'site_id' ) );

// Hauptseite
$template_sql .= cp_db ( 'smart_page', "OR page_id='$page_id'", array ('page_id' => 'new_page_id','user_id','index_id' => 'site_id','TrackingCode' => 'empty' ) ); // erstellung db.sql

// Layout
$template_sql .= cp_db ( 'smart_layout', "OR page_id='$page_id'", array ('layout_id' => 'new_layout_id','page_id' ) );

// Content
$template_sql .= cp_db ( 'smart_content', "OR page_id='$page_id'", array ('page_id','layout_id' ) );

// Gästebuch
$template_sql .= cp_db ( 'smart_gadget_guestbook', "OR page_id='$page_id'", array ('guestbook_id' => 'new_guestbook_id','page_id' ) );

// Layer
$template_sql .= cp_db ( 'smart_layer', $mysql_add_layer, array ('layer_id' => 'new_layer_id','site_id','page_id','splitter_layer_id' => 'layer_id' ), "splitter_layer_id" );

$template_sql .= cp_db ( 'smart_langLayer', $mysql_add_fk_layer, array ('fk_id' => 'layer_id' ), "fk_id" );

// Layer_options
$template_sql .= cp_db ( 'smart_element_options', $mysql_add_option_layer, array ('option_id','element_id' ) );

// Explorer
$template_sql .= cp_db ( 'smart_explorer', "OR page_id='$page_id'", array ('user_id','page_id' ) );

// Formular
$template_sql .= cp_db ( 'smart_formular', $mysql_add_layer, array ('field_id' => 'new_field_id','layer_id' ) );
// Button
$template_sql .= cp_db ( 'smart_gadget_button', $mysql_add_layer, array ('button_id' => 'new_button_id','layer_id','url' => 'site_id' ) );

// Layerverlinkung
$template_sql .= cp_db ( 'smart_id_site2id_page', $mysql_add_site, array ('site_id','page_id','layout_id' ) );

$template_sql .= cp_db ( 'smart_id_layer2id_page', "OR page_id='$page_id' ", array ('layer_id','page_id' ) );
$template_sql .= cp_db ( 'smart_id_layer2id_site', $mysql_add_site, array ('layer_id','site_id' ) );

// Set Template online - Option is just for "Superusers"
/*
 * if (in_array($_SESSION['login_user_id'],$_SESSION['template_useruser']) and $_POST['set_public']) $set_public = 1;
 * else $set_public = 0;
 */

if ($replace_template_id == 'new') $replace_template_id = NULL;

$stmt = $GLOBALS['mysqli']->prepare("REPLACE INTO smart_templates
                                        SET template_id=?,
                                            title=?,
                                            text=?,
                                            url=?,
                                            user_id=?,
                                            set_public=?,
                                            propose_public=?");
$stmt->bind_param("isssiii", $replace_template_id, $title, $text, $url, $user_id, $set_public, $propose_public);
$stmt->execute();
$template_id = $stmt->insert_id;
$stmt->close();

if ($_POST ['set_public'])
	$user_page_path_folder = "$user_page_path" . "public/$template_id";
else
	$user_page_path_folder = "$user_page_path" . "private/$template_id";

// Wenn Vorlage überschrieben werden soll wird der Folder vorher entfernt
if ($replace_template_id == $template_id) {
	exec ( "rm -rf $user_page_path_folder" );
}

// Generate a new Folder
exec ( "mkdir ../../../templates" );
exec ( "mkdir ../../../templates/smart" );
exec ( "mkdir $user_page_path" );
exec ( "mkdir $user_page_path/private" );
exec ( "mkdir $user_page_path/public" );
exec ( "mkdir $user_page_path_folder" );

// Copy from Explorer
exec ( "cp -rf ../../.." . $_SESSION ['path_user'] . "user$user_id/explorer/$page_id/ $user_page_path_folder/explorer" );

$fp = fopen ( "$user_page_path_folder/mysql.txt", "w" );
fwrite ( $fp, "$template_sql" );
fclose ( $fp );

echo "ok";

// Vorlagen und Sicherung
function cp_db($table_name, $sql_select, $array_replace, $order = false) {
	$user_id = $GLOBALS ['user_id'];
	$page_id = $GLOBALS ['page_id'];
	// Call Array for SELECT-Generator
	$column_array = mysql_columns ( $table_name );

	// Generate Add for the Insert
	foreach ( $column_array as $column_name ) {
		if ($wert_name)
			$zusatz_komma1 = ",";
		$wert_name .= "$zusatz_komma1`$column_name`";
	}
	if ($order)
		$orderby = " order by $order";
	else
		$orderby = '';
	$ausgabe_sql1 = "\nINSERT INTO $table_name($wert_name) VALUES" . "";
	$sql = "SELECT * from $table_name WHERE 0 $sql_select $orderby";

	$ausgabe = $GLOBALS ['mysqli']->query ( $sql ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	while ( $spalte = mysqli_fetch_array ( $ausgabe ) ) {
		foreach ( $column_array as $wert ) {
			// Uebergabe 2 dimesionaler arrays
			if ($array_replace [$wert]) {
				if ($array_replace [$wert] == 'empty')
					$wert_value = '';
				else
					$wert_value = "%%" . $array_replace [$wert] . ">" . $spalte [$wert];
			} elseif (in_array ( $wert, $array_replace )) {
				$wert_value = "%%" . $wert . ">" . $spalte [$wert];
			} else
				$wert_value = $spalte [$wert];

			if ($wert_repl)
				$zusatz_ausgabe_komma2 = ",";
			else
				$zusatz_ausgabe_komma2 = "";
			$wert_value = addSlashes ( $wert_value );
			$wert_value = preg_replace ( "/\r|\n/s", "", $wert_value );
			// echo "/user$user_id/explorer/$page_id";
			$wert_value = preg_replace ( "[user$user_id/explorer/$page_id]", "[%page_path%]", $wert_value );
			$wert_value = preg_replace ( "#guestbook_id=(.*)#Uis", 'guestbook_id=%%guestbook_id>\1', $wert_value );
			$wert_repl .= "$zusatz_ausgabe_komma2" . "'$wert_value'";
		}

		if ($zusatz_komma3)
			$zusatz_ausgabe_komma3 = ",";
		$ausgabe_sql .= $ausgabe_sql1 . "($wert_repl);";
		// $ausgabe_sql .= "$zusatz_ausgabe_komma3\n"."($wert_repl)";
		$wert_repl = "";
		$zusatz_komma3 = 1;
	}

	if ($ausgabe_sql) {
		$ausgabe_sql = "\n" . $ausgabe_sql;
		// $ausgabe_sql = "\n"."\n"."#[$table_name]#\n"."$ausgabe_sql";
		// $ausgabe_sql = "\n"."\n"."#[$table_name]#\n"."$ausgabe_sql1"."$ausgabe_sql".";";
		return $ausgabe_sql;
	}
}
?>