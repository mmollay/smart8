<?php
include(__DIR__ . '/../users_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$update_id = $_POST['update_id'] ?? null;
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'userForm',
    'action' => 'ajax/save_user.php',
    'method' => 'POST',
    'class' => 'ui form',
    'responseType' => 'json',
    'success' => 'afterFormSubmit(response)'
]);

if ($update_id) {
    $formGenerator->addField([
        'type' => 'hidden',
        'name' => 'update_id',
        'value' => $update_id
    ]);
}

// Define tabs
$formGenerator->addField([
    'type' => 'tab',
    'tabs' => [
        'account' => 'Benutzerdaten',
        'modules' => 'Module & Berechtigungen',
        'contact' => 'Kontaktdaten'
    ],
    'active' => 'account'
]);

// Account Tab
$formGenerator->addField([
    'type' => 'input',
    'name' => 'user_name',
    'label' => 'E-Mail (Benutzername)',
    'required' => true,
    'validation' => 'email',
    'tab' => 'account'
]);

if (!$update_id) {
    $formGenerator->addField([
        'type' => 'password',
        'name' => 'password',
        'label' => 'Passwort',
        'required' => true,
        'minLength' => 8,
        'placeholder' => 'Passwort eingeben',
        'tab' => 'account'
    ]);
}

$formGenerator->addFieldGroup('personal', [
    [
        'type' => 'input',
        'name' => 'firstname',
        'label' => 'Vorname',
        'required' => true,
        'width' => 'eight'
    ],
    [
        'type' => 'input',
        'name' => 'secondname',
        'label' => 'Nachname',
        'required' => true,
        'width' => 'eight'
    ]
], [
    'wrapper' => 'ui segment'
], 'account');

$formGenerator->addFieldGroup('status', [
    [
        'type' => 'dropdown',
        'name' => 'verified',
        'label' => 'Status',
        'array' => [
            '1' => 'Aktiv',
            '0' => 'Inaktiv'
        ],
        'width' => 'eight'
    ],
    [
        'type' => 'checkbox',
        'name' => 'superuser',
        'label' => 'Superuser',
        'value' => '1',
        'width' => 'eight'
    ]
], [
    'wrapper' => 'ui segment'
], 'account');

// Modules Tab
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
    'name' => 'modules',
    'label' => 'Zugewiesene Module',
    'array' => $moduleOptions,
    'multiple' => true,
    'placeholder' => 'Module auswählen',
    'dropdownSettings' => [
        'fullTextSearch' => true,
        'allowAdditions' => false
    ],
    'tab' => 'modules'
]);

// Contact Tab
$formGenerator->addFieldGroup('company', [
    [
        'type' => 'input',
        'name' => 'company1',
        'label' => 'Firma',
        'width' => 'sixteen'
    ]
], [
    'wrapper' => 'ui segment'
], 'contact');

$formGenerator->addFieldGroup('address', [
    [
        'type' => 'input',
        'name' => 'street',
        'label' => 'Straße',
        'width' => 'sixteen'
    ],
    [
        'type' => 'input',
        'name' => 'zip',
        'label' => 'PLZ',
        'validation' => 'numeric',
        'width' => 'six'
    ],
    [
        'type' => 'input',
        'name' => 'city',
        'label' => 'Stadt',
        'width' => 'ten'
    ]
], [
    'wrapper' => 'ui segment'
], 'contact');

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'country',
    'label' => 'Land',
    'array' => [
        'at' => 'Österreich',
        'de' => 'Deutschland',
        'ch' => 'Schweiz'
    ],
    'default' => 'at',
    'tab' => 'contact'
]);

// Action Buttons
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

// Load existing data if updating
if ($update_id) {
    $formGenerator->loadValuesFromDatabase(
        $db,
        "SELECT * FROM user2company WHERE user_id = ? LIMIT 1",
        [$update_id]
    );

    $stmt = $db->prepare("
        SELECT module_id 
        FROM user_modules 
        WHERE user_id = ? AND status = 1
    ");
    $stmt->bind_param('i', $update_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $assignedModules = [];
    while ($row = $result->fetch_assoc()) {
        $assignedModules[] = $row['module_id'];
    }

    $formGenerator->setFieldValue('modules', $assignedModules);
}

echo $formGenerator->generateForm();
echo $formGenerator->generateJS();
?>

<script>
    function afterFormSubmit(response) {
        if (response.success) {
            showToast('Benutzer erfolgreich gespeichert', 'success');
            $('.ui.modal').modal('hide');
            reloadTable();
        } else {
            showToast('Fehler: ' + response.message, 'error');
        }
    }
</script>