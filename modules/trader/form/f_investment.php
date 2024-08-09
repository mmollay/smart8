<?php

$arr['ajax'] = array('success' => "afterFormSubmit(data)", 'dataType' => "html");

if (!$_POST['update_id']) {
    $default_desposite_date = date('Y-m-d');
    $default_end_date = date('Y-m-d', strtotime('+1 year'));
    $default_value_positive_multiplier = 1.0;
    $default_value_negative_multiplier = 0.5;
    $default_amount = 10000;
}

$arr['sql'] = array('query' => "SELECT * FROM ssi_trader.deposits LEFT JOIN ssi_trader.profit_shares ON deposits.client_id = profit_shares.client_id WHERE deposits.deposit_id = '{$_POST['update_id']}'");

$arr['field']['title'] = array('type' => 'input', 'label' => 'Title', 'focus' => true, 'info' => 'Enter the title of the investment. This could be the project name or investment opportunity.', 'value' => 'Investment', 'validate' => true);
$arr['field']['client_id'] = array('type' => 'dropdown', 'label' => 'User', 'array_mysql' => "SELECT client_id, CONCAT(first_name, ' ', last_name) AS name FROM ssi_trader.clients ORDER BY name ASC", 'text' => 'name', 'class' => 'fluid search selection', 'info' => 'Select the user who is making the investment. This list is populated from registered clients.', 'validate' => true);
$arr['field'][] = array('type' => 'div', 'class' => 'fields equal width');
$arr['field']['amount'] = array('type' => 'input', 'label' => 'Deposit Amount', 'class' => 'fluid', 'placeholder' => '50000.00', 'info' => 'Specify the amount of the deposit for this investment. Format: Numeric value, e.g., 10000 for fifty thousand.', 'value' => $default_amount);
$query_brokerlist = "SELECT user account, CONCAT(b.title,' (',b.user,')') AS name FROM ssi_trader.broker AS b ORDER BY b.title ASC";
$arr['field']['account'] = array('tab' => '1', 'type' => 'dropdown', 'label' => 'Broker', 'array_mysql' => $query_brokerlist, 'text' => 'name', 'class' => 'fluid search selection', 'validate' => true);
$arr['field'][] = array('type' => 'div_close');

$arr['field']['deposit_date'] = array('type' => 'date', 'label' => 'Deposit Date', 'class' => 'fluid', 'info' => 'Select the date when the deposit was made.', 'value' => $default_desposite_date, 'validate' => true);

$arr['field'][] = array('type' => 'div', 'class' => 'fields equal width');
$arr['field']['start_date'] = array('type' => 'date', 'label' => 'Start Date', 'class' => 'fluid', 'info' => 'Specify the start date of the period for which profit sharing is calculated.', 'validate' => true, 'value' => $default_desposite_date);
$arr['field']['end_date'] = array('type' => 'date', 'label' => 'End Date', 'class' => 'fluid', 'info' => 'Specify the end date of the period for which profit sharing is calculated.', 'value' => $default_end_date);
$arr['field'][] = array('type' => 'div_close');

$arr['field']['comment'] = array('type' => 'textarea', 'label' => 'Comment', 'class' => 'fluid', 'rows' => 1, 'info' => 'Add any comments or additional notes related to this investment.');


$arr['field'][] = array('type' => 'div', 'class' => 'fields equal width');
$arr['field']['positive_multiplier'] = array('tab' => '1', 'type' => 'input', 'label' => 'Positive Multiplier', 'value' => $default_value_positive_multiplier);
$arr['field']['negative_multiplier'] = array('tab' => '1', 'type' => 'input', 'label' => 'Negative Multiplier', 'value' => $default_value_negative_multiplier);
$arr['field'][] = array('type' => 'div_close');

$add_js .= "<script type=\"text/javascript\" src=\"js/form_after.js\"></script>";