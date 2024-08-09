<?php
include __DIR__ . '/../../../smartform2/FormGenerator.php';
include __DIR__ . '/../n_config.php';

$formGenerator = new FormGenerator();

$delete_id = $_POST['delete_id'] ?? null;
$list_id = $_POST['listid'] ?? '';

if (!$delete_id) {
    die("Keine gültige ID zum Löschen angegeben.");
}

$formGenerator->setFormData([
    'id' => 'deleteForm',
    'action' => 'ajax/form_delete2.php',
    'method' => 'POST',
    'class' => 'ui form warning',
    'responseType' => 'json',
    'success' => "if(response.success) { $('.ui.modal').modal('hide'); reloadTable(); showToast(response.message, 'success'); } else { showToast(response.message, 'error'); }"
]);

$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'delete_id',
    'value' => $delete_id
]);

$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'list_id',
    'value' => $list_id
]);
$formGenerator->addField([
    'type' => 'content',
    'value' => 'Sind Sie sicher, dass Sie diesen Eintrag löschen möchten?',
    'class' => 'ui warning message'
]);

$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'value' => 'Löschen',
        'icon' => 'trash',
        'class' => 'red',
    ],
    [
        'type' => 'close',
        'value' => 'Abbrechen',
        'icon' => 'close',
    ]
]);

echo $formGenerator->generateForm();
echo $formGenerator->generateJS();
