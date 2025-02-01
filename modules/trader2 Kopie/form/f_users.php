<?php
include __DIR__ . '/../../../smartform2/FormGenerator.php';
include __DIR__ . '/../t_config.php';

$update_id = $_POST['update_id'] ?? null;
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'userForm',
    'action' => 'ajax/form_save.php',
    'method' => 'POST',
    'class' => 'ui form',
    'responseType' => 'json',
    'success' => 'afterFormSubmit(response)'
]);

// Tabs definieren
$formGenerator->addField([
    'type' => 'tab',
    'tabs' => [
        'access' => 'Zugangsdaten',
        'contact' => 'Kontaktdaten',
        'bank' => 'Bankverbindung',
        'api' => 'API-Zugang',
        'trading' => 'Trading'
    ],
    'active' => 'access'
]);

// Hidden Fields
$formGenerator->addField(['type' => 'hidden', 'name' => 'update_id', 'value' => $update_id]);
$formGenerator->addField(['type' => 'hidden', 'name' => 'list_id', 'value' => 'users']);

// Tab: Zugangsdaten
$formGenerator->addFieldGroup('credentials', [
    [
        'type' => 'input',
        'name' => 'username',
        'leftLabel' => 'Benutzername',
        'leftLabelClass' => 'ui label fixed-width-label',
        'required' => true,
        'width' => 8
    ],
    [
        'type' => 'input',
        'name' => 'email',
        'leftLabel' => 'E-Mail',
        'leftLabelClass' => 'ui label fixed-width-label',
        'required' => true,
        'email' => true,
        'width' => 8
    ],
    [
        'type' => 'password',
        'name' => 'password',
        'leftLabel' => $update_id ? 'Passwort (leer = keine Änderung)' : 'Passwort',
        'leftLabelClass' => 'ui label fixed-width-label',
        'required' => !$update_id,
        'width' => 8
    ],
    [
        'type' => 'checkbox',
        'name' => 'active',
        'label' => 'Benutzer aktiv',
        'style' => 'toggle',
        'checked' => true
    ]
], [
    'wrapper' => 'ui segment'
], 'access');

// Tab: Kontaktdaten
$formGenerator->addFieldGroup('contact', [
    [
        'type' => 'input',
        'name' => 'company',
        'leftLabel' => 'Firma',
        'leftLabelClass' => 'ui label fixed-width-label',
        'width' => 8
    ],
    [
        'type' => 'input',
        'name' => 'first_name',
        'leftLabel' => 'Vorname',
        'leftLabelClass' => 'ui label fixed-width-label',
        'required' => true,
        'width' => 8
    ],
    [
        'type' => 'input',
        'name' => 'last_name',
        'leftLabel' => 'Nachname',
        'leftLabelClass' => 'ui label fixed-width-label',
        'required' => true,
        'width' => 8
    ],
    [
        'type' => 'input',
        'name' => 'phone',
        'leftLabel' => 'Telefon',
        'leftLabelClass' => 'ui label fixed-width-label',
        'width' => 8
    ]
], [
    'title' => 'Persönliche Daten',
    'wrapper' => 'ui segment'
], 'contact');

$formGenerator->addFieldGroup('address', [
    [
        'type' => 'input',
        'name' => 'address_street',
        'leftLabel' => 'Straße',
        'leftLabelClass' => 'ui label fixed-width-label',
        'width' => 8
    ],
    [
        'type' => 'input',
        'name' => 'address_number',
        'leftLabel' => 'Hausnummer',
        'leftLabelClass' => 'ui label fixed-width-label',
        'width' => 4
    ],
    [
        'type' => 'input',
        'name' => 'address_zip',
        'leftLabel' => 'PLZ',
        'leftLabelClass' => 'ui label fixed-width-label',
        'width' => 4
    ],
    [
        'type' => 'input',
        'name' => 'address_city',
        'leftLabel' => 'Stadt',
        'leftLabelClass' => 'ui label fixed-width-label',
        'width' => 8
    ],
    [
        'type' => 'dropdown',
        'array' => ['de' => 'Deutschland', 'at' => 'Österreich', 'ch' => 'Schweiz'],
        'name' => 'address_country',
        'leftLabel' => 'Land',
        'leftLabelClass' => 'ui label fixed-width-label',
        'width' => 8
    ]
], [
    'title' => 'Adresse',
    'wrapper' => 'ui segment'
], 'contact');

