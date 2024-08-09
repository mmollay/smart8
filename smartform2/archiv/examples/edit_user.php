<?php
require_once '../FormGenerator.php';
require_once __DIR__ . '/mysql.php';

// FormGenerator initialisieren
$formGenerator = new FormGenerator();

// Formular-Daten setzen
$formGenerator->setFormData([
    'id' => 'editUserForm',
    'action' => 'save_user.php',
    'method' => 'POST',
    'class' => 'ui form',
    'responseType' => 'json',
    'success' => "
        if (response.success) {
            showToast(response.message, 'success');
            $('#martin_modal_editUser').modal('hide');
            if (typeof reloadListGenerator === 'function') {
                reloadListGenerator('martin');
            }
        } else {
            showToast(response.message, 'error');
        }
    "
]);

// Felder hinzufügen
$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'id'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'first_name',
    'label' => 'Vorname',
    'placeholder' => 'Geben Sie den Vornamen ein',
    'required' => true
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'last_name',
    'label' => 'Nachname',
    'placeholder' => 'Geben Sie den Nachnamen ein',
    'required' => true
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'email',
    'label' => 'E-Mail',
    'placeholder' => 'Geben Sie die E-Mail-Adresse ein',
    'required' => true
]);

$formGenerator->addField([
    'type' => 'button',
    'name' => 'submit',
    'value' => 'Speichern',
    'class' => 'ui primary button'
]);

$formGenerator->loadValuesFromDatabase($db, "SELECT * FROM users WHERE id = ?", [1]);

// Formular generieren
echo $formGenerator->generateForm();

// JavaScript-Code generieren
echo $formGenerator->generateJS();
?>

<script>
    function showToast(message, type) {
        // Implementieren Sie hier Ihre Toast-Nachricht-Funktionalität
        alert(message);
    }
</script>