<?php
include __DIR__ . '/../../../smartform2/FormGenerator.php';
include __DIR__ . '/../t_config.php';

$update_id = $_POST['update_id'] ?? null;
$clone_id = $_POST['clone_id'] ?? null;

// Aktuelle Werte laden
$currentValues = [];
if ($update_id || $clone_id) {
    $id = $update_id ?? $clone_id;
    $query = "SELECT * FROM trading_parameter_models WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $currentValues = $row;
        if ($clone_id) {
            unset($currentValues['id']);
            $currentValues['name'] .= ' (Kopie)';
        }

        // Parameter Werte laden
        $values_query = "SELECT parameter_name, parameter_value FROM trading_parameter_model_values WHERE model_id = ?";
        $values_stmt = $db->prepare($values_query);
        $values_stmt->bind_param('i', $id);
        $values_stmt->execute();
        $values_result = $values_stmt->get_result();

        while ($value = $values_result->fetch_assoc()) {
            $currentValues[$value['parameter_name']] = $value['parameter_value'];
        }
    }
}

$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'tradingParametersForm',
    'action' => 'ajax/save_trading_parameters.php',
    'method' => 'POST',
    'class' => 'ui form',
    'responseType' => 'json',
    'success' => 'afterFormSubmit(response)'
]);

// Hidden Fields
$formGenerator->addField(['type' => 'hidden', 'name' => 'update_id', 'value' => $update_id]);
$formGenerator->addField(['type' => 'hidden', 'name' => 'clone_id', 'value' => $clone_id]);

// Tabs definieren
$formGenerator->addField([
    'type' => 'tab',
    'tabs' => [
        'basic' => 'Grundeinstellungen',
        'tp_sl' => 'Take Profit & Stop Loss',
        'trade_size' => 'Handelsgrößen',
        'risk' => 'Risikomanagement',
        'other' => 'Sonstige Parameter'
    ],
    'active' => 'basic'
]);

// Tab: Grundeinstellungen
$formGenerator->addField([
    'type' => 'input',
    'name' => 'name',
    'label' => 'Name',
    'description' => 'Der Name des Trading-Modells. Bitte verwenden Sie einen eindeutigen Namen, um das Modell leicht identifizieren zu können.',
    'required' => true,
    'width' => 8,
    'value' => $currentValues['name'] ?? '',
    'tab' => 'basic'
]);

$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'description',
    'label' => 'Beschreibung',
    'description' => 'Eine kurze Beschreibung des Trading-Modells.',
    'width' => 16,
    'value' => $currentValues['description'] ?? '',
    'tab' => 'basic'
]);

$formGenerator->addField([
    'type' => 'checkbox',
    'name' => 'is_active',
    'label' => 'Aktiv',
    'description' => 'Modell für den Handel aktivieren',
    'width' => 8,
    'value' => $currentValues['is_active'] ?? 1,
    'tab' => 'basic'
]);

// Tab: Take Profit & Stop Loss
$formGenerator->addField([
    'type' => 'input',
    'name' => 'tp_percentage_long',
    'label' => 'Take Profit Long',
    'description' => 'Take Profit für Long-Positionen in %',
    'required' => true,
    'width' => 6,
    'inputType' => 'number',
    'min' => 0.1,
    'max' => 100,
    'step' => 0.1,
    'addon' => '%',
    'value' => $currentValues['tp_percentage_long'] ?? 1.5,
    'tab' => 'tp_sl'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'sl_percentage_long',
    'label' => 'Stop Loss Long',
    'description' => 'Stop Loss für Long-Positionen in %',
    'required' => true,
    'width' => 6,
    'inputType' => 'number',
    'min' => 0.1,
    'max' => 100,
    'step' => 0.1,
    'addon' => '%',
    'value' => $currentValues['sl_percentage_long'] ?? 0.8,
    'tab' => 'tp_sl'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'tp_percentage_short',
    'label' => 'Take Profit Short',
    'description' => 'Take Profit für Short-Positionen in %',
    'required' => true,
    'width' => 6,
    'inputType' => 'number',
    'min' => 0.1,
    'max' => 100,
    'step' => 0.1,
    'addon' => '%',
    'value' => $currentValues['tp_percentage_short'] ?? 1.5,
    'tab' => 'tp_sl'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'sl_percentage_short',
    'label' => 'Stop Loss Short',
    'description' => 'Stop Loss für Short-Positionen in %',
    'required' => true,
    'width' => 6,
    'inputType' => 'number',
    'min' => 0.1,
    'max' => 100,
    'step' => 0.1,
    'addon' => '%',
    'value' => $currentValues['sl_percentage_short'] ?? 0.8,
    'tab' => 'tp_sl'
]);

