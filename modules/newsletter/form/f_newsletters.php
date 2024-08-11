<?php
include (__DIR__ . '/../n_config.php');
include (__DIR__ . '/../../../smartform2/FormGenerator.php');

//wenn $_POST['update_id'] nicht vorhanden ist, dann soll eine neue E-Mail erstellt werden
//In der Datebenbank wird ein neuer Datensatz erstellt und die ID des neuen Datensatzes wird zurückgegeben

//$_POST['list_id'] = $_POST['listid'] ?? '';

if (!isset($_POST['update_id'])) {
    $sql = "INSERT INTO email_contents (subject, message, sender_id) VALUES ('Neue Nachricht', 'Neuer Text', 1)";
    $db->query($sql);
    $update_id = $db->insert_id;
} else {
    $update_id = $_POST['update_id'];
}

$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'form_edit',
    'action' => 'ajax/form_save.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "afterFormSubmit(response);"
]);

$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'update_id',
    'value' => $update_id
]);

$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'list_id',
    'value' => 'newsletters'
]);

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'tags',
    'label' => 'Gruppen',
    'array' => getGroups($db),
    'multiple' => true,
    'required' => true,
    'error_message' => 'Bitte mindestens eine Gruppe auswählen',
    'placeholder' => '--Gruppen wählen--',
    'value' => getSelectedEmailContentGroups($db, $update_id),
]);


$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'sender_id',
    'label' => 'Absender',
    'array' => getSenders($db),
    'required' => true,
    'error_message' => 'Bitte Absender auswählen',
    'placeholder' => '--Absender wählen--'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'subject',
    'label' => 'Betreff',
    'required' => true,

    'error_message' => 'Bitte geben Sie einen Betreff ein'
]);

$formGenerator->addField([
    'type' => 'ckeditor5',
    'name' => 'message',
    'label' => 'Nachricht',
    'required' => true,
    'error_message' => 'Bitte geben Sie eine Nachricht ein',
    'config' => [
        'minHeight' => 300,
        'maxHeight' => 600,
        'placeholder' => 'Geben Sie hier Ihre Nachricht ein...'
    ]
]);

// Hier fügen wir den FileUploader hinzu
$formGenerator->addField([
    'type' => 'uploader',
    'name' => 'files',
    'config' => array(
        'MAX_FILE_SIZE' => 10 * 1024 * 1024, // 10 MB
        'MAX_FOLDER_SIZE' => 10000 * 1024 * 1024,
        'ALLOWED_FORMATS' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'zip', 'wav'],
        'MAX_FILE_COUNT' => 10,
        'UPLOAD_DIR' => "../../uploads/users/$update_id/",
        'LANGUAGE' => 'de',
        'dropZoneId' => 'drop-zone',
        'fileInputId' => 'file-input',
        'fileListId' => 'file-list',
        'deleteAllButtonId' => 'delete-all',
        'progressContainerId' => 'progress-container',
        'progressBarId' => 'progress',
        'showDeleteAllButton' => true,  // Option zum Anzeigen des "Alles löschen" Buttons
        'onFileListChange' => 'updateFileListInDatabase',

    )
]);

//$formGenerator->addField(['type' => 'hidden', 'name' => 'list_id', 'value' => $_POST['list_id']]);
$formGenerator->addField(['type' => 'hidden', 'name' => 'update_id', 'value' => $update_id]);


// Buttons hinzufügen
$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'name' => 'submit',
        'value' => 'Speichern',
        'class' => 'ui primary button'
    ],
    [
        'name' => 'cancel',
        'value' => 'Abbrechen',
        'class' => 'ui button',
        'onclick' => "$('.ui.modal').modal('hide');"
    ]
]);

$sql = "SELECT * FROM email_contents WHERE id = ?";
$formGenerator->loadValuesFromDatabase($db, $sql, [$update_id]);

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();

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

function getGroups($db)
{
    $array_groups = array();
    $sql = "SELECT g.id, g.name, g.color, COUNT(rg.recipient_group_id) as email_count 
            FROM groups g
            LEFT JOIN recipient_group rg ON g.id = rg.group_id
            GROUP BY g.id
            ORDER BY g.name ASC";

    $result = $db->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $color_class = $row['color'];
            $email_count = $row['email_count'];
            $array_groups[$row['id']] = '<div class="ui mini empty circular ' . $color_class . ' label"> </div>' .
                $row['name'] . ' (' . $email_count . ')</b>';
        }
    }
    return $array_groups;
}

function getSelectedEmailContentGroups($db, $email_content_id)
{
    $selected_groups = array();
    $sql = "SELECT group_id FROM email_content_groups WHERE email_content_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $email_content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $selected_groups[] = $row['group_id'];
    }
    $stmt->close();

    return $selected_groups;
}

?>
<script>
    function afterFormSubmit(response) {
        if (response.success) {
            showToast('Gruppe erfolgreich gespeichert', 'success');
            // Hier können Sie zusätzliche Aktionen nach erfolgreicher Speicherung hinzufügen
            // z.B. Modal schließen, Liste aktualisieren, etc.
            // $('.ui.modal').modal('hide');
            if (typeof reloadTable === 'function') {
                reloadTable();
            }
        } else {
            showToast('Fehler beim Speichern der Gruppe: ' + response.message, 'error');
        }
    }

    function updateFileListInDatabase(fileList) {
        console.log('updateFileListInDatabase ist definiert:', typeof updateFileListInDatabase === 'function');

        const formData = new FormData();
        formData.append('action', 'updateFileList');
        formData.append('update_id', <?php echo json_encode($update_id); ?>);
        formData.append('fileList', JSON.stringify(fileList));

        fetch('ajax/update_file_list.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Dateiliste erfolgreich aktualisiert');
                } else {
                    console.error('Fehler beim Aktualisieren der Dateiliste:', data.error);
                }
            })
            .catch(error => {
                console.error('Fehler beim Senden der Anfrage:', error);
            });
    }



</script>