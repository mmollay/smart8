<?php
include __DIR__ . '/../../../smartform2/FormGenerator.php';
include __DIR__ . '/../n_config.php';

$update_id = $_POST['update_id'] ?? null;
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'senderForm',
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
    'type' => 'hidden',
    'name' => 'list_id',
    'value' => 'senders'
]);

$formGenerator->addField([
    'type' => 'grid',
    'columns' => 16,
    'fields' => [
        [
            'type' => 'dropdown',
            'name' => 'gender',
            'label' => 'Geschlecht',
            'array' => [
                'male' => 'Männlich',
                'female' => 'Weiblich',
                'other' => 'Andere'
            ],
            'width' => 4
        ],
        [
            'type' => 'input',
            'name' => 'title',
            'label' => 'Titel',
            'width' => 2,
        ],
        [
            'type' => 'input',
            'name' => 'first_name',
            'label' => 'Vorname',
            'focus' => true,
            'width' => 5
        ],
        [
            'type' => 'input',
            'name' => 'last_name',
            'label' => 'Nachname',
            'width' => 5
        ]
    ]
]);


// Einzelne Felder
$formGenerator->addField([
    'type' => 'input',
    'name' => 'company',
    'label' => 'Firma'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'email',
    'label' => 'Absende-Email'
]);


$formGenerator->addField([
    'type' => 'input',
    'name' => 'test_email',
    'label' => 'Test-Email für Newsletter',
    'placeholder' => 'test@example.com',
    'email' => true,
    'width' => 'eight',
    'value' => $data['test_email'] ?? '',
    'error_message' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.'
]);


$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'comment',
    'label' => 'Kommentar'
]);

// Buttons hinzufügen
$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'name' => 'submit',
        'value' => 'Speichern',
        'class' => 'ui primary button'
    ],
    [
        'name' => 'cancel',
        'value' => 'Abbrechen',
        'class' => 'ui button',
        'onclick' => "$('.ui.modal').modal('hide');"
    ]
], [
    'layout' => 'grouped',
    'alignment' => 'right'
]);


if ($update_id) {
    try {
        $formGenerator->loadValuesFromDatabase($db, "SELECT * FROM senders WHERE id = ? LIMIT 1", [$update_id]);
    } catch (Exception $e) {
        echo "<div class='ui error message'>Fehler beim Laden der Absenderdaten: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();
?>

<script>
    function afterFormSubmit(response) {
        if (response.success) {
            showToast('Absender erfolgreich gespeichert', 'success');
            $('.ui.modal').modal('hide');
            if (typeof reloadTable === 'function') {
                reloadTable();
            }
        } else {
            showToast('Fehler beim Speichern des Absenders: ' + response.message, 'error');
        }
    }
</script>