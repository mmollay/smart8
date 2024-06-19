<?
include (__DIR__ . "/../t_config.php");
include ('../../ssi_smart/smart_form/include_form.php');

$defaul_value['lot'] = '1';

//Server auflisten mit mysql array für Dropdown
$sql = "SELECT server_id, name FROM ssi_trader.servers";
$result = $mysqli->query($sql);
while ($row = $result->fetch_assoc()) {
    $server_array[$row['server_id']] = $row['name'];
}
//ausgabe der ersten server_id  
$set_server_id = key($server_array);


//Start -  Trade
$arr['form'] = array('action' => "ajax/post.php", 'id' => 'form_buy_sell', 'class' => 'message');
$arr['ajax'] = array('success' => "after_post_request(data)", 'dataType' => "html");
$arr['field']['server_id'] = array('type' => 'dropdown', 'label' => 'Server', 'array' => $server_array, 'class' => 'fluid search selection', 'validate' => true, 'value' => $set_server_id);
$arr['field'][] = array('type' => 'div', 'class' => 'two fields');
$arr['field']['buy_sell'] = array('type' => 'dropdown', 'label' => 'Trade', 'array' => array('buy' => 'Buy', 'sell' => 'Sell'), 'class' => 'fluid search selection', 'validate' => true, 'value' => 'buy');
$arr['field']['qty'] = array('type' => 'input', 'label' => 'Lots', 'focus' => true, 'value' => $defaul_value['lot'], 'validate' => true, 'wide' => 'three');

$arr['field'][] = array('type' => 'div_close');
$arr['button']['submit'] = array('value' => 'Market Order', 'icon' => 'play', 'class' => 'green circular fluid');
$output = call_form($arr);
$field_buysell_start = $output['html'] . $output['js'];

//Stop - Trade
$arr['form'] = array('action' => "ajax/post.php", 'id' => 'form_buy_sell_stop', 'class' => 'message');
$arr['ajax'] = array('success' => "after_post_request(data)", 'dataType' => "html");
$arr['field']['server_id'] = array('type' => 'dropdown', 'label' => 'Server', 'array' => $server_array, 'class' => 'fluid search selection', 'validate' => true, 'value' => $set_server_id);
$arr['field'][] = array('type' => 'div', 'class' => 'fields');
$arr['field']['buy_sell_stop'] = array('type' => 'dropdown', 'label' => 'Trade', 'array' => array('buyStop' => 'Stop Buy', 'sellStop' => 'Stop Sell'), 'class' => 'fluid search selection', 'validate' => true);
$arr['field']['qty'] = array('type' => 'input', 'label' => 'Lots', 'focus' => true, 'value' => $defaul_value['lot'], 'validate' => true, 'wide' => 'four');
$arr['field']['price'] = array('type' => 'input', 'label' => 'Price', 'focus' => true, 'validate' => true, 'wide' => 'six');
$arr['field'][] = array('type' => 'div_close');
$arr['button']['submit'] = array('value' => 'Stop Orders', 'icon' => 'counterclockwise rotated step backward', 'class' => 'blue');
$output = call_form($arr);
$field_buysell_stop = $output['html'] . $output['js'];

//Limit - Trade
$arr['form'] = array('action' => "ajax/post.php", 'id' => 'form_buy_sell_limit', 'class' => 'message');
$arr['ajax'] = array('success' => "after_post_request(data)", 'dataType' => "html");
$arr['field']['server_id'] = array('type' => 'dropdown', 'label' => 'Server', 'array' => $server_array, 'class' => 'fluid search selection', 'validate' => true, 'value' => $set_server_id);
$arr['field'][] = array('type' => 'div', 'class' => 'fields');
$arr['field']['buy_sell_limit'] = array('type' => 'dropdown', 'label' => 'Trade', 'array' => array('buyLimit' => 'Limit Buy', 'sellLimit' => 'Limit Sell'), 'class' => 'fluid search selection', 'validate' => true);
$arr['field']['qty'] = array('type' => 'input', 'label' => 'Lots', 'focus' => true, 'value' => $defaul_value['lot'], 'validate' => true, 'wide' => 'four');
$arr['field']['price'] = array('type' => 'input', 'label' => 'Price', 'focus' => true, 'validate' => true, 'wide' => 'six');
$arr['field'][] = array('type' => 'div_close');
$arr['button']['submit'] = array('value' => 'Limit Orders', 'icon' => 'clockwise rotated step backward', 'class' => 'blue');
$output = call_form($arr);
$field_buysell_limit = $output['html'] . $output['js'];

?>
<style>
    .scrollable-segment {
        max-height: 600px;
        overflow-y: auto;
    }
</style>

<div style='max-width: 1100px; text-align:left;'>
    <div class="ui grid">
        <div class="eight wide column">
            <?= $field_buysell_start ?><br>
            <?= $field_buysell_stop ?><br>
            <?= $field_buysell_limit ?>
            <br>
            <?= $field_hedingstrategy_id ?>
            <?= $field_hedingstrategy_account ?>
        </div>
    </div>
</div>

<script type='text/javascript' src='js/form_develop.js'></script>