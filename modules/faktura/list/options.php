<?php
$arr ['mysql'] = array ('table' => "company",'field' => "*",'group' => "company_id",'order' => 'company_id desc','limit' => 25,'where' => "AND user_id = '{$_SESSION['user_id']}'",'like' => '' );

$arr ['list'] = array ('id' => 'option_list','width' => '1000px','align' => '','size' => 'small','class' => 'compact celled striped definition' ); // definition

$arr ['th'] ['company_id'] = array ('title' => "ID" );
$arr ['th'] ['company_1'] = array ('title' => "Firma" );
$arr ['th'] ['email'] = array ('title' => "Email" );
$arr ['th'] ['smtp_email'] = array ('title' => "Versende-Adresse" );
$arr ['th'] ['web'] = array ('title' => "Internet" );

$arr ['tr'] ['buttons'] ['left'] = array ('class' => 'tiny' );
$arr ['tr'] ['button'] ['left'] ['modal_form_edit'] = array ('title' => '','icon' => 'edit','class' => 'blue mini','popup' => 'Bearbeiten' );

$arr ['tr'] ['buttons'] ['right'] = array ('class' => 'tiny' );
$arr ['tr'] ['button'] ['right'] ['modal_form_delete'] = array ('title' => '','icon' => 'trash','class' => 'mini','popup' => 'Löschen' );

$arr ['modal'] ['modal_form_edit'] = array ('title' => 'Einstellungen bearbeiten','class' => 'small large','url' => 'form_edit.php' );
$arr ['modal'] ['modal_form_delete'] = array ('title' => 'Einstellungen entfernen','class' => 'small','url' => 'form_delete.php' );

$arr ['top'] ['button'] ['modal_form_edit'] = array ('title' => 'Neue Firma anlegen','icon' => 'plus','class' => 'blue circular' );
