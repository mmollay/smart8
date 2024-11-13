-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 12. Nov 2024 um 07:11
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
-- Datenbank: `ssi_newsletter`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `email_contents`
--

CREATE TABLE `email_contents` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `track_opens` tinyint(1) DEFAULT 1,
  `track_clicks` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sender_id` int(11) NOT NULL,
  `send_status` tinyint(1) DEFAULT 0,
  `html_content` mediumtext DEFAULT NULL,
  `text_content` mediumtext DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  `scheduled_for` timestamp NULL DEFAULT NULL,
  `repeat_type` enum('none','daily','weekly','monthly') DEFAULT 'none',
  `repeat_until` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `email_content_groups`
--

CREATE TABLE `email_content_groups` (
  `email_content_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `email_jobs`
--

CREATE TABLE `email_jobs` (
  `id` int(11) NOT NULL,
  `content_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `data_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','sent','success','failed','bounce','send','open','click','unsub','blocked','spam','deferred','delivered') DEFAULT 'pending',
  `message_id` varchar(255) DEFAULT NULL,
  `error_message` varchar(255) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `status` enum('pending','success','failed','bounce','send','open','click','unsub','blocked','spam','deferred','delivered') DEFAULT NULL,
  `response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `html_content` mediumtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `email_tracking`
--

CREATE TABLE `email_tracking` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `event_type` enum('open','click','unsubscribe','bounce','spam') NOT NULL,
  `event_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`event_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `color` varchar(20) DEFAULT 'grey',
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `newsletter_attachments`
--

CREATE TABLE `newsletter_attachments` (
  `id` int(11) NOT NULL,
  `newsletter_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `recipients`
--

CREATE TABLE `recipients` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `unsubscribe_token` varchar(64) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unsubscribed` tinyint(1) DEFAULT 0,
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `bounce_status` enum('none','soft','hard') DEFAULT 'none',
  `last_bounce_at` timestamp NULL DEFAULT NULL,
  `opt_in_status` enum('none','single','double') DEFAULT 'none',
  `opt_in_date` timestamp NULL DEFAULT NULL,
  `last_engagement_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `recipient_group`
--

CREATE TABLE `recipient_group` (
  `recipient_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `senders`
--

CREATE TABLE `senders` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `test_email` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `smtp_host` varchar(255) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT NULL,
  `smtp_user` varchar(255) DEFAULT NULL,
  `smtp_password` varchar(255) DEFAULT NULL,
  `smtp_encryption` enum('none','ssl','tls') DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `senders`
--
DELIMITER $$
CREATE TRIGGER `before_sender_insert` BEFORE INSERT ON `senders` FOR EACH ROW BEGIN
    IF NEW.test_email IS NOT NULL THEN
        -- Prüfe ob die Test-Email bereits als Empfänger existiert
        IF NOT EXISTS (SELECT 1 FROM recipients WHERE email = NEW.test_email) THEN
            -- Wenn nicht, füge sie als neuen Empfänger hinzu
            INSERT INTO recipients (email, first_name, last_name, comment)
            VALUES (NEW.test_email, 'Test', 'Empfänger', 'Automatisch erstellt für Test-Mails');
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_sender_test_email_update` BEFORE UPDATE ON `senders` FOR EACH ROW BEGIN
    IF NEW.test_email IS NOT NULL AND NEW.test_email != OLD.test_email THEN
        -- Prüfe ob die Test-Email bereits als Empfänger existiert
        IF NOT EXISTS (SELECT 1 FROM recipients WHERE email = NEW.test_email) THEN
            -- Wenn nicht, füge sie als neuen Empfänger hinzu
            INSERT INTO recipients (email, first_name, last_name, comment)
            VALUES (NEW.test_email, 'Test', 'Empfänger', 'Automatisch erstellt für Test-Mails');
        END IF;
        
        -- Verknüpfe den Sender mit dem Test-Empfänger
        SET @recipient_id = (SELECT id FROM recipients WHERE email = NEW.test_email);
        INSERT IGNORE INTO test_recipients (sender_id, recipient_id)
        VALUES (NEW.id, @recipient_id);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `status_log`
--

CREATE TABLE `status_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL,
  `message_id` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `test_recipients`
--

CREATE TABLE `test_recipients` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `unsubscribe_log`
--

CREATE TABLE `unsubscribe_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `recipient_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `content_id` int(10) UNSIGNED NOT NULL,
  `message_id` varchar(255) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `email_contents`
--
ALTER TABLE `email_contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `idx_send_status` (`send_status`);

--
-- Indizes für die Tabelle `email_content_groups`
--
ALTER TABLE `email_content_groups`
  ADD PRIMARY KEY (`email_content_id`,`group_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indizes für die Tabelle `email_jobs`
--
ALTER TABLE `email_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_id` (`content_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `idx_status_created` (`status`,`created_at`),
  ADD KEY `idx_content_status` (`content_id`,`status`),
  ADD KEY `idx_message_id` (`message_id`),
  ADD KEY `idx_sent_at` (`sent_at`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indizes für die Tabelle `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indizes für die Tabelle `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `email_tracking`
--
ALTER TABLE `email_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indizes für die Tabelle `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indizes für die Tabelle `newsletter_attachments`
--
ALTER TABLE `newsletter_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `newsletter_id` (`newsletter_id`),
  ADD KEY `idx_newsletter_count` (`newsletter_id`);

--
-- Indizes für die Tabelle `recipients`
--
ALTER TABLE `recipients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_unsubscribe_token` (`unsubscribe_token`);

--
-- Indizes für die Tabelle `recipient_group`
--
ALTER TABLE `recipient_group`
  ADD PRIMARY KEY (`recipient_id`,`group_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indizes für die Tabelle `senders`
--
ALTER TABLE `senders`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `status_log`
--
ALTER TABLE `status_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_id` (`message_id`),
  ADD KEY `idx_event` (`event`),
  ADD KEY `idx_email` (`email`);

--
-- Indizes für die Tabelle `test_recipients`
--
ALTER TABLE `test_recipients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_sender_recipient` (`sender_id`,`recipient_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indizes für die Tabelle `unsubscribe_log`
--
ALTER TABLE `unsubscribe_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipient` (`recipient_id`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `email_contents`
--
ALTER TABLE `email_contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `email_jobs`
--
ALTER TABLE `email_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `email_tracking`
--
ALTER TABLE `email_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `newsletter_attachments`
--
ALTER TABLE `newsletter_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `recipients`
--
ALTER TABLE `recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `senders`
--
ALTER TABLE `senders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `status_log`
--
ALTER TABLE `status_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `test_recipients`
--
ALTER TABLE `test_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `unsubscribe_log`
--
ALTER TABLE `unsubscribe_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `email_contents`
--
ALTER TABLE `email_contents`
  ADD CONSTRAINT `email_contents_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `senders` (`id`);

--
-- Constraints der Tabelle `email_content_groups`
--
ALTER TABLE `email_content_groups`
  ADD CONSTRAINT `email_content_groups_ibfk_1` FOREIGN KEY (`email_content_id`) REFERENCES `email_contents` (`id`),
  ADD CONSTRAINT `email_content_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`);

--
-- Constraints der Tabelle `email_jobs`
--
ALTER TABLE `email_jobs`
  ADD CONSTRAINT `email_jobs_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `email_contents` (`id`),
  ADD CONSTRAINT `email_jobs_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `senders` (`id`),
  ADD CONSTRAINT `email_jobs_ibfk_3` FOREIGN KEY (`recipient_id`) REFERENCES `recipients` (`id`);

--
-- Constraints der Tabelle `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `email_jobs` (`id`);

--
-- Constraints der Tabelle `email_tracking`
--
ALTER TABLE `email_tracking`
  ADD CONSTRAINT `email_tracking_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `email_jobs` (`id`),
  ADD CONSTRAINT `email_tracking_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `recipients` (`id`);

--
-- Constraints der Tabelle `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `groups` (`id`);

--
-- Constraints der Tabelle `newsletter_attachments`
--
ALTER TABLE `newsletter_attachments`
  ADD CONSTRAINT `newsletter_attachments_ibfk_1` FOREIGN KEY (`newsletter_id`) REFERENCES `email_contents` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `recipient_group`
--
ALTER TABLE `recipient_group`
  ADD CONSTRAINT `recipient_group_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `recipients` (`id`),
  ADD CONSTRAINT `recipient_group_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`);

--
-- Constraints der Tabelle `test_recipients`
--
ALTER TABLE `test_recipients`
  ADD CONSTRAINT `test_recipients_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `senders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `test_recipients_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `recipients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
