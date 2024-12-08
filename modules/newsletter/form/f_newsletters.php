<?php

include(__DIR__ . '/../n_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');
include(__DIR__ . '/../components/placeholders.php');
include(__DIR__ . '/../components/editor_config.php');

// Initial Newsletter erstellen oder prüfen
if (!isset($_POST['update_id'])) {
    $sql = "INSERT INTO email_contents (subject, message, sender_id, user_id) VALUES ('Neue Nachricht', 'Neuer Text', 1, '$userId')";
    $db->query($sql);
    $update_id = $db->insert_id;
} else {
    $update_id = $_POST['update_id'];
    // Prüfe ob der Newsletter dem User gehört
    $check = $db->query("SELECT id FROM email_contents WHERE id = '$update_id' AND user_id = '$userId'");
    if ($check->num_rows === 0) {
        die("Keine Berechtigung");
    }
}


$formGenerator = new FormGenerator();

// Tabs für bessere Übersichtlichkeit
$formGenerator->addField([
    'type' => 'tab',
    'tabs' => [
        'basis' => 'Grundeinstellungen',
        'anhang' => 'Anhänge',
        'vorlagen' => 'Template-Verwaltung'
    ],
    'active' => 'basis'
]);

$formGenerator->setFormData([
    'id' => 'form_edit',
    'action' => 'ajax/form_save.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "afterFormSubmit(response);"
]);

// Hidden fields
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

// Template selection
$formGenerator->addField([
    'type' => 'segment',
    'class' => 'ui segment',
    'tab' => 'vorlagen',
    'fields' => [
        [
            'type' => 'dropdown',
            'name' => 'template_id',
            'label' => 'Template',
            'array' => getEmailTemplates($db),
            'placeholder' => '--Template auswählen--',
            'width' => 12,
            'dropdownSettings' => [
                'onChange' => 'function(value, text, $selected) { loadTemplate(value); }'
            ]
        ],
        [
            'type' => 'button',
            'class' => 'ui primary button',
            'value' => 'Als Template speichern',
            'onclick' => 'saveAsTemplate()',
            'width' => 4
        ]
    ]
]);

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'sender_id',
    'tab' => 'basis',
    'leftLabel' => 'Von',
    'leftLabelClass' => 'ui label fixed-width-label',  // Zusätzliche Klasse
    'array' => getSenders($db),
    'required' => true,
    'error_message' => 'Bitte Absender auswählen',
    'placeholder' => '--Absender wählen--'
]);

// Existing fields
$formGenerator->addField([
    'type' => 'dropdown',
    'tab' => 'basis',
    'name' => 'tags',
    'leftLabel' => 'An ',
    'leftLabelClass' => 'ui label fixed-width-label',
    'array' => getAllGroups($db),
    'multiple' => true,
    'required' => true,
    'error_message' => 'Bitte mindestens eine Gruppe auswählen',
    'placeholder' => '--Gruppen wählen--',
    'value' => getSelectedEmailContentGroups($db, $update_id),
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'subject',
    'tab' => 'basis',
    'placeholder' => 'Betreff eingeben...',
    'leftLabel' => 'Betreff',
    'leftLabelClass' => 'ui label fixed-width-label',  // Die gleiche Klasse
    'required' => true,
    'error_message' => 'Bitte geben Sie einen Betreff ein'
]);

// Placeholder buttons
$formGenerator->addField([
    'type' => 'custom',
    'name' => 'placeholders',
    'tab' => 'basis',
    //'label' => 'Verfügbare Platzhalter',
    'html' => getPlaceholdersHTML()
]);

$formGenerator->addField([
    'type' => 'ckeditor5',
    'name' => 'message',
    'tab' => 'basis',

    //'label' => 'Nachricht',
    //'required' => true,
    'error_message' => 'Bitte geben Sie eine Nachricht ein',
    'config' => getEditorConfig($_SESSION['user_id'], $update_id)
]);

// File uploader
$formGenerator->addField([
    'type' => 'uploader',
    'tab' => 'anhang',
    'name' => 'files',
    'config' => array(
        'MAX_FILE_SIZE' => 10 * 1024 * 1024,
        'MAX_FOLDER_SIZE' => 10000 * 1024 * 1024,
        'ALLOWED_FORMATS' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'zip', 'wav'],
        'MAX_FILE_COUNT' => 10,
        'UPLOAD_DIR' => "../../../users/$userId/newsletters/$update_id/attachements/",
        'LANGUAGE' => 'de',
        'dropZoneId' => 'drop-zone',
        'fileInputId' => 'file-input',
        'fileListId' => 'file-list',
        'deleteAllButtonId' => 'delete-all',
        'progressContainerId' => 'progress-container',
        'progressBarId' => 'progress',
        'showDeleteAllButton' => true,
        //'onFileListChange' => "updateFileListInDatabase",
    )
]);

// Buttons
// $formGenerator->addButtonElement([
//     [
//         'type' => 'submit',
//         'name' => 'submit',
//         'value' => 'Speichern',
//         'class' => 'ui primary button'
//     ],
//     [
//         'name' => 'close',
//         'value' => 'Schließen',
//         'class' => 'ui button',
//         'onclick' => "$('.ui.modal').modal('hide');"
//     ]
// ]);

if ($update_id) {
    $sql = "SELECT * FROM email_contents WHERE id = '$update_id' AND user_id = '$userId'";
    $formGenerator->loadValuesFromDatabase($db, $sql);
}

// Helper functions
function getEmailTemplates($db)
{
    global $userId;
    $templates = array();
    $sql = "SELECT id, name FROM email_templates WHERE user_id = '$userId' ORDER BY name ASC";
    $result = $db->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $templates[$row['id']] = $row['name'];
        }
    }
    return $templates;
}

function getSenders($db)
{
    global $userId;
    $array_senders = array();
    $sql = "SELECT id, first_name, last_name,email FROM senders WHERE email != '' AND user_id = ? ORDER BY email ASC";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $array_senders[$row['id']] = $row['first_name'] . ' ' . $row['last_name'] . ' (' . $row['email'] . ')';
        }
    }
    return $array_senders;
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

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();
?>

<!-- Template save modal -->
<div class="ui mini modal" id="saveTemplateModal">
    <i class="close icon"></i>
    <div class="header">Template speichern</div>
    <div class="content">
        <div class="ui form">
            <div class="field required">
                <label>Template Name</label>
                <input type="text" id="templateName" placeholder="Name eingeben...">
            </div>
            <div class="field">
                <label>Beschreibung</label>
                <textarea id="templateDescription" rows="2" placeholder="Optionale Beschreibung..."></textarea>
            </div>
        </div>
    </div>
    <div class="actions">
        <div class="ui cancel button">Abbrechen</div>
        <div class="ui positive button">Speichern</div>
    </div>
    <div class="ui inverted dimmer">
        <div class="ui loader"></div>
    </div>
</div>
<script src="js/f_newsletter.js"></script>

<style>

</style>