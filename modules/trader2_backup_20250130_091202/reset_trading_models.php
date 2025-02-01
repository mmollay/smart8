<?php
include __DIR__ . '/t_config.php';

// Alte Models und Parameter löschen
$db->query("DELETE FROM trading_parameter_model_values");
$db->query("DELETE FROM trading_parameter_models");

// Neue Models anlegen
$models = [
    [
        'name' => 'Konservativ ETH',
        'description' => 'Konservatives ETH Trading mit geringem Risiko und moderatem Hebel',
        'is_active' => 1
    ],
    [
        'name' => 'Moderat ETH',
        'description' => 'Ausgewogenes ETH Trading mit mittlerem Risiko und Hebel',
        'is_active' => 1
    ],
    [
        'name' => 'Aggressiv ETH',
        'description' => 'Aggressives ETH Trading mit höherem Risiko und Hebel',
        'is_active' => 1
    ]
];

// Models einfügen und IDs speichern
$model_ids = [];
foreach ($models as $model) {
    $stmt = $db->prepare("INSERT INTO trading_parameter_models (name, description, is_active) VALUES (?, ?, ?)");
    $stmt->bind_param('ssi', $model['name'], $model['description'], $model['is_active']);
    $stmt->execute();
    $model_ids[] = $db->insert_id;
}

// Parameter für jedes Model
$parameters = [
    // Konservativ
    [
        'model_id' => $model_ids[0],
        'params' => [
            ['default_trade_size', 0.02],
            ['default_leverage', 5],
            ['tp_percentage_long', 1.0],
            ['sl_percentage_long', 0.5],
            ['tp_percentage_short', 1.0],
            ['sl_percentage_short', 0.5]
        ]
    ],
    // Moderat
    [
        'model_id' => $model_ids[1],
        'params' => [
            ['default_trade_size', 0.05],
            ['default_leverage', 10],
            ['tp_percentage_long', 1.5],
            ['sl_percentage_long', 0.8],
            ['tp_percentage_short', 1.5],
            ['sl_percentage_short', 0.8]
        ]
    ],
    // Aggressiv
    [
        'model_id' => $model_ids[2],
        'params' => [
            ['default_trade_size', 0.1],
            ['default_leverage', 20],
            ['tp_percentage_long', 2.0],
            ['sl_percentage_long', 1.0],
            ['tp_percentage_short', 2.0],
            ['sl_percentage_short', 1.0]
        ]
    ]
];

// Parameter einfügen
$param_stmt = $db->prepare("INSERT INTO trading_parameter_model_values (model_id, parameter_name, parameter_value) VALUES (?, ?, ?)");
foreach ($parameters as $model_params) {
    foreach ($model_params['params'] as $param) {
        $param_stmt->bind_param('isd', $model_params['model_id'], $param[0], $param[1]);
        $param_stmt->execute();
    }
}

echo "Trading Models wurden erfolgreich zurückgesetzt!\n";
