<?php
include(__DIR__ . '/../n_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');
include(__DIR__ . '/../components/placeholders.php');
include(__DIR__ . '/../components/editor_config.php');

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



// Existing fields
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

// Placeholder buttons
$formGenerator->addField([
    'type' => 'custom',
    'name' => 'placeholders',
    'label' => 'Verfügbare Platzhalter',
    'html' => getPlaceholdersHTML()
]);

$formGenerator->addField([
    'type' => 'ckeditor5',
    'name' => 'message',
    'label' => 'Nachricht',
    //'required' => true,
    'error_message' => 'Bitte geben Sie eine Nachricht ein',
    'config' => getEditorConfig()
]);

// File uploader
$formGenerator->addField([
    'type' => 'uploader',
    'name' => 'files',
    'config' => array(
        'MAX_FILE_SIZE' => 10 * 1024 * 1024,
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
        'showDeleteAllButton' => true,
        'onFileListChange' => 'updateFileListInDatabase',
    )
]);

// Buttons
$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'name' => 'submit',
        'value' => 'Speichern',
        'class' => 'ui primary button'
    ],
    [
        'name' => 'close',
        'value' => 'Schließen',
        'class' => 'ui button',
        'onclick' => "$('.ui.modal').modal('hide');"
    ]
]);

if ($update_id) {
    $sql = "SELECT * FROM email_contents WHERE id = ?";
    $formGenerator->loadValuesFromDatabase($db, $sql, [$update_id]);
}

