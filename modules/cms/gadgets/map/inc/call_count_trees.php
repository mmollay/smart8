<?php
include ('../mysql_map.inc.php');

// $count_array_trees = $GLOBALS['mysqli']->query ( "SELECT * from tree
// 		INNER JOIN (client,tree_template,tree_template_lang,tree_group_lang)
// 			ON client.client_id = tree.client_faktura_id
// 			AND tree_template_lang.temp_id = tree_template.temp_id
// 			AND tree_group_lang.matchcode = tree_template.tree_group
// 			AND tree.plant_id = tree_template.temp_id
// 			AND tree_group_lang.lang = 'de'
// 			WHERE 1 AND tree.trash = '0'
// 		$add_mysql " ) or die ( mysqli_error ($GLOBALS['mysqli']) );

$count_array_trees = $GLOBALS['mysqli']->query ( "SELECT * from tree 
		LEFT JOIN (client,tree_template) 
			ON client.client_id = tree.client_faktura_id 
			AND tree.plant_id = tree_template.temp_id
			WHERE 1 AND tree.trash = '0'
		$add_mysql " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$count_trees = mysqli_num_rows ( $count_array_trees );
echo $count_trees;