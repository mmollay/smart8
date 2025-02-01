-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 30. Jan 2025 um 09:04
-- Server-Version: 10.4.28-MariaDB
-- PHP-Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `ssi_trader2`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `account_transactions`
--

CREATE TABLE `account_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('deposit','withdrawal') NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `status` varchar(20) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `bitget_timestamp` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `analysis_signals`
--

CREATE TABLE `analysis_signals` (
  `id` int(11) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  `action` enum('buy','sell','hold') NOT NULL,
  `confidence` decimal(5,2) NOT NULL,
  `entry_price` decimal(20,8) NOT NULL,
  `tp_price` decimal(20,8) NOT NULL,
  `sl_price` decimal(20,8) NOT NULL,
  `reasoning` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reasoning`)),
  `result` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `api_credentials`
--

CREATE TABLE `api_credentials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `platform` varchar(50) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `api_secret` varchar(255) NOT NULL,
  `api_passphrase` varchar(40) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `iban` varchar(34) NOT NULL,
  `bic` varchar(11) DEFAULT NULL,
  `account_holder` varchar(100) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `market_data`
--

CREATE TABLE `market_data` (
  `id` int(11) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `volume` decimal(20,8) DEFAULT NULL,
  `rsi` decimal(10,4) DEFAULT NULL,
  `ema20` decimal(20,8) DEFAULT NULL,
  `ema50` decimal(20,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parameter_model_id` int(11) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `side` enum('buy','sell') NOT NULL,
  `position_size` decimal(10,4) NOT NULL,
  `entry_price` decimal(10,2) NOT NULL,
  `take_profit` decimal(10,2) DEFAULT NULL,
  `stop_loss` decimal(10,2) DEFAULT NULL,
  `leverage` int(11) NOT NULL,
  `bitget_order_id` varchar(50) DEFAULT NULL,
  `status` enum('pending','placed','filled','cancelled','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  `closing_price` decimal(10,2) DEFAULT NULL,
  `pnl` decimal(10,2) DEFAULT NULL,
  `tp_order_id` varchar(50) DEFAULT NULL,
  `sl_order_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pnl_history`
--

CREATE TABLE `pnl_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `side` varchar(10) NOT NULL,
  `size` decimal(20,8) NOT NULL,
  `entry_price` decimal(20,8) NOT NULL,
  `exit_price` decimal(20,8) NOT NULL,
  `profit` decimal(20,8) NOT NULL,
  `net_profit` decimal(20,8) DEFAULT 0.00000000,
  `leverage` int(11) NOT NULL,
  `bitget_timestamp` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `trades`
--

CREATE TABLE `trades` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `side` varchar(10) NOT NULL,
  `size` decimal(20,8) NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `fee` decimal(20,8) NOT NULL,
  `profit` decimal(20,8) DEFAULT NULL,
  `fee_coin` varchar(10) NOT NULL,
  `trade_id` varchar(50) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `bitget_timestamp` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `trading_parameter_models`
--

CREATE TABLE `trading_parameter_models` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `trading_parameter_model_values`
--

CREATE TABLE `trading_parameter_model_values` (
  `id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `parameter_name` varchar(50) NOT NULL,
  `parameter_value` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `trading_triggers`
--

CREATE TABLE `trading_triggers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `trigger_type` varchar(50) NOT NULL,
  `condition_type` varchar(20) NOT NULL,
  `target_price` decimal(20,8) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_pending` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company` varchar(200) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_street` varchar(100) DEFAULT NULL,
  `address_number` varchar(10) DEFAULT NULL,
  `address_zip` varchar(10) DEFAULT NULL,
  `address_city` varchar(50) DEFAULT NULL,
  `address_country` varchar(50) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active` tinyint(1) DEFAULT 1,
  `default_parameter_model_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `account_transactions`
--
ALTER TABLE `account_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `analysis_signals`
--
ALTER TABLE `analysis_signals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_symbol_timestamp` (`symbol`,`timestamp`);

--
-- Indizes für die Tabelle `api_credentials`
--
ALTER TABLE `api_credentials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_platform` (`user_id`,`platform`,`api_key`),
  ADD KEY `idx_api_credentials_user` (`user_id`);

--
-- Indizes für die Tabelle `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bank_accounts_user` (`user_id`);

--
-- Indizes für die Tabelle `market_data`
--
ALTER TABLE `market_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `symbol_timestamp` (`symbol`,`timestamp`);

--
-- Indizes für die Tabelle `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_parameter_model_id` (`parameter_model_id`),
  ADD KEY `idx_symbol` (`symbol`),
  ADD KEY `idx_bitget_order_id` (`bitget_order_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indizes für die Tabelle `pnl_history`
--
ALTER TABLE `pnl_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `trades`
--
ALTER TABLE `trades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_trade` (`trade_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `trading_parameter_models`
--
ALTER TABLE `trading_parameter_models`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `trading_parameter_model_values`
--
ALTER TABLE `trading_parameter_model_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_id` (`model_id`);

--
-- Indizes für die Tabelle `trading_triggers`
--
ALTER TABLE `trading_triggers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `fk_default_parameter_model` (`default_parameter_model_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `account_transactions`
--
ALTER TABLE `account_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `analysis_signals`
--
ALTER TABLE `analysis_signals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `api_credentials`
--
ALTER TABLE `api_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `market_data`
--
ALTER TABLE `market_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `pnl_history`
--
ALTER TABLE `pnl_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `trades`
--
ALTER TABLE `trades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `trading_parameter_models`
--
ALTER TABLE `trading_parameter_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `trading_parameter_model_values`
--
ALTER TABLE `trading_parameter_model_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `trading_triggers`
--
ALTER TABLE `trading_triggers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `account_transactions`
--
ALTER TABLE `account_transactions`
  ADD CONSTRAINT `account_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints der Tabelle `api_credentials`
--
ALTER TABLE `api_credentials`
  ADD CONSTRAINT `api_credentials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD CONSTRAINT `bank_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `pnl_history`
--
ALTER TABLE `pnl_history`
  ADD CONSTRAINT `pnl_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints der Tabelle `trades`
--
ALTER TABLE `trades`
  ADD CONSTRAINT `trades_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints der Tabelle `trading_parameter_model_values`
--
ALTER TABLE `trading_parameter_model_values`
  ADD CONSTRAINT `trading_parameter_model_values_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `trading_parameter_models` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `trading_triggers`
--
ALTER TABLE `trading_triggers`
  ADD CONSTRAINT `trading_triggers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints der Tabelle `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_default_parameter_model` FOREIGN KEY (`default_parameter_model_id`) REFERENCES `trading_parameter_models` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
