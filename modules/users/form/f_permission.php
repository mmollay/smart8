<?php
include(__DIR__ . '/../users_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$update_id = $_POST['update_id'] ?? null;
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'permissionForm',
    'action' => 'ajax/save_permissions.php',
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

// Benutzerauswahl
$stmt = $db->prepare("
    SELECT user_id, user_name, CONCAT(firstname, ' ', secondname) as full_name 
    FROM user2company 
    WHERE verified = 1 
    ORDER BY user_name
");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$userOptions = [];
foreach ($users as $user) {
    $userOptions[$user['user_id']] = $user['user_name'] . ' (' . $user['full_name'] . ')';
}

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'user_id',
    'label' => 'Benutzer',
    'array' => $userOptions,
    'required' => true,
    'placeholder' => 'Benutzer auswählen',
    'dropdownSettings' => [
        'fullTextSearch' => true,
        'clearable' => true
    ]
]);

// Modulauswahl
$stmt = $db->prepare("
    SELECT module_id, name, identifier 
    FROM modules 
    WHERE status = 1 
    ORDER BY name
");
$stmt->execute();
$modules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$moduleOptions = [];
foreach ($modules as $module) {
    $moduleOptions[$module['module_id']] = $module['name'] . ' (' . $module['identifier'] . ')';
}

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'module_id',
    'label' => 'Modul',
    'array' => $moduleOptions,
    'required' => true,
    'placeholder' => 'Modul auswählen'
]);

// Berechtigungen als grouped_checkbox
$formGenerator->addField([
    'type' => 'grouped_checkbox',
    'name' => 'permissions',
    'label' => 'Berechtigungen',
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
    'required' => true,
    'class' => 'permissions-checkboxes'
]);

// Gültig bis (optional)
$formGenerator->addField([
    'type' => 'calendar',
    'name' => 'valid_until',
    'label' => 'Gültig bis (optional)',
    'placeholder' => 'Datum auswählen',
    'calendarType' => 'date',
    'format' => 'YYYY-MM-DD',
    'minDate' => date('Y-m-d')
]);

// Status
$formGenerator->addField([
    'type' => 'checkbox',
    'style' => 'toggle',
    'name' => 'status',
    'label' => 'Aktiv',
    'value' => '1'
]);

// Buttons
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

// Wenn Update, dann Werte laden
if ($update_id) {
    $formGenerator->loadValuesFromDatabase(
        $db,
        "SELECT * FROM user_module_permissions WHERE id = ? LIMIT 1",
        [$update_id]
    );
}

echo $formGenerator->generateForm();
echo $formGenerator->generateJS();
?>

<script>
    function afterFormSubmit(response) {
        if (response.success) {
            showToast('Berechtigungen erfolgreich gespeichert', 'success');
            $('.ui.modal').modal('hide');
            reloadTable();
        } else {
            showToast('Fehler: ' + response.message, 'error');
        }
    }

    // Dynamisches Laden der verfügbaren Berechtigungen basierend auf Modulauswahl
    $('select[name="module_id"]').on('change', function () {
        let moduleId = $(this).val();
        if (moduleId) {
            $.ajax({
                url: 'ajax/get_module_permissions.php',
                method: 'POST',
                data: { module_id: moduleId },
                success: function (response) {
                    // Aktualisiere die Berechtigungs-Checkboxen basierend auf der Antwort
                    updatePermissionCheckboxes(response);
                }
            });
        }
    });
</script>