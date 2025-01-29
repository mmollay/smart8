-- API Felder zur users Tabelle hinzufügen
ALTER TABLE users 
ADD COLUMN api_key VARCHAR(100) AFTER email,
ADD COLUMN api_secret VARCHAR(100) AFTER api_key,
ADD COLUMN passphrase VARCHAR(100) AFTER api_secret;
