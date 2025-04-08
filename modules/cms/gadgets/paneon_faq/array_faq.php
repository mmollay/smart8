<?php
include_once (__DIR__ . "/functions.inc");



$arr ['mysql'] = array ('table' => 'ssi_paneon.faq
        LEFT JOIN ssi_paneon.faq2tag ON faq.faq_id = faq2tag.faq_id
        LEFT JOIN ssi_paneon.faq2category ON faq.faq_id = faq2category.faq_id',
    'field' => "faq.faq_id faq_id, title, question, DATE(timestamp) timestamp, tag_id,
		if (image > '', CONCAT ('<div align=\"center\"><img style=\"object-fit: cover;  max-width:150px; height:150px;\" class=\"ui image medium bordered rounded image\" src=\" ',image,'\"></div>'),'Kein Bild') image,
        CONCAT ('<span class=\'ui header\'>',question,'</span><br>',SUBSTRING_INDEX(`answer`, ' ', 10)) question
		",'order' => '','limit' => 50,'group' => 'faq.faq_id','like' => 'title,question,answer' );


$arr ['list'] = array ('id' => 'faq_list','width' => '1200px','size' => '','class' => 'ui very basic table selectable','header' => false ); //selectable
$arr ['list'] ['serial'] = false;
//$arr ['list'] ['template'] = '{title}<br>{problem}';

$arr ['top'] ['button'] ['share_link'] = array ('title' => 'Seite Teilen','icon' => 'share alternate','href' => '','target' => 'new','popup' => 'Link','class' => 'blue' );

$arr ['filter'] ['category_id'] = array ('type' => 'dropdown','array' => $array_category,'placeholder' => '-- Alle Kategorien --' );
//$arr ['filter'] ['category'] = array ('type' => 'dropdown','array' => call_array_report_catogary(),'placeholder' => '-- Alle Kategorien --' );
$arr ['filter'] ['tag_id'] = array ('type' => 'dropdown','array' => call_array_faq_tags(),'placeholder' => '--Alle Tags --','class' => '' );

$arr ['order'] = array ("array" => array ('timestamp desc' => 'Neueste Beiträge zuerst','title ' => 'Nach ABC sortiert','age ' => 'Nach Alter sortiert' ),'default' => 'timestamp desc' );

$arr ['th'] ['image'] = array ('title' => "",'href' => '?id={faq_id}','align'=>'center' );
//$arr ['th'] ['title'] = array ('title' => "Titel", );
$arr ['th'] ['question'] = array ('title' => "Frage",'href' => '?id={faq_id}' );

$arr ['tr'] ['buttons'] ['right'] = array ('class' => 'tiny' );
$arr ['tr'] ['button'] ['right'] ['share_link'] = array ('title' => '','icon' => 'share alternate','class' => 'blue','popup' => 'Teilen' );
//$arr ['tr'] ['button'] ['right'] ['page'] = array ('href'=>'page.php?id={report_id}','title' => '','icon' => 'file','popup' => 'Page', 'align'=>'center', 'target' =>'new' );

$arr ['modal'] ['modal_form_detail'] = array ('title' => 'Erfahrungsbericht {title}','url' => 'form_detail.php','class' => 'fullscreen' ); //overlay
$arr ['modal'] ['share_link'] = array ('title' => 'Share Link','url' => 'share_link.php?share_id={id}','class' => 'small','focus' => true );
$arr ['modal'] ['modal_form_detail'] ['button'] ['cancel'] = array ('title' => 'Schließen','color' => 'green','icon' => 'close' );