<?php
echo $_POST['content_id'];
if ($_POST['list_id'] == 'groups') {
    include ('../form/f_' . $_POST['list_id'] . '.php');
    exit;
}
if ($_POST['list_id'] == 'newsletters') {
    include ('../form/f_' . $_POST['list_id'] . '.php');
    exit;
}

include (__DIR__ . '/../n_config.php');
include (__DIR__ . '/../../../../smartform/include_form.php');

$arr['form'] = array('action' => "ajax/form_edit2.php", 'id' => 'form_edit', 'size' => 'small', 'inline' => 'list');

include ('../form/f_' . $_POST['list_id'] . '.php');
// $arr['button']['submit'] = array('value' => 'Speichern', 'color' => 'blue');
// $arr['button']['close'] = array('value' => 'Schließen', 'color' => 'gray', 'js' => "$('.ui.modal').modal('hide').modal('hide dimmer').find('.content').empty();");
$arr['hidden']['list_id'] = $_POST['list_id'];
$output = call_form($arr);
echo $output['html'];
echo $output['js'];
