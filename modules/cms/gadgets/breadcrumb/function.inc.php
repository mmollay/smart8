<?php
// $sql = $GLOBALS['mysqli']->query ( "SELECT * from smart_page WHERE page_id = {$_SESSION['smart_page_id']}" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
// $array = mysqli_fetch_array ( $sql );
// $GLOBALS ['index_id'] = $array ['index_id'];

function fu_ausgabe_navigpfad($set_id, $ii = 0) {
	$ii ++;

	if ($set_id) {
		// Auslesen der id aus der Navigstruktur
		$sql = "SELECT * FROM smart_id_site2id_page, smart_langSite WHERE site_id = fk_id AND site_id = '$set_id' AND lang = '{$_SESSION['page_lang']}' ";
		$query = $GLOBALS['mysqli']->query ( $sql );
		$array = mysqli_fetch_array ( $query );
		$parent_id = $array ['parent_id'];
		if (! $array ['menu_text'])
			$array ['menu_text'] = $array ['title'];
		if ($GLOBALS ['index_id'] != $array ['site_id'])
			$output = "<i style='padding-left:4px;' class='right chevron icon divider'></i><a href=\"#\" onclick=\"CallContentSite('".$array ['site_id']."')\">" . $array ['menu_text'] . "</a>";
	}

	if ($parent_id != $set_id)
		return fu_ausgabe_navigpfad ( $parent_id, $ii ) . $output;
}