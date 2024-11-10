<?php
include(__DIR__ . '/../../../smartform2/FormGenerator.php');
include(__DIR__ . '/../n_config.php');
include(__DIR__ . '/../components/placeholders.php');
include(__DIR__ . '/../components/editor_config.php');

$update_id = $_POST['update_id'] ?? null;
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'form_template',
    'action' => 'ajax/template/save_template.php',
    'method' => 'POST',
    'class' => 'ui form',
    'responseType' => 'json',
    'success' => 'afterTemplateFormSubmit(response);'
]);

// Hidden fields
$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'update_id',
    'value' => $update_id
]);

// Grid für Template-Informationen
$formGenerator->addField([
    'type' => 'grid',
    'columns' => 16,
    'fields' => [
        [
            'type' => 'input',
            'name' => 'name',
            'label' => 'Template Name',
            'required' => true,
            'focus' => true,
            'width' => 10
        ],
        [
            'type' => 'input',
            'name' => 'subject',
            'label' => 'Standard-Betreff',
            'placeholder' => 'Standard-Betreff für dieses Template',
            'width' => 6
        ]
    ]
]);

$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'description',
    'label' => 'Beschreibung',
    'rows' => 2
]);

// Platzhalter Toolbar
$formGenerator->addField([
    'type' => 'custom',
    'name' => 'placeholders',
    'label' => 'Verfügbare Platzhalter',
    'html' => getPlaceholdersHTML()
]);

// Template Editor with proper configuration
$formGenerator->addField([
    'type' => 'ckeditor5',
    'name' => 'html_content',
    'label' => 'Template-Inhalt',
    //'required' => true,
    'value' => '', // Default empty value
    'config' => getEditorConfig(),
    'attributes' => [
        'id' => 'template_editor'
    ]
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
        'name' => 'preview',
        'value' => 'Vorschau',
        'class' => 'ui secondary button',
        'onclick' => 'previewTemplate()'
    ],
    [
        'name' => 'close',
        'value' => 'Schließen',
        'class' => 'ui button',
        'onclick' => "$('.ui.modal').modal('hide');"
    ]
]);

// Load existing template data if updating
if ($update_id) {
    $sql = "SELECT * FROM email_templates WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $update_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $formGenerator->setFieldValues($row);
    }
    $stmt->close();
}

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();
?>

<!-- Preview Modal -->
<div class="ui large modal" id="previewModal">
    <i class="close icon"></i>
    <div class="header">Template Vorschau</div>
    <div class="content">
        <div id="previewContent" class="preview-container"></div>
    </div>
    <div class="actions">
        <div class="ui deny button">Schließen</div>
    </div>
</div>

<script src="js/editor_utils.js"></script>

<script>
    $(document).ready(function () {
        // Initialize modal
        $('#previewModal').modal({
            closable: false
        });
    });

    function insertPlaceholder(placeholder) {
        EditorUtils.insertPlaceholder(placeholder);
    }

    function previewTemplate() {
        if (!window.templateEditor) return;

        const content = window.templateEditor.getData();
        const subject = $('#subject').val();

        // Example data for preview
        const previewData = {
            anrede: 'Sehr geehrter Herr',
            titel: 'Dr.',
            vorname: 'Max',
            nachname: 'Mustermann',
            firma: 'Beispiel GmbH',
            email: 'max.mustermann@beispiel.de',
            datum: new Date().toLocaleDateString('de-DE'),
            uhrzeit: new Date().toLocaleTimeString('de-DE')
        };

        let previewContent = content;
        let previewSubject = subject;

        // Replace placeholders
        Object.entries(previewData).forEach(([key, value]) => {
            const regex = new RegExp(`{{${key}}}`, 'g');
            previewContent = previewContent.replace(regex, value);
            previewSubject = previewSubject.replace(regex, value);
        });

        $('#previewContent').html(`
        <div class="ui raised segment">
            <h3>${previewSubject || 'Kein Betreff'}</h3>
            <div class="ui divider"></div>
            ${previewContent || 'Kein Inhalt'}
        </div>
    `);

        $('#previewModal').modal('show');
    }

    function afterTemplateFormSubmit(response) {
        if (response.success) {
            showToast('Template erfolgreich gespeichert', 'success');
            $('.ui.modal').modal('hide');
            if (typeof reloadTable === 'function') {
                reloadTable();
            }
        } else {
            showToast('Fehler beim Speichern: ' + response.message, 'error');
        }
    }

</script>

<style>
    .ck-editor__editable {
        min-height: 400px !important;
    }

    .preview-container {
        max-height: 70vh;
        overflow-y: auto;
        padding: 1em;
    }

    .ui.segment {
        background: #f8f9fa;
    }

    .ui.tiny.buttons {
        margin-bottom: 0.5em;
    }

    .ui.tiny.button {
        margin-right: 0.2em !important;
    }
</style>