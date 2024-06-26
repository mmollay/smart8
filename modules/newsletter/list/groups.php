<?php
$arr['mysql'] = array(
    'table' => "groups g
                LEFT JOIN recipient_group rg ON g.id = rg.group_id
                LEFT JOIN recipients r ON rg.recipient_id = r.id",
    'field' => "g.id as group_id, CONCAT('<div class=\"ui ', g.color, ' compact empty mini circular label\"></div> ', g.name) as group_name, COUNT(r.id) as recipients_count, g.created_at",
    'order' => 'g.created_at desc',
    'limit' => 25,
    'group' => 'g.id',
    'like' => 'g.name, r.first_name, r.last_name, r.email',
);

$arr['list'] = array('id' => 'groups', 'width' => '1200px', 'align' => '', 'size' => 'small', 'class' => 'compact celled striped definition');

$arr['th']['group_id'] = array('title' => "ID");
$arr['th']['group_name'] = array('title' => "<i class='users icon'></i>Gruppe");
$arr['th']['recipients_count'] = array('title' => "Anzahl der Empfänger");
$arr['th']['created_at'] = array('title' => "Erstellt am");

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '', 'icon' => 'edit', 'class' => 'blue mini', 'popup' => 'Bearbeiten');
$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash', 'class' => 'mini', 'popup' => 'Löschen');

$arr['modal']['modal_form'] = array('title' => 'Gruppe bearbeiten', 'class' => '', 'url' => 'form_edit.php');
$arr['modal']['modal_form_delete'] = array('title' => 'Gruppe entfernen', 'class' => 'small', 'url' => 'form_delete.php');
$arr['modal']['modal_form']['button']['submit'] = array('title' => 'Speichern', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form']['button']['cancel'] = array('title' => 'Schließen', 'color' => 'grey', 'icon' => 'close');

$arr['top']['button']['modal_form'] = array('title' => 'Neue Gruppe anlegen', 'icon' => 'plus', 'class' => 'blue circular', 'popup' => 'Neue Gruppe anlegen');
