<?php
include_once (__DIR__ . "/functions.inc.php");

$cfg_max_length = 50;

$arr ['mysql'] = array (
		'table' => 'ssi_smart1.21_groups t1 ',
		'field' => "t1.challenge_id challenge_id",
		'order' => '',
		'limit' => 20,
		'debug' => true,
		'group' => 't1.21_groups.challenge_id',
		'like' => '' );
$arr ['list'] ['loading_time'] = true;


//$arr ['list'] = array ('id' => 'challenge_list','width' => '1200px','size' => '','class' => 'ui very basic table selectable','header' => false ); // selectable
$arr ['list'] = array ('id' => 'demo_list','size' => 'small','class' => 'compact celled striped definitio','hover' => true,'header' => false );
$arr ['list'] ['template'] =  "<hr><br>{name}";



$arr ['list'] ['serial'] = false;

// $arr ['list'] ['template'] = '{title}<br>{problem}';

// $arr ['th'] ['image'] = array ('title' => "",'href' => '?id={report_id}','align' => 'center' );
// $arr ['th'] ['info'] = array ('title' => "Erfahrungsberichte",'modal' => array ('id' => 'modal_form_detail','popup' => 'Hier klicken: {title}','onclick' => "$('#modal_form_detail>.header').html('{title}');" ) );

$arr ['th'] ['name'] = array ('title' => "description" );

$arr ['th'] ['info'] = array ('title' => "Erfahrungsberichte",'href' => '?id={challent_id}' );

$arr ['tr'] ['buttons'] ['left'] = array ('class' => 'tiny' );
$arr ['tr'] ['button'] ['left'] ['edit'] = array ('title' => '','icon' => 'edit','class' => 'blue mini','modal' => 'edit','popup' => 'Edit' );

$arr ['tr'] ['buttons'] ['right'] = array ('class' => 'tiny' );

$arr ['tr'] ['button'] ['right'] ['share_link'] = array ('title' => '','icon' => 'share alternate','class' => 'blue','popup' => 'Teilen' );
// $arr ['tr'] ['button'] ['right'] ['page'] = array ('href'=>'page.php?id={report_id}','title' => '','icon' => 'file','popup' => 'Page', 'align'=>'center', 'target' =>'new' );

$arr ['modal'] ['modal_form_detail'] = array ('title' => 'Erfahrungsbericht {title}','url' => 'form_detail.php','class' => 'fullscreen' ); // overlay
$arr ['modal'] ['share_link'] = array ('title' => 'Share Link','url' => 'share_link.php?share_id={id}','class' => 'small','focus' => true );
$arr ['modal'] ['modal_form_detail'] ['button'] ['cancel'] = array ('title' => 'Schließen','color' => 'green','icon' => 'close' );