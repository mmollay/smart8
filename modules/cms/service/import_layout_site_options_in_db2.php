<?php
/*******************************************************************************
 * Übertragen der Setting von Array in db für neue Ooptionsverwaltung
 * *****************************************************************************
 */


include ("../../login/config_main.inc.php");

echo "Seiten-Layout Option import<hr>";

$sql_query = "SELECT layout_array,site_id,page_id from smart_id_site2id_page";

$sql = $GLOBALS ['mysqli']->query ( $sql_query ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
while ( $array = mysqli_fetch_array ( $sql ) ) {
	$value_output = '';
	$site_id = $array ['site_id'];
	$page_id = $array ['page_id'];
	$layout_array = $array ['layout_array'];
	$layout_array_n = explode ( "|", $layout_array );

	foreach ( $layout_array_n as $array ) {

		$array2 = preg_split ( "[=]", $array, 2 );
		$id = $array2 [0];
		$value = $array2 [1];
		if ($value and $id) {
			$value_output .= "$site_id:   {$array2[0]} = {$array2[1]} <br>";
			save_smart_option ( array ($id => $value ), $page_id, $site_id );
		}
	}
	if ($value_output)
		echo "Page_id = $page_id <br>$value_output<hr>";
}