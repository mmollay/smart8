<?
$update_id = $_POST['update_id'] ?? null;
$arr['ajax'] = array('success' => "afterFormSubmit(data)", 'dataType' => "html");

if ($update_id) {
    $arr['sql'] = array('query' => "SELECT * from recipients WHERE id = '$update_id' LIMIT 1");
    $selected_groups = getSelectedGroups($db, $update_id);
} else {
    $selected_groups = [];
}

//$arr['field']['tags'] = array('tab' => 'first', 'type' => 'multiselect', 'label' => "An diese Tags", 'array' => getGroups($db), 'class' => 'search', 'value' => $tag_id);
$arr['field'][] = array('tab' => 'tag', 'type' => 'div', 'class' => 'fields');
$arr['field']['tags'] = array('tab' => 'tag', 'label' => 'Ausgewählte Tags', 'class' => 'eleven wide search', 'type' => 'multiselect', 'array' => getGroups($db), 'value' => $selected_groups, 'settings' => "onChange: function(value, text, selectedItem) { generate_tag_toggles(value,'{$_POST['update_id']}') }");
$arr['field']['tags_add'] = array('tab' => 'tag', 'type' => 'input', 'label' => 'Neuen Tag', 'class' => 'five wide', 'label_left' => "<i class='icon arrow left'></i> Anlegen", 'label_left_class' => 'button orange ui');
$arr['field'][] = array('tab' => 'tag', 'type' => 'div_close');


$arr['field'][] = array('type' => 'div', 'class' => 'fields width');
$arr['field']['gender'] = array('tab' => '1', 'type' => 'select', 'label' => 'Geschlecht', 'array' => array('male' => 'Männlich', 'female' => 'Weiblich', 'other' => 'Andere'));
$arr['field']['title'] = array('tab' => '1', 'type' => 'input', 'label' => 'Titel');
$arr['field']['first_name'] = array('tab' => '1', 'type' => 'input', 'label' => 'Vorname', 'focus' => true);
$arr['field']['last_name'] = array('tab' => '1', 'type' => 'input', 'label' => 'Nachname');
$arr['field'][] = array('type' => 'div_close');
$arr['field']['company'] = array('tab' => '1', 'type' => 'input', 'label' => 'Firma');
$arr['field']['email'] = array('tab' => '1', 'type' => 'input', 'label' => 'Empfänger-Email');
$arr['field']['comment'] = array('tab' => '1', 'type' => 'textarea', 'label' => 'Kommentar');


function getGroups($db)
{
    $array_groups = array();

    $sql = "SELECT g.id, g.name, COUNT(rg.recipient_id) as recipients_count 
            FROM groups g
            LEFT JOIN recipient_group rg ON g.id = rg.group_id
            GROUP BY g.id, g.name
            ORDER BY g.name ASC";
    $result = $db->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $array_groups[$row['id']] = $row['name'] . ' (' . $row['recipients_count'] . ' Empfänger)';
        }
    }

    return $array_groups;
}

function getSelectedGroups($db, $recipient_id)
{
    $selected_groups = array();

    $sql = "SELECT group_id FROM recipient_group WHERE recipient_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $recipient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $selected_groups[] = $row['group_id'];
    }

    $stmt->close();
    return $selected_groups;
}