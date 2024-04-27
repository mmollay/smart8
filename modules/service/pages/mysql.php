<?php
// Filter zurück setzen beim neuladen der der Tabelle
include_once (__DIR__ . '/../../../config.php');
include_once (__DIR__ . "/../../../smartform/include_form.php");

$array_database = array(
    "ssi_smart_all" => 'ssi_smart(alle)',
    "ssi_smart1" => 'ssi_smart1',
    "ssi_smart2" => 'ssi_smart2',
    "ssi_smart3" => 'ssi_smart3',
    "ssi_smart4" => 'ssi_smart4',
    "ssi_smart7" => 'ssi_smart7',
    "ssi_smart8" => 'ssi_smart8',
    "ssi_newsletter2" => 'ssi_newsletter2',
    "ssi_faktura" => 'ssi_faktura',
    "ssi_faktura93" => 'ssi_faktura93',
    "ssi_faktura94" => 'ssi_faktura94',
    "ssi_faktura_all" => 'ssi_faktura(all)',
    "ssi_learning" => 'ssi_learning',
    "ssi_company" => 'ssi_company'
);

$array_typ = array('int' => 'INT', 'varchar' => 'VARCHAR', 'text' => 'TEXT', 'date' => 'DATE', 'datetime' => 'DATETIME', 'timestamp' => 'TIMESTAMP');

$qres = $GLOBALS['mysqli']->query('show tables') or die(mysqli_error());
while (list($tabelle) = mysqli_fetch_row($qres))
    $table[$tabelle] = $tabelle;

$message = "<div class='message ui' id='message_content' style='max-height:400px; overflow: auto; max-width:1000px;'><i class='close icon'></i><div class='header'>Info</div>	<div id='content_query'></div><br></div>";
$beforeSend = "$('#output_mysql').html(\"$message\"); $('.message .close').on('click', function() { $(this).closest('.message').transition('fade');});";

$arr['form'] = array('width' => '1000', 'action' => 'ajax/mysql_save.php', 'size' => '', 'class' => '');
//$arr ['ajax'] = array ('dataType' => 'html','success' => "$('#output_mysql').html(data); $('.message .close').on('click', function() { $(this).closest('.message').transition('fade');});" );
$arr['ajax'] = array('dataType' => 'html', 'xhr' => "$('#content_query').append(data); $('#message_content').scrollTop($('#message_content')[0].scrollHeight); ", 'beforeSend' => "$beforeSend");

$arr['field'][] = array('type' => 'content', 'text' => "<div id='form_message'></div>");

$arr['field'][] = array('type' => 'div', 'class' => 'message olive ui');
$arr['field'][] = array('type' => 'div', 'class' => 'two fields');
$arr['field']['database_select'] = array('label' => 'Datenbank', 'type' => 'dropdown', 'array' => $array_database, 'validate' => true, 'value_default' => 'ssi_learning');
$arr['field']['database'] = array('label' => '&nbsp', 'label_left' => 'oder', 'type' => 'input');
$arr['field'][] = array('type' => 'div_close');
$arr['field'][] = array('type' => 'div_close');

$arr['field'][] = array('type' => 'accordion', 'class' => 'styled fluid', 'title' => 'Feld anlegen');

$arr['field'][] = array('type' => 'div', 'class' => 'fields');
$arr['field']['set_table'] = array('label' => 'Table', 'type' => 'dropdown', 'class' => 'search seven wide', 'array' => $table);
$arr['field']['field_name'] = array('label' => 'Name', 'type' => 'input', 'class' => 'four wide');
$arr['field']['field'] = array('label' => 'Typ', 'type' => 'dropdown', 'array' => $array_typ, 'value' => 'varchar');
$arr['field']['set_value'] = array('label' => 'Länge', 'type' => 'input', 'class' => 'two wide', 'value' => '20');
$arr['field'][] = array('type' => 'div_close');

$arr['field'][] = array('type' => 'accordion', 'title' => 'Tables und Columns bearbeiten', 'split' => true, 'active' => true);

$arr['field']['checkbox_optimize_tables'] = array('label' => 'Tables von gewählter DB optimieren', 'type' => 'checkbox');

$arr['field'][] = array('type' => 'div', 'class' => 'fields');
// $arr['field']['utf8_table'] = array ( 'label' => 'Table' , 'type' => 'select','class'=>'search six wide', 'array'=>$table);
$arr['field']['utf8_table'] = array('label' => 'Table', 'type' => 'input', 'placeholder' => 'all');
$arr['field']['utf8_column'] = array('label' => 'Column', 'info' => 'Durchforstet einen Column und ersetzt Sonderzeichen auf UTF8', 'type' => 'input', 'placeholder' => 'all');
$arr['field'][] = array('type' => 'div_close');

$arr['field']['checkbox_table_utf8'] = array('label' => 'Tables & Columns in UTF8 umwandeln', 'type' => 'checkbox');
$arr['field']['checkbox_replace_specialcaracter'] = array('label' => 'Umwandeln von Inhalten "Special Caratcer" (Ã¤ = ä,...)', 'type' => 'checkbox');
$arr['field']['checkbox_replace_entities'] = array('label' => 'Umwandeln von Inhalten Entities (Bsp.: &uuml;)', 'type' => 'checkbox');

$arr['field'][] = array('type' => 'accordion', 'title' => 'SQL', 'split' => true);

$arr['field']['query'] = array('type' => 'textarea', 'rows' => '10');

$arr['field'][] = array('type' => 'accordion', 'close' => true);

$arr['field']['br'] = array('type' => 'content', 'text' => '<br>');

$arr['button']['submit'] = array('value' => 'Ausführen', 'color' => 'blue fluid big');

$output = call_form($arr);
echo "<div id='output_mysql'></div>";
//echo "<div class='message ui' style='max-width:1000px;'><i class='close icon'></i><div class='header'>Info</div>	<div id='content_query'></div><br></div>";
echo $output['html'];
echo $output['js'];
