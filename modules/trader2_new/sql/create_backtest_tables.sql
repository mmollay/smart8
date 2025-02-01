USE ssi_trader2;

-- Lösche existierende Tabellen
DROP TABLE IF EXISTS backtest_metrics;
DROP TABLE IF EXISTS backtest_trades;
DROP TABLE IF EXISTS backtest_equity;
DROP TABLE IF EXISTS backtest_runs;

-- Backtest-Runs Tabelle
CREATE TABLE IF NOT EXISTS backtest_runs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    symbol VARCHAR(20) NOT NULL,
    interval_type VARCHAR(10) NOT NULL,
    period INT NOT NULL,
    initial_balance DECIMAL(20,8) NOT NULL,
    fee_rate DECIMAL(10,8) NOT NULL,
    start_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    total_trades INT NOT NULL DEFAULT 0,
    winning_trades INT NOT NULL DEFAULT 0,
    losing_trades INT NOT NULL DEFAULT 0,
    profit_factor DECIMAL(10,4) NULL,
    net_profit DECIMAL(20,8) NULL,
    max_drawdown DECIMAL(10,4) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Trades Tabelle
CREATE TABLE IF NOT EXISTS backtest_trades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    run_id BIGINT UNSIGNED NOT NULL,
    entry_time BIGINT NOT NULL,
    exit_time BIGINT NULL,
    type ENUM('long', 'short') NOT NULL,
    entry_price DECIMAL(20,8) NOT NULL,
    exit_price DECIMAL(20,8) NULL,
    position_size DECIMAL(20,8) NOT NULL,
    profit_loss DECIMAL(20,8) NULL,
    exit_reason VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (run_id) REFERENCES backtest_runs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Equity-Verlauf Tabelle
CREATE TABLE IF NOT EXISTS backtest_equity (
    id INT PRIMARY KEY AUTO_INCREMENT,
    run_id INT NOT NULL,
    timestamp INT NOT NULL,
    equity DECIMAL(20,8) NOT NULL,
    drawdown DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (run_id) REFERENCES backtest_runs(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
