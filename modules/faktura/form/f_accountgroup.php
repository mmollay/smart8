<?php
$arr['ajax'] = array(
    'success' => "$('#modal_form').modal('hide'); table_reload();",
    'dataType' => "html"
);
$arr['sql'] = array(
    'query' => "SELECT * from accountgroup WHERE accountgroup_id = '{$_POST['update_id']}'"
);

$arr['field']['title'] = array(
    'type' => 'input',
    'label' => 'Titel',
    'focus' => true,
    'validate'=> true
);
// $arr['field']['company_id'] = array(
//     'type' => 'dropdown',
//     'label' => 'Firmenzuweisung',
//     'array' => $arr_comp,
    
// );