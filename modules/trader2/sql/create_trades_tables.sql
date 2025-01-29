-- Tabelle für abgeschlossene Trades
CREATE TABLE trades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    symbol VARCHAR(20) NOT NULL,
    side VARCHAR(10) NOT NULL,
    size DECIMAL(20,8) NOT NULL,
    price DECIMAL(20,8) NOT NULL,
    fee DECIMAL(20,8) NOT NULL,
    fee_coin VARCHAR(10) NOT NULL,
    trade_id VARCHAR(50) NOT NULL,
    order_id VARCHAR(50) NOT NULL,
    bitget_timestamp BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_trade (trade_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabelle für PnL History
CREATE TABLE pnl_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    symbol VARCHAR(20) NOT NULL,
    side VARCHAR(10) NOT NULL,
    size DECIMAL(20,8) NOT NULL,
    entry_price DECIMAL(20,8) NOT NULL,
    exit_price DECIMAL(20,8) NOT NULL,
    profit DECIMAL(20,8) NOT NULL,
    leverage INT NOT NULL,
    bitget_timestamp BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
