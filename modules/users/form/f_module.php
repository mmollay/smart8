<?php
include(__DIR__ . '/../users_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$update_id = $_POST['update_id'] ?? null;
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'moduleForm',
    'action' => 'ajax/save_module.php',
    'method' => 'POST',
    'class' => 'ui form',
    'responseType' => 'json',
    'success' => 'afterFormSubmit(response)'
]);

// Hidden field für Update
if ($update_id) {
    $formGenerator->addField([
        'type' => 'hidden',
        'name' => 'update_id',
        'value' => $update_id
    ]);
}

// Modulfelder
$formGenerator->addField([
    'type' => 'input',
    'name' => 'name',
    'label' => 'Modulname',
    'required' => true,
    'minLength' => 2,
    'maxLength' => 100
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'identifier',
    'label' => 'Identifier (einzigartig)',
    'required' => true,
    'minLength' => 2,
    'maxLength' => 50,
    'validation' => 'alphanumeric',
    'description' => 'Nur Buchstaben, Zahlen und Unterstriche erlaubt'
]);

$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'description',
    'label' => 'Beschreibung',
    'rows' => 3,
    'maxLength' => 500
]);

$formGenerator->addField([
    'type' => 'select',
    'name' => 'status',
    'label' => 'Status',
    'required' => true,
    'options' => [
        '1' => 'Aktiv',
        '0' => 'Inaktiv'
    ],
    'default' => '1'
]);

$formGenerator->addField([
    'type' => 'grouped_checkbox',
    'name' => 'module_permissions',
    'label' => 'Modul-Berechtigungen',
    'options' => [
        'Basis-Berechtigungen' => [
            'view' => 'Anzeigen',
            'create' => 'Erstellen',
            'edit' => 'Bearbeiten',
            'delete' => 'Löschen'
        ],
        'Erweiterte Berechtigungen' => [
            'export' => 'Exportieren',
            'import' => 'Importieren',
            'print' => 'Drucken'
        ],
        'Administrative Rechte' => [
            'manage_users' => 'Benutzer verwalten',
            'manage_modules' => 'Module verwalten',
            'manage_permissions' => 'Berechtigungen verwalten',
            'manage_settings' => 'Einstellungen verwalten'
        ]
    ],
    'class' => 'module-permissions-checkboxes'
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
        "SELECT * FROM modules WHERE module_id = ? LIMIT 1",
        [$update_id]
    );

    // Lade existierende Berechtigungen
    $stmt = $db->prepare("
       SELECT permission_key 
       FROM module_permissions 
       WHERE module_id = ?
   ");
    $stmt->bind_param('i', $update_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $existing_permissions = [];
    while ($row = $result->fetch_assoc()) {
        $existing_permissions[] = $row['permission_key'];
    }

    $formGenerator->setFieldValue('default_permissions', $existing_permissions);
}

echo $formGenerator->generateForm();
?>
<script>
    function afterFormSubmit(response) {
        if (response.success) {
            showToast('Modul erfolgreich gespeichert', 'success');
            $('.ui.modal').modal('hide');
            reloadTable();
        } else {
            showToast('Fehler: ' + response.message, 'error');
        }
    }
</script>