-- Alte Models und Parameter löschen
DELETE FROM trading_parameter_model_values;
DELETE FROM trading_parameter_models;

-- Neue Models anlegen
INSERT INTO trading_parameter_models (name, description, is_active) VALUES
('Konservativ ETH', 'Konservatives ETH Trading mit geringem Risiko und moderatem Hebel', 1),
('Moderat ETH', 'Ausgewogenes ETH Trading mit mittlerem Risiko und Hebel', 1),
('Aggressiv ETH', 'Aggressives ETH Trading mit höherem Risiko und Hebel', 1);

-- Parameter für Konservatives Modell
INSERT INTO trading_parameter_model_values (model_id, parameter_name, parameter_value) VALUES
(1, 'default_trade_size', 0.02),
(1, 'default_leverage', 5),
(1, 'tp_percentage_long', 1.0),
(1, 'sl_percentage_long', 0.5),
(1, 'tp_percentage_short', 1.0),
(1, 'sl_percentage_short', 0.5);

-- Parameter für Moderates Modell
INSERT INTO trading_parameter_model_values (model_id, parameter_name, parameter_value) VALUES
(2, 'default_trade_size', 0.05),
(2, 'default_leverage', 10),
(2, 'tp_percentage_long', 1.5),
(2, 'sl_percentage_long', 0.8),
(2, 'tp_percentage_short', 1.5),
(2, 'sl_percentage_short', 0.8);

-- Parameter für Aggressives Modell
INSERT INTO trading_parameter_model_values (model_id, parameter_name, parameter_value) VALUES
(3, 'default_trade_size', 0.1),
(3, 'default_leverage', 20),
(3, 'tp_percentage_long', 2.0),
(3, 'sl_percentage_long', 1.0),
(3, 'tp_percentage_short', 2.0),
(3, 'sl_percentage_short', 1.0);
