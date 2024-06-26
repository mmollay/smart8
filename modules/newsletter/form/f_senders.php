<?php
$update_id = $_POST['update_id'] ?? null;

$arr['ajax'] = array('success' => "afterFormSubmit(data)", 'dataType' => "html");
if ($update_id)
    $arr['sql'] = array('query' => "SELECT * from senders WHERE id = '$update_id' LIMIT 1");

$arr['field'][] = array('type' => 'div', 'class' => 'fields width');
$arr['field']['gender'] = array('tab' => '1', 'type' => 'select', 'label' => 'Geschlecht', 'array' => array('male' => 'Männlich', 'female' => 'Weiblich', 'other' => 'Andere'));
$arr['field']['title'] = array('tab' => '1', 'type' => 'input', 'label' => 'Titel');
$arr['field']['first_name'] = array('tab' => '1', 'type' => 'input', 'label' => 'Vorname', 'focus' => true);
$arr['field']['last_name'] = array('tab' => '1', 'type' => 'input', 'label' => 'Nachname');
$arr['field'][] = array('type' => 'div_close');
$arr['field']['company'] = array('tab' => '1', 'type' => 'input', 'label' => 'Firma');
$arr['field']['email'] = array('tab' => '1', 'type' => 'input', 'label' => 'Absende-Email');
$arr['field']['comment'] = array('tab' => '1', 'type' => 'textarea', 'label' => 'Kommentar');
