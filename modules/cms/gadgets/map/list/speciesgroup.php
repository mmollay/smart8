<?
$arr['mysql'] = array ( 'field' => 'tree_group_lang.group_id,count(tree_id) count, tree_group,tree_group_lang.title, family_id, tree_family_lang.name family_name' ,
		'table' => 'tree_group_lang INNER JOIN (client,tree_template,tree_template_lang,tree)
			ON client.client_id = tree.client_faktura_id
			AND tree_template_lang.temp_id = tree_template.temp_id
			
			AND tree.plant_id = tree_template.temp_id
			AND tree_group_lang.lang = "de"
			LEFT JOIN tree_group ON tree_group_lang.group_id = tree_group.tree_group_id
			LEFT JOIN tree_family_lang ON tree_family_lang.family_lang_id = tree_group.family_id'
		,
		'order' => 'count desc' ,
		'group' => 'tree_group_lang.group_id' ,
		'where' => $add_mysql,
		'like' => 'tree_group_lang.title' ,
		'limit' => '20' );
		
	
		
		$arr['list'] = array ( 'id' => 'map_speciesgroup' , 'width' => '100%' , 'size' => 'small' , 'class' => 'compact celled striped definition unstackable' ); // definition
		
		$arr['th']['title'] = array ( 'title' =>"Titel" );
		$arr['th']['count'] = array ( 'title' =>"Anzahl der Bäume" );
		$arr['th']['family_name'] = array ( 'title' =>"Familie" );