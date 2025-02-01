-- Tabelle für Marktdaten
CREATE TABLE IF NOT EXISTS market_data (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    symbol VARCHAR(20) NOT NULL,
    interval_type VARCHAR(10) NOT NULL,
    timestamp BIGINT NOT NULL,
    open DECIMAL(20,8) NOT NULL,
    high DECIMAL(20,8) NOT NULL,
    low DECIMAL(20,8) NOT NULL,
    close DECIMAL(20,8) NOT NULL,
    volume DECIMAL(30,8) NOT NULL,
    turnover DECIMAL(30,8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_candle (symbol, interval_type, timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabelle für Backtest-Runs
CREATE TABLE IF NOT EXISTS backtest_runs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    symbol VARCHAR(20) NOT NULL,
    interval_type VARCHAR(10) NOT NULL,
    period INT NOT NULL,
    initial_balance DECIMAL(20,8) NOT NULL,
    fee_rate DECIMAL(10,8) NOT NULL,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NULL,
    total_trades INT DEFAULT 0,
    winning_trades INT DEFAULT 0,
    losing_trades INT DEFAULT 0,
    profit_factor DECIMAL(10,4) DEFAULT 0,
    net_profit DECIMAL(20,8) DEFAULT 0,
    max_drawdown DECIMAL(10,4) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabelle für Backtest-Trades
CREATE TABLE IF NOT EXISTS backtest_trades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    run_id BIGINT UNSIGNED NOT NULL,
    entry_time BIGINT NOT NULL,
    type VARCHAR(10) NOT NULL,
    entry_price DECIMAL(20,8) NOT NULL,
    position_size DECIMAL(20,8) NOT NULL,
    exit_time BIGINT NULL,
    exit_price DECIMAL(20,8) NULL,
    profit_loss DECIMAL(20,8) NULL,
    exit_reason VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (run_id) REFERENCES backtest_runs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabelle für Equity-Verlauf
CREATE TABLE IF NOT EXISTS backtest_equity (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    run_id BIGINT UNSIGNED NOT NULL,
    timestamp BIGINT NOT NULL,
    equity DECIMAL(20,8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (run_id) REFERENCES backtest_runs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabelle für technische Analyse
CREATE TABLE IF NOT EXISTS technical_analysis (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    market_data_id BIGINT UNSIGNED NOT NULL,
    adx DECIMAL(10,4) NOT NULL,
    plus_di DECIMAL(10,4) NOT NULL,
    minus_di DECIMAL(10,4) NOT NULL,
    atr DECIMAL(20,8) NOT NULL,
    roc DECIMAL(10,4) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (market_data_id) REFERENCES market_data(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabelle für Keltner Channels
CREATE TABLE IF NOT EXISTS keltner_channels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    market_data_id BIGINT UNSIGNED NOT NULL,
    upper DECIMAL(20,8) NOT NULL,
    middle DECIMAL(20,8) NOT NULL,
    lower DECIMAL(20,8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (market_data_id) REFERENCES market_data(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabelle für Live-Trades
CREATE TABLE IF NOT EXISTS live_trades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    symbol VARCHAR(20) NOT NULL,
    type VARCHAR(10) NOT NULL,
    entry_time BIGINT NOT NULL,
    entry_price DECIMAL(20,8) NOT NULL,
    position_size DECIMAL(20,8) NOT NULL,
    stop_loss DECIMAL(20,8) NOT NULL,
    take_profit DECIMAL(20,8) NOT NULL,
    exit_time BIGINT NULL,
    exit_price DECIMAL(20,8) NULL,
    profit_loss DECIMAL(20,8) NULL,
    exit_reason VARCHAR(20) NULL,
    risk_reward_ratio DECIMAL(10,4) NOT NULL,
    atr_at_entry DECIMAL(20,8) NOT NULL,
    adx_at_entry DECIMAL(10,4) NOT NULL,
    volume_at_entry DECIMAL(30,8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabelle für tägliche Performance
CREATE TABLE IF NOT EXISTS daily_performance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    starting_balance DECIMAL(20,8) NOT NULL,
    ending_balance DECIMAL(20,8) NOT NULL,
    total_trades INT NOT NULL DEFAULT 0,
    winning_trades INT NOT NULL DEFAULT 0,
    profit_factor DECIMAL(10,4) NOT NULL DEFAULT 0,
    max_drawdown DECIMAL(10,4) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabelle für API-Konfiguration
CREATE TABLE IF NOT EXISTS api_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    api_key VARCHAR(100) NOT NULL,
    api_secret VARCHAR(100) NOT NULL,
    passphrase VARCHAR(100) NULL,
    is_active BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabelle für Trading-Symbole
CREATE TABLE IF NOT EXISTS trading_symbols (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    symbol VARCHAR(20) NOT NULL,
    base_currency VARCHAR(10) NOT NULL,
    quote_currency VARCHAR(10) NOT NULL,
    min_leverage INT NOT NULL DEFAULT 1,
    max_leverage INT NOT NULL DEFAULT 100,
    min_quantity DECIMAL(20,8) NOT NULL,
    max_quantity DECIMAL(20,8) NOT NULL,
    tick_size DECIMAL(20,8) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_symbol (symbol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
