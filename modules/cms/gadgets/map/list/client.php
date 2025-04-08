<?
$arr['mysql'] = array ( 
		'field' => "client.client_id client_id, if (company_1 = '',CONCAT (firstname,' ',secondname),company_1) company_1, COUNT(tree.client_id) tree_count, web, client.client_id client_id " ,
		'table' => "tree
		INNER JOIN (client,tree_template,tree_template_lang,tree_group_lang)
		ON client.client_id = tree.client_faktura_id
		AND tree_template_lang.temp_id = tree_template.temp_id
		AND tree_group_lang.matchcode = tree_template.tree_group
		AND tree.plant_id = tree_template.temp_id
		AND tree_group_lang.lang = 'de'",	
		'order' => 'tree_count desc' , 
		'group' => 'client.client_id' , 
		'limit' => 10 , 
		'where' => $add_mysql,
		'like' => 'company_1'
 );


$arr['mysql'] = array (
		'field' => "client.client_id client_id, if (company_1 = '',CONCAT (firstname,' ',secondname),company_1) company_1, COUNT(tree.client_id) tree_count, web, client.client_id client_id , client_number" ,
		'table' => "client LEFT JOIN tree ON client.client_id = tree.client_faktura_id" ,
		'order' => 'tree_count desc' ,
		'group' => 'client.client_id' ,
		'where' => $add_mysql ,
		'limit' => 10 ,
		'like' => 'company_1,firstname,secondname,web' );

$arr['list'] = array ( 'id' => 'map_client' , 'width' => '100%' , 'size' => 'small' , 'class' => 'compact celled striped definition unstackable' ); // definition

//$arr['th']['client_id'] = array ( 'title' =>"ID" );
$arr['th']['company_1'] = array ( 'title' =>"<i class='user icon'></i>Baumpate" );
$arr['th']['web'] = array ( 'title' =>"<i class='browser icon'></i>Web" );
$arr['th']['tree_count'] = array ( 'title' =>"Baumspenden" , 'align' => 'center' );