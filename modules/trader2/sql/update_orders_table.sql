-- Orders Tabelle erstellen
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    parameter_model_id INT NOT NULL,
    symbol VARCHAR(20) NOT NULL,
    side ENUM('buy', 'sell') NOT NULL,
    position_size DECIMAL(10,4) NOT NULL,
    entry_price DECIMAL(10,2) NOT NULL,
    take_profit DECIMAL(10,2),
    stop_loss DECIMAL(10,2),
    leverage INT NOT NULL,
    bitget_order_id VARCHAR(50),
    status ENUM('pending', 'placed', 'filled', 'cancelled', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_parameter_model_id (parameter_model_id),
    INDEX idx_symbol (symbol),
    INDEX idx_bitget_order_id (bitget_order_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
