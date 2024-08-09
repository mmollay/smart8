<?php
$clone = $_GET['clone'];


$arr['ajax'] = array('success' => "afterFormSubmit(data)", 'dataType' => "html");
// $arr['tab'] = array('tabs' => array(1 => 'Default', 2 => 'More'), 'active' => '1');

$arr['field']['title'] = array('tab' => '1', 'type' => 'input', 'label' => 'Title');
$arr['field']['text'] = array('tab' => '1', 'type' => 'textarea', 'label' => 'Description', 'rows' => '2');
// $arr['field']['reverse'] = array('type' => 'checkbox', 'label' => 'Reverse');

if (!$clone) {
	$arr['sql'] = array('query' => "SELECT * from ssi_trader.hedging_group WHERE group_id  = '{$_POST['update_id']}' ");
} else
	$arr['sql'] = array('query' => "SELECT group_id,CONCAT(title,'_clone') title ,text from ssi_trader.hedging_group WHERE group_id  = '{$_POST['update_id']}'");

$stmt = $GLOBALS['mysqli']->prepare("SELECT Side, Size, EntryPrice, TP, Switch, info FROM ssi_trader.hedging WHERE group_id = ? AND level = ?");
$update_id = $_POST['update_id'];

for ($i = 1; $i <= 14; $i++) {
	$stmt->bind_param("ii", $update_id, $i);
	$stmt->execute();
	$result = $stmt->get_result();

	// Setze Standardwerte, falls kein Eintrag gefunden wird
	$sideValue = '';
	$sizeValue = '';
	$entryPriceValue = '';
	$tpValue = '';
	$switchValue = '';
	$infoValue = '';

	if ($row = $result->fetch_assoc()) {
		// Werte aus der Datenbank
		$sideValue = $row['Side'];
		$sizeValue = $row['Size'];
		$entryPriceValue = $row['EntryPrice'];
		$tpValue = $row['TP'];
		$switchValue = $row['Switch'] ? '1' : ''; // Checkbox-Status anpassen
		$infoValue = $row['info'];
	}

	$arr['field'][] = array('type' => 'div', 'class' => 'fields width');
	$arr['field']["title$i"] = array('type' => 'content', 'text' => "<div style='position:relative; top:6px'><b>Nr. $i</b></div>", 'wide' => 'one');
	if ($i == 1) {
		$entryPriceValue = 0;
		$set_disabled = 'disabled';
	} else {
		$set_disabled = '';
	}
	// Beachte die Anpassung der Variablenbezeichnungen und die Verwendung der tatsächlichen Spaltennamen
	$arr['field']["Side$i"] = array('type' => 'dropdown', 'label' => '', 'placeholder' => '--', 'value' => $sideValue, 'array' => array('0' => "<span class='ui blue text'>buy</span>", '1' => "<span class='ui red text'>sell</span>"), 'wide' => 'three');
	$arr['field']["Size$i"] = array('type' => 'input', 'value' => $sizeValue, 'label_left' => "Size", 'wide' => 'three');
	$arr['field']["EntryPrice$i"] = array('type' => 'input', 'value' => $entryPriceValue, 'label_left' => 'Entrypoint', 'wide' => 'four', 'class' => "$set_disabled");
	$arr['field']["TP$i"] = array('type' => 'input', 'value' => $tpValue, 'label_left' => 'Takeprofit', 'wide' => 'four');
	$arr['field']["Switch$i"] = array('type' => 'checkbox', 'label' => 'Switch', 'wide' => 'two', 'value' => $switchValue);
	$arr['field'][] = array('type' => 'div_close');
	$sideValue = '';
}

if ($clone) {
	$arr['field']['clone'] = array('type' => 'hidden', 'value' => '1');
}

$stmt->close();

$add_js .= "<script type=\"text/javascript\" src=\"js/form_after.js\"></script>"; // Ensure the file name is correct
