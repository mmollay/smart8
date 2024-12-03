<?php
include(__DIR__ . '/../e_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$update_id = $_POST['update_id'] ?? null;
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'itemForm',
    'action' => 'ajax/form_save.php',
    'method' => 'POST',
    'class' => 'ui form',
    'responseType' => 'json',
    'success' => 'afterFormSubmit(response)'
]);

$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'update_id',
    'value' => $update_id
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'title',
    'label' => 'Titel',
    'required' => true,
    'minLength' => 2,
    'maxLength' => 100
]);

$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'description',
    'label' => 'Beschreibung',
    'rows' => 4,
    'maxLength' => 1000
]);

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'status',
    'label' => 'Status',
    'required' => true,
    'array' => [
        '1' => 'Aktiv',
        '0' => 'Inaktiv'
    ]
]);

$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'value' => 'Speichern',
        'icon' => 'save',
        'class' => 'ui primary button'
    ],
    [
        'type' => 'close',
        'value' => 'Schließen',
        'icon' => 'close'
    ]
]);

if ($update_id) {
    $formGenerator->loadValuesFromDatabase(
        $db,
        "SELECT * FROM items WHERE id = ? LIMIT 1",
        [$update_id]
    );
}

echo $formGenerator->generateForm();
echo $formGenerator->generateJS();
?>
<script>
    function afterFormSubmit(response) {
        if (response.success) {
            showToast('Erfolgreich gespeichert', 'success');
            $('.ui.modal').modal('hide');
            if (typeof reloadTable === 'function') {
                reloadTable();
            }
        } else {
            showToast('Fehler beim Speichern: ' + response.message, 'error');
        }
    }
</script>