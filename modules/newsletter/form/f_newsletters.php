<?
$update_id = $_POST['update_id'] ?? null;

$arr['ajax'] = array('success' => "afterFormSubmit(data)", 'dataType' => "html");

if ($update_id) {
    $arr['sql'] = array('query' => "SELECT * from email_contents WHERE id = '$update_id' LIMIT 1");
}

$arr['field']['sender_id'] = array('tab' => 'first', 'type' => 'dropdown', 'label' => 'Absender', 'array' => getSenders($db), 'validate' => 'Bitte Absender auswählen', 'placeholder' => '--Absender wählen--');
$arr['field']['subject'] = array('tab' => '2', 'type' => 'input', 'label' => 'Betreff');
$arr['field']['message'] = array('tab' => '2', 'type' => 'textarea', 'label' => 'Nachricht');


function getSenders($db)
{
    $array_senders = array();

    $sql = "SELECT id, first_name, last_name FROM senders where email != '' ORDER BY email ASC";
    $result = $db->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $array_senders[$row['id']] = $row['first_name'] . ' ' . $row['last_name'];
        }
    }

    return $array_senders;
}

