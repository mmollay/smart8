<?php
// Basis-Konfiguration
define('MODULE_PATH', dirname(__DIR__));
define('ROOT_PATH', dirname(dirname(__DIR__)));

// Datenbank-Konfiguration
define('DB_NAME', 'ssi_smarttrader');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_HOST', 'localhost');

// BitGet API Konfiguration
define('BITGET_API_KEY', 'bg_cc89302322ccb5c2c3942f70dfbd8d2e');
define('BITGET_SECRET_KEY', 'c034f42fe42bec1b57982ee642fbf3f339c9b4eb6dd5ff0a68f81fd11ee0cde2');
define('BITGET_PASSPHRASE', 'MCmaster23');

// Trading Konfiguration
define('DEFAULT_SYMBOL', 'ETHUSDT_UMCBL');
define('DEFAULT_LEVERAGE', 10);

// Indikatoren-Konfiguration
define('MIN_ADX', 25);
define('MAX_ADX', 50);
define('MIN_DI_DIFF', 5);
define('MAX_ATR_PERCENT', 2.0);
define('MIN_ROC', 0.5);
define('MAX_ROC', 10.0);
define('MIN_VOLUME', 0.3);

// Position Sizing
define('BASE_POSITION_SIZE', 0.10); // 10% des verfügbaren Kapitals
define('MAX_POSITION_SIZE', 0.15); // 15% des Gesamtkapitals
define('VOLATILITY_REDUCTION', 0.30); // 30% Reduzierung bei hoher Volatilität
define('TREND_INCREASE', 0.20); // 20% Erhöhung bei optimalem Trend

// Stop-Loss & Take-Profit
define('SL_ATR_MULTIPLIER', 1.5);
define('SL_ATR_MULTIPLIER_HIGH_VOL', 2.0);
define('MIN_SL_DISTANCE', 0.01); // 1% vom Eintrittspreis
define('MIN_RR_RATIO', 2.0); // Mindest-Risiko-Reward-Verhältnis
define('STRONG_TREND_RR_RATIO', 2.5);
define('MAX_TP_DISTANCE', 0.05); // 5% vom Eintrittspreis

// Risikomanagement
define('MAX_DRAWDOWN', 0.05); // 5% maximaler Drawdown
define('MAX_LOSS_PER_TRADE', 0.02); // 2% maximaler Verlust pro Trade
define('MIN_TRADE_INTERVAL', 3600); // 1 Stunde Mindestabstand zwischen Trades
define('MAX_OPEN_POSITIONS', 1);
define('DAILY_LOSS_LIMIT', 0.03); // 3% tägliches Verlustlimit

// Performance-Anforderungen
define('MIN_PROFIT_FACTOR', 1.5);
define('MIN_WIN_RATE', 55);
define('MAX_ALLOWED_DRAWDOWN', 15);
define('MIN_SHARPE_RATIO', 1.2);

// Backtesting-Konfiguration
define('BACKTEST_MIN_TRADES', 100);
define('BACKTEST_MIN_MONTHS', 6);
define('TRADING_FEE', 0.0006); // 0.06% Handelsgebühr

// Logging & Debugging
define('LOG_LEVEL', 'info'); // info, warning, error, critical
define('DEBUG_MODE', true);

// Zeitzone
date_default_timezone_set('Europe/Berlin');

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Autoloader
spl_autoload_register(function ($class) {
    $file = MODULE_PATH . '/src/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
