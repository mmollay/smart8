<?php

$arr['mysql'] = array ( 
		'field' => "site_id, menu_text, title,site_url, 
			if (dynamic_site,'dynamisch','') dynamic_site,
			if (menu_disable = 0,'<font color=green><b>On</b></font>','<font color=gray><b>Off</b></font>') as menu_disable", 
		'table' => "smart_langSite RIGHT JOIN smart_id_site2id_page ON site_id = fk_id", 
		'order' => 'site_id desc' , 
		'group' => 'site_id',
		'limit' => 25 , 
		'where' => "AND page_id = '{$_SESSION['smart_page_id']}'",  
		'like' => 'menu_text, title' 
			
		);
$arr['order']['array'] = array ('timestamp desc'=>'nach Aktualisierung sortieren','title'=>'nach Namen sortieren','site_id desc'=>'nach Veröffentlichung sortieren');
$arr['order']['default']  = 'timestamp desc';
$arr['list'] = array ( 'id' => 'site_list' , 'width' => '' , 'align' => '' , 'size' => 'small' , 'class' => 'compact celled striped definition' ); // definition

$arr['th']['site_id'] = array ( 'title' =>"ID" );
$arr['th']['title'] = array ( 'title' =>"Titel" );
$arr['th']['site_url'] = array ( 'title' =>"Url", 'replace' => array('default'=>'{value}.html') );
$arr['th']['menu_text'] = array ( 'title' =>"Menü" );
//$arr['th']['dynamic_site'] = array ( 'title' =>'<i title="Extern eingebundene Seite" class="tooltip ui icon external share">' , 'align' => 'center' );
$arr['th']['menu_disable'] = array ( 'title' =>'<i title="In Menü eingebunden" class="tooltip ui icon linkify"></i>' , 'align' => 'center' );

$arr['tr']['buttons']['left'] = array ( 'class' => 'tiny' );
$arr['tr']['button']['left']['modal_form_view'] = array ( 'title' =>' Öffnen' , 'icon' => 'eye' , 'popup' => 'Seite anzeigen',  'onclick'=>"location.href='index.php?site_select={id}';" );
$arr['tr']['button']['left']['modal_form'] = array ( 'title' =>'' , 'class'=>'blue', 'icon' => 'settings' , 'popup' => 'Einstellungen' );

$arr['tr']['buttons']['right'] = array ( 'class' => 'tiny' );
$arr['tr']['button']['right']['modal_form_delete'] = array ( 'title' =>'', 'icon' => 'trash' , 'class' => 'mini red' , 'popup' => 'Löschen' );

$arr['modal']['modal_form'] = array ( 'title' =>'Seite bearbeiten' , 'class' => '' , 'url' => 'form_edit.php' );
$arr['modal']['modal_form_delete'] = array ( 'title' =>'Seite entfernen' , 'class' => 'small' , 'url' => 'form_delete.php' );

//$arr['top']['button']['modal_form'] = array ( 'title' =>'Neuer Black-Kontakt' , 'icon' => 'plus' , 'class' => 'blue circular' , 'popup' => 'Neuen Black-Kontakt anlegen' );