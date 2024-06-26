<?php
$arr['mysql'] = array(
    'table' => "email_jobs ej
                JOIN email_contents ec ON ej.content_id = ec.id
                JOIN senders s ON ec.sender_id = s.id
                JOIN recipients r ON ej.recipient_id = r.id",
    'field' => "ej.content_id content_id, s.email as sender_email, s.last_name as sender_last_name, s.email as sender_email, ec.subject, ec.message, COUNT(ej.recipient_id) as recipients_count, ej.created_at",
    'order' => 'ej.created_at desc',
    'limit' => 25,
    'group' => 'ej.content_id',
    'like' => 's.first_name, s.last_name, s.email, ec.subject, r.first_name, r.last_name, r.email',
);

$arr['list'] = array('id' => 'newsletters', 'width' => '1200px', 'align' => '', 'size' => 'small', 'class' => 'compact celled striped definition');

$arr['th']['content_id'] = array('title' => "ID");
$arr['th']['sender_email'] = array('title' => "<i class='user icon'></i>Absender");
$arr['th']['subject'] = array('title' => "<i class='envelope icon'></i>Betreff");
$arr['th']['message'] = array('title' => "Nachricht");
$arr['th']['recipients_count'] = array('title' => "Anzahl der Empfänger");
$arr['th']['created_at'] = array('title' => "Versendet am");

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '', 'icon' => 'edit', 'class' => 'blue mini', 'popup' => 'Bearbeiten');
$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash', 'class' => 'mini', 'popup' => 'Löschen');

$arr['modal']['modal_form'] = array('title' => 'Newsletter bearbeiten', 'class' => '', 'url' => 'form_edit.php');
$arr['modal']['modal_form_delete'] = array('title' => 'Newsletter entfernen', 'class' => 'small', 'url' => 'form_delete.php');
$arr['modal']['modal_form']['button']['submit'] = array('title' => 'Speichern', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form']['button']['cancel'] = array('title' => 'Schließen', 'color' => 'grey', 'icon' => 'close');


$arr['top']['button']['modal_form'] = array('title' => 'Neuen Newsletter anlegen', 'icon' => 'plus', 'class' => 'blue circular', 'popup' => 'Neuen Newsletter anlegen');
