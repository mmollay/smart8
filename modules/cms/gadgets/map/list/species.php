<?
$arr['mysql'] = array (
		'field' => 't1.temp_id temp_id,fruit_type,  t3.title title, COUNT(tree_id) count, t5.title taste' ,
		'table' => 'tree_template t1
			LEFT JOIN tree_template_lang t2 ON t1.temp_id = t2.temp_id
			LEFT JOIN tree_group_lang t3 ON t1.group_id = t3.group_id
			LEFT JOIN tree ON t1.temp_id = tree.plant_id
		    LEFT JOIN tree_taste_lang t5 ON t1.taste_id = t5.taste_id ' ,
		'order' => 'count desc, fruit_type' ,
		'where' => "$add_mysql" ,
		'group' => 't1.temp_id' ,
		'debug' => true,
		'like' => 'fruit_type, t3.title' ,
		'limit' => '20' );


// $arr['mysql'] = array ( 
// 		'field' => 't1.temp_id temp_id,fruit_type, tree_group, if(!t3.title,t3.title,tree_group) title, count(tree_id) count', 
// 		'table' => 'tree_template t1 
// 		INNER JOIN (tree_group_lang t3, tree_template_lang t2, tree) 
// 		ON t3.matchcode = t1.tree_group 
// 		AND t1.temp_id = t2.temp_id
// 		AND t1.temp_id = tree.plant_id ',
// 		'order' => 'count desc, fruit_type' ,  
// 		'where' => "AND t2.lang = 'de' $add_mysql" ,
// 		'group' => 't1.temp_id',
// 		'like' => 'fruit_type, t3.title',
// 		'limit' => '20'
// );

//$array_speciesgroup = call_sql_array ( "SELECT group_id,title FROM tree_group_lang WHERE lang = 'de' " );
//$arr['filter']['group_id'] = array ( 'type' => 'dropdown' , 'table'=>'t1', 'array' => $array_speciesgroup , 'placeholder' => '--Alle Gattungen--' );

$arr['list'] = array ( 'id' => 'map_sort' , 'width' => '100%' , 'size' => 'small' , 'class' => 'compact celled striped definition unstackable' ); // definition


$arr['th']['fruit_type'] = array ( 'title' =>"<i class='icon lemon'></i>Sorte" );
$arr['th']['title'] = array ( 'title' =>"<i class='pagelines icon'></i>Gattung" );
$arr['th']['taste'] = array ( 'title' =>"Geschmack" );
$arr['th']['count'] = array ( 'title' =>"Anzahl" );



