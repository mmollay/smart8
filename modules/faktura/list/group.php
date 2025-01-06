<?php
if ($_SESSION['SetYear'] && !$_SESSION["filter"]['group_list']['SetYear']) {
	$_SESSION["filter"]['group_list']['SetYear'] = $_SESSION['SetYear'];
}

$arr['mysql'] = array ( 
		'table' => "article_group" ,
		'field' => "*",
		'group' =>  "group_id",
		'order' => 'company_id desc' , 
		'limit' => 25 ,
		'where' => "AND company_id = '{$_SESSION['faktura_company_id']}' " , 
		'like' => ''
);

$arr['list'] = array ( 'id' => 'group_list' , 'width' => '1000px' , 'align' => '' , 'size' => 'small' , 'class' => 'compact selectable celled striped definition' ); // definition

$arr['th']['group_id'] = array ( 'title' =>"GrpNr." );
$arr['th']['parent_id'] = array ( 'title' =>"In Grupppe");
$arr['th']['parent_id2'] = array ( 'title' =>"In Grupppe2");
$arr['th']['sort'] = array ( 'title' =>"Sortiert");
$arr['th']['title'] = array ( 'title' =>"Titel");
$arr['th']['internet_show'] = array ( 'title' =>"Internet", "align"=>'center', 'replace' => array('1'=>"<i class='icon green eye'></i>",'0'=>""));

$arr['tr']['buttons']['left'] = array ( 'class' => 'tiny' );
$arr['tr']['button']['left']['modal_form_edit'] = array ( 'title' =>'' , 'icon' => 'edit' , 'class' => 'blue mini' , 'popup' => 'Bearbeiten' );

$arr['tr']['buttons']['right'] = array ( 'class' => 'tiny' );
$arr['tr']['button']['right']['modal_form_delete'] = array ( 'title' =>'' , 'icon' => 'trash' , 'class' => 'mini' , 'popup' => 'Löschen' );

$arr['modal']['modal_form_edit'] = array ( 'title' =>'Artikel-Gruppe bearbeiten' , 'class' => 'large' , 'url' => 'form_edit.php' );
$arr['modal']['modal_form_delete'] = array ( 'title' =>'Artikel-Gruppe entfernen' , 'class' => 'small' , 'url' => 'form_delete.php' );

$arr['top']['button']['modal_form_edit'] = array ( 'title' =>'Neue Artikel-Gruppe anlegen' , 'icon' => 'plus' , 'class' => 'blue circular' );
