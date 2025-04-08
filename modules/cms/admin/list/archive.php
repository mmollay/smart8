<?php
$arr['mysql'] = array ( 
		'field' => "c.layer_id layer_id, a.title title, matchcode", 
		'table' => "
		smart_langSite a 
		INNER JOIN smart_id_site2id_page b ON a.fk_id = b.site_id 
		INNER JOIN smart_layer c ON b.site_id = c.site_id 
		INNER JOIN smart_langLayer d ON c.layer_id = d.fk_id", 
		'group' => 'layer_id',
		'limit' => 25 , 
 		'where' => "AND b.page_id = '{$_SESSION['smart_page_id']}' AND archive = 1" , 
		'like' => 'a.title, matchcode' );

$query = $GLOBALS['mysqli']->query ( "SELECT b.site_id site_id,a.title title FROM smart_langSite a 
		INNER JOIN smart_id_site2id_page b ON a.fk_id = b.site_id 
		INNER JOIN smart_layer c ON b.site_id = c.site_id 
		INNER JOIN smart_langLayer d ON c.layer_id = d.fk_id 
			WHERE b.page_id = '{$_SESSION['smart_page_id']}' 
			AND archive = 1 GROUP by site_id " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
while ( $array = mysqli_fetch_array ( $query ) ) {
	$site_array[$array['site_id']] = $array['title'];
}

$arr['list'] = array ( 'id' => 'archive_list' , 'width' => '' , 'align' => '' , 'size' => 'small' , 'class' => 'compact celled striped definition' ); // definition
$arr['filter']['site_id'] = array ( 'type' => 'select', 'array' => $site_array, 'placeholder' => '--Seiten--',  'table' =>'b' );
$arr['th']['layer_id'] = array ( 'title' =>"ID" );
$arr['th']['matchcode'] = array ( 'title' =>"Machcode" );
$arr['th']['title'] = array ( 'title' =>"Zugewiesen Seite" );
//$arr['th']['menu_disable'] = array ( 'title' =>'<i title="In Menü eingebunden" class="tooltip ui icon linkify"></i>' , 'align' => 'center' );

$arr['tr']['buttons']['left'] = array ( 'class' => 'tiny' );
$arr['tr']['button']['left']['modal_form_view'] = array ( 'title' =>'Wieder einbinden' , 'icon' => 'recycle' , 'onclick'=>"layer_back_archive({id});" );

$arr['tr']['buttons']['right'] = array ( 'class' => 'tiny' );
$arr['tr']['button']['right']['modal_form_delete2'] = array ( 'title' =>'', 'icon' => 'trash' , 'class' => 'mini red' , 'popup' => 'Löschen' );

$arr['modal']['modal_form_delete2'] = array ( 'title' =>'Element entfernen' , 'class' => 'small' , 'url' => 'form_delete.php' );

//$arr['top']['button']['modal_form'] = array ( 'title' =>'Neuer Black-Kontakt' , 'icon' => 'plus' , 'class' => 'blue circular' , 'popup' => 'Neuen Black-Kontakt anlegen' );