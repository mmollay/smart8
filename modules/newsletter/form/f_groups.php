<?

$update_id = $_POST['update_id'] ?? null;
$arr['ajax'] = array('success' => "afterFormSubmit(data)", 'dataType' => "html");

if ($update_id) {
    $arr['sql'] = array('query' => "SELECT * from groups WHERE id = '$update_id' LIMIT 1");
}

$arr['field']['name'] = array('tab' => '1', 'type' => 'input', 'label' => 'Gruppenname', 'focus' => true);
$arr['field']['description'] = array('tab' => '1', 'type' => 'textarea', 'label' => 'Beschreibung');
//color
$arr['field']['color'] = array('tab' => '1', 'type' => 'dropdown', 'array' => 'color', 'label' => 'Farbe');