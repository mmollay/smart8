-- Füge Parameter Model ID zur Users Tabelle hinzu
ALTER TABLE users 
ADD COLUMN default_parameter_model_id INT NULL,
ADD CONSTRAINT fk_default_parameter_model 
FOREIGN KEY (default_parameter_model_id) 
REFERENCES trading_parameter_models(id)
ON DELETE SET NULL;