// Tab: Bankverbindung
$formGenerator->addFieldGroup('bank', [
    [
        'type' => 'input',
        'name' => 'bank_name',
        'leftLabel' => 'Bank',
        'leftLabelClass' => 'ui label fixed-width-label',
        'width' => 8
    ],
    [
        'type' => 'input',
        'name' => 'iban',
        'leftLabel' => 'IBAN',
        'leftLabelClass' => 'ui label fixed-width-label',
        'width' => 8
    ],
    [
        'type' => 'input',
        'name' => 'bic',
        'leftLabel' => 'BIC',
        'leftLabelClass' => 'ui label fixed-width-label',
        'width' => 8
    ],
    [
        'type' => 'input',
        'name' => 'account_holder',
        'leftLabel' => 'Kontoinhaber',
        'leftLabelClass' => 'ui label fixed-width-label',
        'width' => 8
    ]
], [
    'wrapper' => 'ui segment'
], 'bank');

// Tab: API-Zugang
$formGenerator->addField([
    'type' => 'segment',
    'content' => 'API-Zugangsdaten für Trading',
    'tab' => 'api',
    'wrapper' => 'ui segment'
]);

$formGenerator->addFieldGroup('api_credentials', [
    [
        'type' => 'select',
        'name' => 'platform',
        'leftLabel' => 'Plattform',
        'leftLabelClass' => 'ui label fixed-width-label',
        'required' => true,
        'width' => 12,
        'options' => [
            'bitget' => 'Bitget',
            'binance' => 'Binance',
            'bybit' => 'Bybit'
        ]
    ],
    [
        'type' => 'input',
        'name' => 'api_key',
        'leftLabel' => 'API Key',
        'leftLabelClass' => 'ui label fixed-width-label',
        'required' => true,
        'width' => 12
    ],
    [
        'type' => 'input',
        'name' => 'api_secret',
        'leftLabel' => 'API Secret',
        'leftLabelClass' => 'ui label fixed-width-label',
        'required' => true,
        'width' => 12
    ],
    [
        'type' => 'input',
        'name' => 'api_passphrase',
        'leftLabel' => 'Passphrase',
        'leftLabelClass' => 'ui label fixed-width-label',
        'required' => true,
        'width' => 12
    ]
], [
    'wrapper' => 'ui segment'
], 'api');

$formGenerator->addField([
    'type' => 'html',
    'content' => '
        <div class="ui info message">
            <div class="header">API-Zugangsdaten</div>
            <ul class="list">
                <li>Die API-Zugangsdaten werden für den automatischen Trading-Zugang benötigt.</li>
                <li>Sie können die API-Zugangsdaten in Ihrem Trading-Account erstellen.</li>
                <li>Bitte aktivieren Sie die Trading-Berechtigung für die API.</li>
                <li>Die Passphrase ist ein zusätzliches Sicherheitsmerkmal und wird bei der API-Erstellung festgelegt.</li>
            </ul>
        </div>
    ',
    'tab' => 'api'
]);

// Tab: Trading
$models_query = "SELECT id, name FROM trading_parameter_models WHERE is_active = 1 ORDER BY name";
$models_result = $db->query($models_query);
$models = ['' => '--Model wählen--'];
while ($row = $models_result->fetch_assoc()) {
    $models[$row['id']] = $row['name'];
}

$formGenerator->addField([
    'type' => 'select',
    'name' => 'default_parameter_model_id',
    'tab' => 'trading',
    'leftLabel' => 'Model',
    'leftLabelClass' => 'ui label fixed-width-label',
    'options' => $models,
    'required' => false,
    'value' => $currentValues['default_parameter_model_id'] ?? '',
    'placeholder' => '--Model wählen--'
]);

// Buttons
$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'value' => 'Speichern',
        'class' => 'ui primary button',
        'icon' => 'save'
    ],
    [
        'value' => 'Abbrechen',
        'class' => 'ui button',
        'icon' => 'cancel',
        'onclick' => "$('.ui.modal').modal('hide');"
    ]
], [
    'layout' => 'grouped',
    'alignment' => 'right'
]);

// Daten laden
if ($update_id) {
    $sql = "SELECT u.*, 
            b.bank_name, b.iban, b.bic, b.account_holder,
            a.platform, a.api_key, a.api_secret, a.api_passphrase, a.description as api_description, a.is_active as api_active
            FROM users u 
            LEFT JOIN bank_accounts b ON u.id = b.user_id AND b.is_primary = 1
            LEFT JOIN api_credentials a ON u.id = a.user_id AND a.is_active = 1
            WHERE u.id = ?";

    $formGenerator->loadValuesFromDatabase($db, $sql, [$update_id]);
}

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();

// CSS für fixed-width Label
echo "
<style>
    .fixed-width-label {
        width: 120px !important;
    }
</style>
";

?>

<script>
    function afterFormSubmit(response) {
        if (response.success) {
            showToast('Benutzer erfolgreich gespeichert', 'success');
            $('.ui.modal').modal('hide');
            if (typeof reloadTable === 'function') {
                reloadTable();
            }
        } else {
            showToast('Fehler beim Speichern: ' + response.message, 'error');
        }
    }
</script>