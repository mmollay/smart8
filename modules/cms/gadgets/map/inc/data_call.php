<?php
include ('../mysql_map.inc.php');

if (! $j)
	$j = 0;

// CAll from function.js (client)
$client_id = $GLOBALS['mysqli']->real_escape_string ( $_POST['client_id'] );

// $GLOBALS['mysqli']->query("SET NAMES 'utf8'");
// $GLOBALS['mysqli']->query("SET CHARACTER SET 'utf8'");

// Liste aller Bauume zu Darstellung "array"

if (is_array ( $_SESSION["map_filter_fruit"] )) {
	foreach ( $_SESSION["map_filter_fruit"] as $key => $value ) {
		if ($value) {
			if ($add_mysql_group)
				$add_mysql_group .= " OR ";
			$add_mysql_group .= "tree_group.tree_group_id = '$key' ";
		}
	}
}

if ($add_mysql_group)
	$add_mysql_map .= " AND ( $add_mysql_group ) ";
// $query = $GLOBALS['mysqli']->query ( "SELECT
// latitude, longtitude, tree_group,tree_id,plant_date,search_sponsor,
// (SELECT date_booking FROM bill_details INNER JOIN bills ON bill_details.bill_id = bills.bill_id WHERE bill_detail_id = detail_id) date_booking
// FROM tree
// LEFT JOIN (client,tree_template,tree_template_lang,tree_group_lang)
// ON client.client_id = tree.client_faktura_id
// AND tree_template_lang.temp_id = tree_template.temp_id
// AND tree_group_lang.matchcode = tree_template.tree_group
// AND tree.plant_id = tree_template.temp_id
// AND tree_group_lang.lang = 'de'
// WHERE 1 $add_mysql $add_mysql_map AND tree.trash = '0'" ) or die ( mysqli_error ($GLOBALS['mysqli']) );

if ($_POST['set_admin'] != true) {}

$query = $GLOBALS['mysqli']->query ( "
SELECT latitude, longtitude, tree_group.matchcode,tree_id,plant_date,search_sponsor, tree_group_lang.title title
	FROM tree 
		LEFT JOIN (tree_template,client,tree_group,tree_group_lang) 
				ON tree_template.group_id = tree_group.tree_group_id
				AND tree.plant_id = tree_template.temp_id 
				AND client.client_id = tree.client_faktura_id
				AND tree_group_lang.group_id = tree_template.group_id
				AND tree_group_lang.lang = 'de'
				WHERE 1 AND tree.trash = '0' $add_mysql $add_mysql_map" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );

while ( $array = mysqli_fetch_array ( $query ) ) {
	
	// if ($_POST['set_admin'] == true or $array['matchcode'] or $array['search_sponsor']) {
	$tree_id = $array['tree_id'];
	$array_map[$j]['latLng'] = array ( $array['latitude'] , $array['longtitude'] );
	$array_map[$j]['tag'] = array ( 'trees' , $array['tree_group'] , 'client_' . $array['client_id'] );
	$array_map[$j]['id'] = $tree_id;
	
	
	$icon_url = 'https://center.ssi.at/ssi_smart/gadgets/map/icons';
	
	// Wenn Baumpate gesucht wird
	if ($array['search_sponsor']) {
		$array_map[$j]['options']['icon'] = "$icon_url/grey.png";
		$array_map[$j]['options']['title'] = "Baumpate gesucht";
	} elseif ($array['date_booking'] == '0000-00-00') {
		$array_map[$j]['options']['icon'] = "$icon_url/grey.png";
		$array_map[$j]['options']['title'] = "Baum noch nicht frei gegeben";
	} elseif ($array['plant_date'] == '0000-00-00') {
		$array_map[$j]['options']['icon'] = "$icon_url/grey.png";
		$array_map[$j]['options']['title'] = "Pflanzdatum noch nicht bekannt";
	} elseif (strtotime ( $array['plant_date'] ) > time ()) {
		// echo $array['plant_date'] ."-> ".(strtotime($array['plant_date']))."<". time()."<br> ";
		$array_map[$j]['options']['icon'] = "$icon_url/grey.png";
		$array_map[$j]['options']['title'] = "Baum wird gesetzt am {$array['plant_date']}";
	} elseif ($_SESSION['mapcart'][$tree_id]) {
		$array_map[$j]['options']['icon'] = "$icon_url/grey.png";
		$array_map[$j]['options']['title'] = "Baum im Warenkorb";
	} elseif ($array['matchcode']) {
		if (!is_file("../icons/" . $array['matchcode'] . ".png" )) {
		//if (!@getimagesize ( "$icon_url/" . $array['matchcode'] . ".png" )) {
			$array['matchcode'] = 'apple';
		}
		$array_map[$j]['options']['icon'] = "$icon_url/" . $array['matchcode'] . ".png";
		$array_map[$j]['options']['title'] = $array['title'];
	} else {
		$array_map[$j]['options']['icon'] = "$icon_url/grey.png";
		$array_map[$j]['options']['title'] = "Noch nicht definiert";
	}
	
	$group = $group_array_title[$array['tree_group']];
	$array_list[$array['tree_group']][$j]['tree_id'] = $array['tree_id'];
	$array_list[$array['tree_group']][$j]['title'] = $group;
	// $array_list[$array['tree_group']][$j][kind] = $array['fruit_type'];
	$j ++;
	// }
}

// Sorten auslesen
$query = $GLOBALS['mysqli']->query ( "
SELECT count(tree_id) count, tree_group.matchcode, tree_group, tree_group_id,tree_group_lang.title title FROM tree_group
	LEFT JOIN tree_group_lang  ON tree_group_lang.group_id = tree_group.tree_group_id  AND tree_group_lang.lang = 'de'
	LEFT JOIN tree_family_lang ON tree_family_lang.family_lang_id = tree_group.family_id
	LEFT JOIN (client,tree,tree_template) ON tree.plant_id = tree_template.temp_id AND tree_template.group_id = tree_group.tree_group_id AND client.client_id = tree.client_faktura_id  
			WHERE 1 AND tree.trash = '0'
			$add_mysql
			GROUP by tree_group.tree_group_id
			ORDER by count desc
" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
while ( $array2 = mysqli_fetch_array ( $query ) ) {
	$group = $array2['tree_group_id'];
	$matchcode = $array2['matchcode'];
	$array_group[$group]['count'] = $array2['count'];
	$array_group[$group]['group'] = $array2['title'];
	$array_group[$group]['color'] = $group_array[$matchcode]['color'];
	$array_group[$group]['background'] = $group_array[$matchcode]['background'];
}
