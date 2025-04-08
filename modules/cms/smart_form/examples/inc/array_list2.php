<?php
$arr ['list'] = array ('id' => 'demo_list2','size' => 'small','class' => 'compact celled striped definitio' );
$arr ['mysql'] ['table'] = "list ";
$arr ['mysql'] ['field'] = "*";
$arr ['mysql'] ['like'] = 'firstname,secondname,category';
$arr ['mysql'] ['limit'] = '20';

$array_category = array ('first'=>'First','second'=>'Second');

$arr ['th'] ['firstname'] = array ('title' => "Firstname",'modal' => array ('id' => 'edit','popup' => 'Open' ) );
$arr ['th'] ['secondname'] = array ('title' => "Secondname",'validate' => true );
$arr ['th'] ['birthday'] = array ('title' => "Birthday" );
$arr ['th'] ['category'] = array ('title' => "Category" );

$arr ['tr'] ['buttons'] ['left'] = array ('class' => 'tiny' );
$arr ['tr'] ['button'] ['left'] ['edit'] = array ('title' => '','icon' => 'edit','class' => 'blue mini','modal' => 'edit','popup' => 'Edit' );
$arr ['tr'] ['button'] ['left'] ['edit'] ['onclick'] = "$('#edit>.header').html('{firstname} {secondname}');";

$arr ['top'] ['buttons'] = array ('class' => 'tiny' );
$arr ['top'] ['button'] ['edit'] = array ('title' => 'Add new user','icon' => 'plus','class' => 'blue mini' );

$arr ['filter'] ['category'] = array ('type' => 'dropdown','array' => $array_category,'placeholder' => '-- Categories --' );

$arr ['modal'] ['delete'] = array ('title' => 'Remove data(s)','url' => 'ajax/list_form_delete.php','class' => 'small' );

$arr ['modal'] ['edit'] = array ('title' => 'Edit contact','url' => 'ajax/list_form_edit.php','class' => 'small' );
$arr ['modal'] ['edit'] ['button'] ['submit'] = array ('title' => 'Save','color' => 'green','form_id' => 'form_edit' ); //form_id = > ID formular
$arr ['modal'] ['edit'] ['button'] ['more'] = array ('title' => 'More','onclick' => "alert('test');" );
$arr ['modal'] ['edit'] ['button'] ['cancel'] = array ('title' => 'Close','color' => 'grey','icon' => 'close' );
//$arr ['modal'] ['edit'] ['button'] ['submit'] = array ('title' => 'Save','onclick'=>"$('.form_edit.submit').submit();" );