// Helper functions
function getEmailTemplates($db)
{
    $templates = array();
    $sql = "SELECT id, name FROM email_templates ORDER BY name ASC";
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
    $sql = "SELECT g.id, g.name, g.color, COUNT(rg.recipient_id) as email_count 
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

<!-- Preview modal -->
<div class="ui large modal" id="previewModal">
    <div class="header">Vorschau mit Platzhaltern</div>
    <div class="content">
        <div id="previewContent"></div>
    </div>
    <div class="actions">
        <div class="ui deny button">Schließen</div>
    </div>
</div>
<script src="js/editor_utils.js"></script>
<script>
    $(document).ready(function () {
        // Haupt-Modal konfigurieren (das mit dem Formular)
        $('#modal_form_n').modal({
            allowMultiple: true,  // Erlaubt mehrere Modals
            closable: false
        });

        // Template-Modal separat konfigurieren
        $('#saveTemplateModal').modal({
            allowMultiple: true,  // Erlaubt mehrere Modals
            closable: false,
        });


    });

    function insertPlaceholder(placeholder) {
        EditorUtils.insertPlaceholder(placeholder);
    }

    function loadTemplate(templateId) {
        if (!templateId) return;

        $.ajax({
            url: 'ajax/template/get_template.php',
            method: 'POST',
            data: { template_id: templateId },
            dataType: 'json',
            success: function (response) {
                console.log('Template Response:', response); // Debug-Ausgabe

                if (response.success) {
                    const editor = document.querySelector('.ck-editor__editable').ckeditorInstance;
                    if (editor && response.data.html_content) {
                        editor.setData(response.data.html_content);
                    }

                    if (response.data.subject) {
                        $('#subject').val(response.data.subject);
                    }

                    showToast('Template wurde geladen', 'success');
                } else {
                    console.error('Template Ladefehler:', response.message);
                    showToast('Fehler beim Laden des Templates: ' + response.message, 'error');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                showToast('Fehler beim Laden des Templates', 'error');
            }
        });
    }

    function saveAsTemplate() {
        // Template-Modal öffnen, ohne das Haupt-Modal zu schließen
        $('#saveTemplateModal')
            .modal({
                allowMultiple: true,
                onApprove: function () {
                    return saveTemplate();
                }
            })
            .modal('show');
    }

    function saveTemplate() {
        const name = $('#templateName').val();
        const description = $('#templateDescription').val();
        const editor = document.querySelector('.ck-editor__editable').ckeditorInstance;
        // Hole den Betreff aus dem Hauptformular
        const subject = $('#subject').val();


        // Hole den Content aus dem Editor
        const content = editor ? editor.getData() : '';

        // Debug-Ausgaben
        console.log('Speichere Template mit folgenden Werten:', {
            name: name,
            description: description,
            html_content: content,
            subject: subject
        });

        if (!name) {
            showToast('Bitte geben Sie einen Template-Namen ein', 'error');
            return false;
        }

        // Modal in Loading-Zustand versetzen
        $('#saveTemplateModal').addClass('loading');

        // AJAX-Request
        $.ajax({
            url: 'ajax/template/save_template.php',
            method: 'POST',
            data: {
                name: name,
                description: description,
                html_content: content,
                subject: subject // Stelle sicher, dass der subject mitgesendet wird
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#saveTemplateModal')
                        .modal('hide')
                        .removeClass('loading');

                    showToast('Template wurde gespeichert', 'success');

                    // Template Dropdown aktualisieren
                    refreshTemplateDropdown();

                    // Template-Modal-Felder zurücksetzen
                    $('#templateName').val('');
                    $('#templateDescription').val('');
                } else {
                    $('#saveTemplateModal').removeClass('loading');
                    showToast('Fehler beim Speichern des Templates: ' + response.message, 'error');
                }
            },
            error: function (xhr, status, error) {
                $('#saveTemplateModal').removeClass('loading');
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                showToast('Fehler beim Speichern des Templates: ' + error, 'error');
            }
        });

        return false;
    }

    // Funktion zum Aktualisieren der Template-Dropdown
    function refreshTemplateDropdown() {
        $.ajax({
            url: 'ajax/template/get_templates.php',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    const $dropdown = $('select[name="template_id"]');
                    const currentValue = $dropdown.val();

                    $dropdown.empty();
                    $dropdown.append('<option value="">--Template auswählen--</option>');

                    response.templates.forEach(template => {
                        $dropdown.append(new Option(template.name, template.id));
                    });

                    // Dropdown aktualisieren
                    $dropdown.dropdown('refresh');
                }
            }
        });
    }

    function previewWithPlaceholders() {
        const editor = document.querySelector('.ck-editor__editable').ckeditorInstance;
        if (!editor) return;

        const content = editor.getData();
        const subject = $('#subject').val();

        // Example placeholder values
        const placeholders = {
            'anrede': 'Sehr geehrter Herr',
            'titel': 'Dr.',
            'vorname': 'Max',
            'nachname': 'Mustermann',
            'firma': 'Musterfirma GmbH',
            'email': 'max.mustermann@example.com',
            'datum': new Date().toLocaleDateString('de-DE'),
            'uhrzeit': new Date().toLocaleTimeString('de-DE')
        };

        let previewContent = content;
        let previewSubject = subject;

        // Replace placeholders
        Object.entries(placeholders).forEach(([key, value]) => {
            const regex = new RegExp(`{{${key}}}`, 'g');
            previewContent = previewContent.replace(regex, value);
            previewSubject = previewSubject.replace(regex, value);
        });

        $('#previewContent').html(`
        <div class="ui raised segment">
            <h3>${previewSubject}</h3>
            <div class="ui divider"></div>
            ${previewContent}
        </div>
    `);

        $('#previewModal').modal('show');
    }

    function updateFileListInDatabase(fileList) {
        const formData = new FormData();
        formData.append('action', 'updateFileList');
        formData.append('update_id', <?php echo json_encode($update_id); ?>);
        formData.append('fileList', JSON.stringify(fileList));

        fetch('ajax/template/update_file_list.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Dateiliste aktualisiert');
                } else {
                    console.error('Fehler beim Aktualisieren der Dateiliste:', data.error);
                }
            })
            .catch(error => {
                console.error('Fehler beim Senden der Anfrage:', error);
            });
    }

    function afterFormSubmit(response) {
        if (response.success) {
            showToast('Newsletter gespeichert', 'success');
            if (typeof reloadTable === 'function') {
                reloadTable();
            }
        } else {
            showToast('Fehler beim Speichern: ' + response.message, 'error');
        }
    }

</script>

<style>
    .ui.segment .ui.buttons {
        margin-bottom: 0.5em;
    }

    .ui.segment .ui.buttons button {
        margin-right: 0.2em;
    }

    #previewContent {
        max-height: 70vh;
        overflow-y: auto;
    }

    .ck-editor__editable {
        min-height: 300px;
    }
</style>