<?php
include(__DIR__ . '/../n_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'form_import',
    'action' => 'ajax/import_save.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "afterImportSubmit(response);"
]);

// Erklärungstext
$formGenerator->addField([
    'type' => 'segment',
    'class' => 'ui info message',
    'content' => '
        <p>Fügen Sie hier Ihre Daten im folgenden Format ein:</p>
        <pre>email;vorname;nachname;firma;geschlecht;titel;kommentar</pre>
        <p>Eine E-Mail-Adresse pro Zeile. Trennen Sie die Felder mit Semikolon (;).</p>
    '
]);

// Import Optionen
$formGenerator->addField([
    'type' => 'segment',
    'class' => 'ui segment',
    'fields' => [
        [
            'type' => 'dropdown',
            'name' => 'group_id',
            'label' => 'Gruppe zuweisen',
            'array' => getAllGroups($db),
            'required' => true,
            'error_message' => 'Bitte wählen Sie eine Gruppe aus',
            'placeholder' => '--Gruppe auswählen--',
            'width' => 8
        ],
        [
            'type' => 'checkbox',
            'name' => 'overwrite',
            'label' => 'Bestehende Datensätze überschreiben',
            'width' => 8
        ],
        [
            'type' => 'checkbox',
            'name' => 'skip_duplicates',
            'label' => 'Duplikate überspringen',
            'width' => 8,
            'checked' => true
        ]
    ]
]);

// Textfeld für Import
$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'import_data',
    'label' => 'Import Daten',
    'required' => true,
    'error_message' => 'Bitte geben Sie Daten zum Importieren ein',
    'rows' => 10,
    'placeholder' => "max@beispiel.de;Max;Mustermann;Firma GmbH;male;Dr.;Kommentar\njane@beispiel.de;Jane;Doe;Firma AG;female;Prof.;Weiterer Kommentar"
]);

// Vorschau Button
$formGenerator->addField([
    'type' => 'button',
    'value' => 'Vorschau',
    'class' => 'ui secondary button',
    'onclick' => 'previewImport()',
    'width' => 4
]);

// Submit Button
$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'name' => 'submit',
        'value' => 'Importieren',
        'class' => 'ui primary button'
    ],
    [
        'type' => 'button',
        'name' => 'cancel',
        'value' => 'Abbrechen',
        'class' => 'ui button',
        'onclick' => 'window.location.href="index.php"'
    ]
]);

// Vorschau Container
$formGenerator->addField([
    'type' => 'segment',
    'class' => 'ui segment',
    'style' => 'display: none;',
    'id' => 'preview-container',
    'content' => '<div class="ui header">Vorschau</div><div id="preview-content"></div>'
]);

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();
?>

<script>
    function previewImport() {
        const importData = document.querySelector('[name="import_data"]').value;
        if (!importData) {
            alert('Bitte geben Sie Daten zum Importieren ein');
            return;
        }

        const previewContainer = document.getElementById('preview-container');
        const previewContent = document.getElementById('preview-content');

        // Tabelle erstellen
        let table = '<table class="ui celled table">';
        table += '<thead><tr><th>E-Mail</th><th>Vorname</th><th>Nachname</th><th>Firma</th><th>Geschlecht</th><th>Titel</th><th>Kommentar</th></tr></thead><tbody>';

        const rows = importData.split('\n');
        rows.forEach((row, index) => {
            if (row.trim()) {
                const columns = row.split(';');
                table += '<tr>';
                for (let i = 0; i < 7; i++) {
                    table += `<td>${columns[i] || ''}</td>`;
                }
                table += '</tr>';
            }
        });

        table += '</tbody></table>';
        previewContent.innerHTML = table;
        previewContainer.style.display = 'block';
    }

    function afterImportSubmit(response) {
        if (response.success) {
            alert('Import erfolgreich abgeschlossen!\n' +
                'Importierte Datensätze: ' + response.imported + '\n' +
                'Übersprungene Duplikate: ' + response.skipped);
            window.location.href = 'index.php';
        } else {
            alert('Fehler beim Import: ' + response.message);
        }
    }
</script>

<style>
    .ui.form .field>label {
        font-weight: bold;
        margin-bottom: 0.5em;
    }

    .ui.info.message pre {
        background: #f8f8f9;
        padding: 1em;
        border-radius: 4px;
        margin: 0.5em 0;
    }

    #preview-container {
        margin-top: 2em;
    }
</style>