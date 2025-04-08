<?php
$cfgTextLimitList = 500;

$arr['list'] = array ( 'id' => 'search_list' , 'align' => 'center' );

if ($_SESSION['admin_modus'])
	$arr['list']['template'] = "<div class=\"ui huge sub header\"><a href='#' onclick=\"CallContentSite('{site_id}')\">{title}</a></div><span>{text}<span><hr>";
else
	$arr['list']['template'] = "<div class=\"ui huge sub header\"><a href='{site_url}.html'>{title}</a></div><span>{text}<span><hr>";

$arr['mysql']['field'] = "b.site_id sie_id, site_url, c.title title, IF(LENGTH(a.text) >= $cfgTextLimitList, CONCAT(substring(a.text, 1,$cfgTextLimitList), '...'), a.text) text";
$arr['mysql']['table'] = "smart_langLayer a 
LEFT JOIN smart_layer b ON a.fk_id=b.layer_id 
LEFT JOIN smart_langSite c ON b.site_id = c.fk_id
LEFT JOIN smart_id_site2id_page d ON d.site_id = c.fk_id";

$arr['mysql']['group'] = 'b.site_id';
$arr['mysql']['where'] = "AND d.page_id = '$page_id' AND archive =0 ";
// $arr['mysql']['like'] = 'a.text, c.title';
$arr['mysql']['match'] = 'a.text, c.title';
$arr['mysql']['limit'] = '10';
// $arr['mysql']['debug'] = true;

$arr['search']['show_empty'] = true;
$arr['search']['hightlight'] = true;
// $arr['search']['class'] = 'fluid';
$arr['search']['default_text'] = '<br>Nach gewünschten Begriff suchen<br><br><hr>';
$arr['search']['default_text_notfound'] = 'Es wurden keine Inhalte für den Begriff <b>{data}</b> gefunden.';
$arr['search']['strip_tags'] = true;

$arr['th']['title'] = array ();
$arr['th']['text'] = array ();