// Tab: Handelsgrößen
$formGenerator->addField([
    'type' => 'select',
    'name' => 'leverage',
    'label' => 'Hebel',
    'description' => 'Der verwendete Hebel für neue Positionen',
    'required' => true,
    'width' => 6,
    'options' => [
        '5.00' => '5x',
        '10.00' => '10x',
        '20.00' => '20x',
        '50.00' => '50x',
        '100.00' => '100x',
        '125.00' => '125x'
    ],
    'value' => $currentValues['leverage'] ?? '5.00',
    'tab' => 'trade_size'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'position_size',
    'label' => 'Position Size',
    'description' => 'Standard Positionsgröße in ETH',
    'required' => true,
    'width' => 6,
    'inputType' => 'number',
    'min' => 0.001,
    'max' => 10,
    'step' => 0.001,
    'addon' => 'ETH',
    'value' => $currentValues['position_size'] ?? 0.01,
    'tab' => 'trade_size'
]);

// Tab: Risikomanagement
$formGenerator->addField([
    'type' => 'input',
    'name' => 'max_daily_trades',
    'label' => 'Maximale Trades pro Tag',
    'description' => 'Maximale Anzahl von Trades pro Tag',
    'required' => true,
    'width' => 6,
    'inputType' => 'number',
    'min' => 1,
    'max' => 100,
    'step' => 1,
    'value' => $currentValues['max_daily_trades'] ?? 10,
    'tab' => 'risk'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'max_daily_loss',
    'label' => 'Maximaler täglicher Verlust',
    'description' => 'Maximaler Verlust pro Tag in %',
    'required' => true,
    'width' => 6,
    'inputType' => 'number',
    'min' => 0.1,
    'max' => 100,
    'step' => 0.1,
    'addon' => '%',
    'value' => $currentValues['max_daily_loss'] ?? 5.0,
    'tab' => 'risk'
]);

// Tab: Sonstige Parameter
$formGenerator->addField([
    'type' => 'input',
    'name' => 'min_volume_24h',
    'label' => 'Minimales 24h Volumen',
    'description' => 'Minimales Handelsvolumen der letzten 24 Stunden',
    'required' => true,
    'width' => 6,
    'inputType' => 'number',
    'min' => 1000,
    'max' => 1000000000,
    'step' => 1000,
    'addon' => 'USDT',
    'value' => $currentValues['min_volume_24h'] ?? 1000000,
    'tab' => 'other'
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
        'type' => 'button',
        'value' => 'Abbrechen',
        'icon' => 'cancel',
        'class' => 'ui button',
        'onclick' => "$('.ui.modal').modal('hide');"
    ]
], [
    'layout' => 'grouped',
    'alignment' => 'right'
]);

// Form generieren
echo $formGenerator->generateJS();
echo $formGenerator->generateForm();

// JavaScript für Erfolgs-/Fehlermeldungen
?>
<script>
    function afterFormSubmit(response) {
        if (response.success) {
            showToast('Einstellungen erfolgreich gespeichert', 'success');
            $('.ui.modal').modal('hide');
            if (typeof reloadTable === 'function') {
                reloadTable();
            }
        } else {
            showToast('Fehler beim Speichern: ' + response.message, 'error');
        }
    }
</script>