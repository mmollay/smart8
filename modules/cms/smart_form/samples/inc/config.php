<?php
$arr['list'] = array ( 'id'=>'demo_list', 'width' => '800px' , 'align' => 'center' , 'size' => 'small' , 'class' => 'compact celled striped definitio' ); //definition

$arr['mysql']['table'] = "tbl_user ";
$arr['mysql']['field'] = "user_id, user_name email, concat(firstname,' ',secondname) name, zip";
$arr['mysql']['group'] = 'user_id';
$arr['mysql']['like'] = 'user_name';
$arr['mysql']['limit'] = '20';

$arr['th']['name'] = array ( 'title' =>"<i class='user icon'></i>Name" );
$arr['th']['email'] = array ( 'title' =>"<i class='mail icon'></i>Email", 'align' => "center" );
$arr['th']['zip'] = array ( 'title' =>'Address' , 'class' => "right aligned" );

$arr['tr']['buttons'] = array('class'=>'tiny');
$arr['tr']['button']['edit']   = array('title' =>'', 'icon' =>'edit', 'class'=>'blue mini',  modal=>'edit', 'popup' => 'Bearbeiten' );
$arr['tr']['button']['delete'] = array('title' =>'', 'icon' =>'trash', 'class'=>'mini', 'popup' => 'Löschen' );

$arr['modal']['edit'] =  array( 'title' =>'Kontakt bearbeiten', 'url' => 'example_form.php' );
$arr['modal']['delete'] = array ( 'title' =>'Kontakt löschen' , 'class' => 'small' , 'url' => 'form_delete.php' );

$arr['top']['buttons'] = array('class'=>'tiny');
$arr['top']['button']['edit']   = array('title' =>'Neuen User anlegen', 'icon' =>'plus', 'class'=>'blue mini', 'popup' => 'Neu anlegen' );