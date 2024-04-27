<?php
$array_inout_filter = array ('amount < 0 ' => "Ausgaben",'amount > 0 ' => "Einnahmen" );

$arr ['mysql'] = array (
		'field' => "automator_id, word, description, client_id, accounts.title title, comment",'table' => "automator",
		'table' => "automator LEFT JOIN accounts ON automator.account_id = accounts.account_id",
		'order' => 'automator_id desc ',
		'limit' => 50,
		'group' => 'automator_id',
		'like' => 'description,word' );

$arr ['list'] = array ('serial' => false,'id' => 'automator_list','width' => '','align' => '','size' => 'small','class' => 'compact celled striped definition' ); // definition

//$arr ['filter'] ['account'] = array ('type' => 'dropdown','array' => $array_account_number,'placeholder' => '--Alle Konten--' );

//$arr ['filter'] ['inout'] = array ('type' => 'dropdown','array' => $array_inout_filter,'placeholder' => 'Ein- und Ausgang','query' => "{value}" );

//$arr ['filter'] ['more'] = array ('type' => 'dropdown','array' => $array_more_filter,'placeholder' => '--Andere Filter--','query' => "{value}" );

$arr ['th'] ['automator_id'] = array ('title' => "ID",'align' => 'center' );

$arr ['th'] ['description'] = array ('title' => "Beschreibung" );

$arr ['th'] ['word'] = array ('title' => "Schlüsselwörter" );

$arr ['th'] ['title'] = array ('title' => "Konto" );

$arr ['tr'] ['buttons'] ['left'] = array ('class' => 'tiny' );
$arr ['tr'] ['button'] ['left'] ['modal_form_edit'] = array ('title' => '','icon' => 'edit','class' => 'blue mini','popup' => 'Bearbeiten' );

$arr ['tr'] ['buttons'] ['right'] = array ('class' => 'tiny' );
$arr ['tr'] ['button'] ['right'] ['modal_form_delete'] = array ('title' => '','icon' => 'trash','class' => 'mini','popup' => 'Löschen' );

$arr ['modal'] ['modal_form_edit'] = array ('title' => 'Automation bearbeiten','class' => 'small','url' => 'form_edit.php' );
$arr ['modal'] ['modal_form_delete'] = array ('title' => 'Automation entfernen','class' => 'small','url' => 'form_delete.php' );

$arr ['top'] ['button'] ['modal_form_edit'] = array ('title' => 'Neue Automation erstellen','icon' => 'plus','class' => 'blue circular' );