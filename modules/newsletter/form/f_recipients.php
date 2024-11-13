<?php
include __DIR__ . '/../../../smartform2/FormGenerator.php';
include __DIR__ . '/../n_config.php';

$update_id = $_POST['update_id'] ?? null;
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'recipientForm',
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
    'value' => 'recipients'
]);

// Gruppen-Auswahl hinzufügen
$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'tags',
    'label' => 'Gruppen',
    'array' => getAllGroups($db),
    'multiple' => true,
    'class' => 'search',
    'dropdownSettings' => [
        'allowAdditions' => true,
        'placeholder' => 'Gruppen auswählen oder hinzufügen'
    ]
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
            'width' => 2
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
    'label' => 'Empfänger-Email',
    'required' => true
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
]);

if ($update_id) {
    try {
        $formGenerator->loadValuesFromDatabase($db, "SELECT * FROM recipients WHERE id = ? LIMIT 1", [$update_id]);
        $selectedGroups = getSelectedGroups($db, $update_id);
        $formGenerator->setFieldValues(['tags' => $selectedGroups]);
    } catch (Exception $e) {
        echo "<div class='ui error message'>Fehler beim Laden der Empfängerdaten: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();


function getSelectedGroups($db, $recipient_id)
{
    $selected_groups = [];
    $sql = "SELECT group_id FROM recipient_group WHERE recipient_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $recipient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $selected_groups[] = $row['group_id'];
    }
    $stmt->close();
    return $selected_groups;
}
?>

<script>
    function afterFormSubmit(response) {
        if (response.success) {
            showToast('Empfänger erfolgreich gespeichert', 'success');
            $('.ui.modal').modal('hide');
            if (typeof reloadTable === 'function') {
                reloadTable();
            }
        } else {
            showToast('Fehler beim Speichern des Empfängers: ' + response.message, 'error');
        }
    }
</script>