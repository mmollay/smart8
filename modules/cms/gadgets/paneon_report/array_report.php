<?php
include_once (__DIR__ . "/functions.inc");
$arr ['mysql'] = array ('table' => 'ssi_paneon.report LEFT JOIN ssi_paneon.report2tag ON report.report_id = report2tag.report_id',
		'field' => "report.report_id report_id, title, text, DATE(timestamp) timestamp, tag_id, age, problem, highlight,
		if (image > '', CONCAT ('<div align=\"center\"><img style=\"object-fit: cover;  max-width:150px; height:150px;\" class=\"ui image medium bordered rounded image\" src=\" ',image,'\"></div>'),'Kein Bild') image,
		CONCAT ('<span class=\'ui header small\'>',problem,'</span><br>',highlight) info
		",'order' => '','limit' => 20,'group' => 'report.report_id','like' => 'title,text,highlight,problem,answer' );

// mit Lazy load 
// $arr ['mysql'] = array ('table' => 'ssi_paneon.report LEFT JOIN ssi_paneon.report2tag ON report.report_id = report2tag.report_id',
// 		'field' => "report.report_id report_id, title, text, DATE(timestamp) timestamp, tag_id, age, problem, highlight,
// 		if (image > '', CONCAT ('<div class=\"image\" align=\"center\"><img style=\"object-fit: cover;  max-width:150px; height:150px;\" class=\"lazy_load ui medium bordered rounded image\" data-src=\"gadgets/images/square-image.png\" src=\"',image,'\"></div>'),'Kein Bild') image,
// 		CONCAT ('<span class=\'ui header small\'>',problem,'</span><br>',highlight) info
// 		",'order' => '','limit' => 50,'group' => 'report.report_id','like' => 'title,text,highlight,problem,answer' );


//if (highlight>'',CONCAT('<div class=\'ui message green\'>',highlight,'</div>'),''))
//'<div class=\'ui message\'>',answer,'</div>',
//'<span class=\'text ui red\'><b>Problem:</b> ',problem,'</span>',
//if (age>0,CONCAT('<div class=\'ui compact label \'>',age,' Jahre</div><br>'),'<br>'),

$arr ['list'] = array ('id' => 'report_list','width' => '1200px','size' => '','class' => 'ui very basic table selectable','header' => false ); //selectable
$arr ['list'] ['serial'] = false;
//$arr ['list'] ['template'] = '{title}<br>{problem}';

$arr ['top'] ['button'] ['share_link'] = array ('title' => 'Seite Teilen','icon' => 'share alternate','href' => '','target' => 'new','popup' => 'Link','class' => 'blue' );

$arr ['filter'] ['category'] = array ('type' => 'dropdown','array' => $array_category,'placeholder' => '-- Alle Kategorien --' );
$arr ['filter'] ['tag_id'] = array ('type' => 'dropdown','array' => call_array_report_tags (),'placeholder' => '--Alle Tags --','class' => '' );

$arr ['order'] = array ("array" => array ('timestamp desc' => 'Neueste Beiträge zuerst','title ' => 'Nach ABC sortiert','age ' => 'Nach Alter sortiert' ),'default' => 'timestamp desc' );

$arr ['th'] ['image'] = array ('title' => "",'href' => '?id={report_id}','align' => 'center' );
// $arr ['th'] ['info'] = array ('title' => "Erfahrungsberichte",'modal' => array ('id' => 'modal_form_detail','popup' => 'Hier klicken: {title}','onclick' => "$('#modal_form_detail>.header').html('{title}');" ) );

$arr ['th'] ['info'] = array ('title' => "Erfahrungsberichte",'href' => '?id={report_id}' );

$arr ['tr'] ['buttons'] ['right'] = array ('class' => 'tiny' );
$arr ['tr'] ['button'] ['right'] ['share_link'] = array ('title' => '','icon' => 'share alternate','class' => 'blue','popup' => 'Teilen' );
//$arr ['tr'] ['button'] ['right'] ['page'] = array ('href'=>'page.php?id={report_id}','title' => '','icon' => 'file','popup' => 'Page', 'align'=>'center', 'target' =>'new' );

$arr ['modal'] ['modal_form_detail'] = array ('title' => 'Erfahrungsbericht {title}','url' => 'form_detail.php','class' => 'fullscreen' ); //overlay
$arr ['modal'] ['share_link'] = array ('title' => 'Share Link','url' => 'share_link.php?share_id={id}','class' => 'small','focus' => true );
$arr ['modal'] ['modal_form_detail'] ['button'] ['cancel'] = array ('title' => 'Schließen','color' => 'green','icon' => 'close' );