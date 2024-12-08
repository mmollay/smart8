<?php
include(__DIR__ . '/../n_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$update_id = $_POST['update_id'] ?? null;
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'blacklistForm',
    'action' => 'ajax/form_save.php',
    'method' => 'POST',
    'class' => 'ui form',
    'responseType' => 'json',
    'success' => 'afterFormSubmit(response)'
]);

// Hidden Fields
$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'update_id',
    'value' => $update_id
]);

$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'list_id',
    'value' => 'blacklist'
]);

// E-Mail Eingabe mit Autovervollständigung aus der recipients-Tabelle
$formGenerator->addField([
    'type' => 'input',
    'name' => 'email',
    'label' => 'E-Mail Adresse',
    'required' => true,
    'focus' => true,
    'placeholder' => 'mail@example.com',
    'email' => true,
    'attributes' => [
        'class' => 'search'
    ],
    'error_message' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein'
]);

// Grund für den Blacklist-Eintrag
$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'reason',
    'label' => 'Grund',
    'required' => true,
    'placeholder' => 'Geben Sie hier den Grund für die Aufnahme in die Blacklist ein...',
    'rows' => 3,
    'error_message' => 'Bitte geben Sie einen Grund an'
]);

// Information wenn es ein Update ist
if ($update_id) {
    $formGenerator->addField([
        'type' => 'segment',
        'class' => 'ui info message',
        'html' => '
            <div class="header">Hinweis</div>
            <p>Beim Bearbeiten eines Blacklist-Eintrags wird das Änderungsdatum automatisch aktualisiert.</p>'
    ]);
}

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
        'icon' => 'close',
        'class' => 'ui button'
    ]
]);

// Lade existierende Daten wenn es ein Update ist
if ($update_id) {
    try {
        $formGenerator->loadValuesFromDatabase(
            $db,
            "SELECT * FROM blacklist WHERE id = ? AND user_id = ? LIMIT 1",
            [$update_id, $userId]
        );
    } catch (Exception $e) {
        echo "<div class='ui error message'>Fehler beim Laden der Daten: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Generiere und zeige das Formular
echo $formGenerator->generateJS();
echo $formGenerator->generateForm();
?>

<script>
    $(document).ready(function () {
        // E-Mail Autovervollständigung einrichten
        $('input[name="email"]').search({
            apiSettings: {
                url: 'ajax/search_recipients.php?q={query}',
                onResponse: function (response) {
                    return {
                        results: response.items
                    };
                }
            },
            minCharacters: 2,
            fields: {
                title: 'email',
                description: 'name'
            },
            onSelect: function (result, response) {
                $('input[name="email"]').val(result.email);
                return false;
            }
        });
    });

    function afterFormSubmit(response) {
        if (response.success) {
            showToast('Blacklist-Eintrag erfolgreich gespeichert', 'success');
            $('.ui.modal').modal('hide');
            if (typeof reloadTable === 'function') {
                reloadTable();
            }
        } else {
            showToast('Fehler beim Speichern: ' + response.message, 'error');
        }
    }
</script>

<style>
    .search.input {
        width: 100%;
    }

    .ui.search .prompt {
        border-radius: 4px;
    }

    .ui.info.message {
        margin-top: 1em;
    }
</style>