<?php
include(__DIR__ . '/../n_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$update_id = $_POST['update_id'] ?? null;
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'groupForm',
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
    'value' => 'groups'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'name',
    'label' => 'Gruppenname',
    'required' => true,
    'focus' => true,
    'minLength' => 2,
    'maxLength' => 50,
    'error_message' => 'Bitte geben Sie einen Gruppennamen ein.',
    'minLength_error' => 'Der Gruppenname muss mindestens 2 Zeichen lang sein.',
    'maxLength_error' => 'Der Gruppenname darf höchstens 50 Zeichen lang sein.'
]);

$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'description',
    'label' => 'Beschreibung',
    'maxLength' => 255,
    'maxLength_error' => 'Die Beschreibung darf höchstens 255 Zeichen lang sein.'
]);

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'color',
    'label' => 'Farbe',
    'array' => [
        'red' => 'Rot',
        'blue' => 'Blau',
        'green' => 'Grün',
        'yellow' => 'Gelb',
        'purple' => 'Lila'
    ],
    'required' => true,
    'error_message' => 'Bitte wählen Sie eine Farbe aus.'
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
    try {
        $formGenerator->loadValuesFromDatabase($db, "SELECT * FROM groups WHERE id = ? LIMIT 1", [$update_id]);
    } catch (Exception $e) {
        echo "<div class='ui error message'>Fehler beim Laden der Gruppendaten: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();

// Zusätzliches JavaScript für die Fehlerbehandlung und den Erfolgsfall
?>
<script>
    function afterFormSubmit(response) {
        if (response.success) {
            showToast('Gruppe erfolgreich gespeichert', 'success');
            // Hier können Sie zusätzliche Aktionen nach erfolgreicher Speicherung hinzufügen
            // z.B. Modal schließen, Liste aktualisieren, etc.
            $('.ui.modal').modal('hide');
            if (typeof reloadTable === 'function') {
                reloadTable();
            }
        } else {
            showToast('Fehler beim Speichern der Gruppe: ' + response.message, 'error');
        }
    }
</script>