<?php
$arr['mysql'] = array(
	'table' => "senders",
	'field' => "id, first_name, last_name, company, email, gender, title, comment",
	'order' => 'id desc',
	'limit' => 25,
	'group' => 'id',
	'like' => 'first_name, last_name, email, company',
);

$arr['list'] = array('id' => 'senders', 'width' => '1200px', 'align' => '', 'size' => 'small', 'class' => 'compact celled striped definition');

$arr['th']['first_name'] = array('title' => "<i class='user icon'></i>Vorname");
$arr['th']['last_name'] = array('title' => "<i class='user icon'></i>Nachname");
$arr['th']['company'] = array('title' => "<i class='building icon'></i>Firma");
$arr['th']['email'] = array('title' => "<i class='mail icon'></i>Absende-Email");
// $arr['th']['gender'] = array('title' => "Geschlecht");
// $arr['th']['title'] = array('title' => "Titel");
$arr['th']['comment'] = array('title' => "Kommentar");

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '', 'icon' => 'edit', 'class' => 'blue mini', 'popup' => 'Bearbeiten');
$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash', 'class' => 'mini', 'popup' => 'Löschen');

$arr['modal']['modal_form'] = array('title' => 'Absender bearbeiten', 'class' => '', 'url' => 'form_edit.php');
$arr['modal']['modal_form_delete'] = array('title' => 'Absender entfernen', 'class' => 'small', 'url' => 'form_delete.php');
$arr['modal']['modal_form']['button']['submit'] = array('title' => 'Speichern', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form']['button']['cancel'] = array('title' => 'Schließen', 'color' => 'grey', 'icon' => 'close');


$arr['top']['button']['modal_form'] = array('title' => 'Neuen Absender anlegen', 'icon' => 'plus', 'class' => 'blue circular', 'popup' => 'Neuen Absender anlegen');
