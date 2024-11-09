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
    'type' => 'html',
    'content' => getPlaceholdersHTML()
]);

// Template Editor
$formGenerator->addField([
    'type' => 'ckeditor5',
    'name' => 'html_content',
    'label' => 'Template-Inhalt',
    'required' => true,
    'config' => getEditorConfig()
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

if ($update_id) {
    $sql = "SELECT * FROM email_templates WHERE id = ?";
    $formGenerator->loadValuesFromDatabase($db, $sql, [$update_id]);
}

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();
?>

<script src="js/editor_utils.js"></script>

<script>
    function insertPlaceholder(placeholder) {
        EditorUtils.insertPlaceholder(placeholder);
    }

    function insertSnippet(type) {
        const editor = document.querySelector('.ck-editor__editable').ckeditorInstance;
        if (!editor) return;

        let content = '';
        switch (type) {
            case 'text-abstand':
                content = '<p style="margin: 15px 0;">&nbsp;</p>';
                break;
            case 'trennlinie':
                content = '<hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">';
                break;
        }

        editor.setData(editor.getData() + content);
    }

    function previewTemplate() {
        const editor = document.querySelector('.ck-editor__editable').ckeditorInstance;
        const content = editor ? editor.getData() : '';
        const subject = $('#subject').val();

        // Preview Modal öffnen mit Beispieldaten
        const previewData = {
            anrede: 'Sehr geehrter Herr',
            titel: 'Dr.',
            vorname: 'Max',
            nachname: 'Mustermann',
            firma: 'Beispiel GmbH',
            email: 'max.mustermann@beispiel.de',
            datum: new Date().toLocaleDateString('de-DE'),
            datum_lang: new Date().toLocaleDateString('de-DE', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }),
            uhrzeit: new Date().toLocaleTimeString('de-DE')
        };

        let previewContent = content;
        let previewSubject = subject;

        // Platzhalter ersetzen
        Object.entries(previewData).forEach(([key, value]) => {
            const regex = new RegExp(`{{${key}}}`, 'g');
            previewContent = previewContent.replace(regex, value);
            previewSubject = previewSubject.replace(regex, value);
        });

        $('#previewModal').modal({
            closable: false,
            onShow: function () {
                $('#previewContent').html(`
                <div class="ui raised segment">
                    <h3>${previewSubject || 'Kein Betreff'}</h3>
                    <div class="ui divider"></div>
                    ${previewContent || 'Kein Inhalt'}
                </div>
            `);
            }
        }).modal('show');
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

    .ui.tiny.button {
        text-align: left;
        margin-bottom: 0.5em !important;
        padding: 0.8em !important;
    }

    .ui.tiny.button .ui.mini.label {
        float: right;
        background: rgba(0, 0, 0, 0.1);
    }

    .ui.vertical.buttons {
        margin-top: 0.5em;
    }

    .preview-container {
        max-height: 70vh;
        overflow-y: auto;
        padding: 1em;
    }

    .ui.segment {
        background: #f8f9fa;
    }
</style>