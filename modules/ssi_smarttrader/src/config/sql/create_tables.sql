-- Datenbank erstellen
CREATE DATABASE IF NOT EXISTS ssi_smarttrader;
USE ssi_smarttrader;

-- Trades Tabelle
CREATE TABLE IF NOT EXISTS trades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    symbol VARCHAR(20) NOT NULL,
    position_type ENUM('long', 'short') NOT NULL,
    entry_price DECIMAL(20,8) NOT NULL,
    position_size DECIMAL(20,8) NOT NULL,
    leverage INT NOT NULL,
    take_profit DECIMAL(20,8) NOT NULL,
    stop_loss DECIMAL(20,8) NOT NULL,
    status ENUM('open', 'closed', 'cancelled') NOT NULL DEFAULT 'open',
    exit_price DECIMAL(20,8) NULL,
    profit_loss DECIMAL(20,8) NULL,
    roi DECIMAL(10,2) NULL,
    entry_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    exit_time TIMESTAMP NULL,
    close_reason VARCHAR(50) NULL,
    order_id VARCHAR(50) NOT NULL,
    INDEX idx_symbol (symbol),
    INDEX idx_status (status),
    INDEX idx_entry_time (entry_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Trade Signale
CREATE TABLE IF NOT EXISTS trade_signals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    trade_id INT NOT NULL,
    signal_type VARCHAR(50) NOT NULL,
    signal_value VARCHAR(255) NOT NULL,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trade_id) REFERENCES trades(id) ON DELETE CASCADE,
    INDEX idx_trade_id (trade_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Technische Indikatoren
CREATE TABLE IF NOT EXISTS technical_indicators (
    id INT PRIMARY KEY AUTO_INCREMENT,
    symbol VARCHAR(20) NOT NULL,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    adx DECIMAL(10,2) NOT NULL,
    plus_di DECIMAL(10,2) NOT NULL,
    minus_di DECIMAL(10,2) NOT NULL,
    atr DECIMAL(20,8) NOT NULL,
    atr_percent DECIMAL(10,2) NOT NULL,
    roc DECIMAL(10,2) NOT NULL,
    volume DECIMAL(20,8) NOT NULL,
    keltner_upper DECIMAL(20,8) NOT NULL,
    keltner_middle DECIMAL(20,8) NOT NULL,
    keltner_lower DECIMAL(20,8) NOT NULL,
    INDEX idx_symbol_time (symbol, timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Performance Metriken
CREATE TABLE IF NOT EXISTS performance_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    symbol VARCHAR(20) NOT NULL,
    total_trades INT NOT NULL DEFAULT 0,
    winning_trades INT NOT NULL DEFAULT 0,
    losing_trades INT NOT NULL DEFAULT 0,
    profit_factor DECIMAL(10,2) NULL,
    win_rate DECIMAL(10,2) NULL,
    avg_win DECIMAL(20,8) NULL,
    avg_loss DECIMAL(20,8) NULL,
    max_drawdown DECIMAL(10,2) NULL,
    sharpe_ratio DECIMAL(10,2) NULL,
    total_profit_loss DECIMAL(20,8) NOT NULL DEFAULT 0,
    UNIQUE KEY idx_date_symbol (date, symbol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System Log
CREATE TABLE IF NOT EXISTS system_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    log_level ENUM('info', 'warning', 'error', 'critical') NOT NULL,
    component VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    details JSON NULL,
    INDEX idx_timestamp (timestamp),
    INDEX idx_level (log_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
