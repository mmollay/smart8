<?php

class DatabaseHandler {
    private $connection;
    private static $instance = null;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        // Verwende die Konstanten aus t_config.php
        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
            throw new Exception('Datenbank-Konfiguration nicht gefunden');
        }
        
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->connection->connect_error) {
            error_log("Datenbankverbindungsfehler: " . $this->connection->connect_error);
            throw new Exception('Datenbankverbindung fehlgeschlagen: ' . $this->connection->connect_error);
        }
        
        $this->connection->set_charset('utf8mb4');
        
        // Wähle die Datenbank aus
        if (!$this->connection->select_db(DB_NAME)) {
            throw new Exception('Datenbank konnte nicht ausgewählt werden: ' . DB_NAME);
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql) {
        $result = $this->connection->query($sql);
        if ($result === false) {
            throw new Exception('SQL-Fehler: ' . $this->connection->error);
        }
        return $result;
    }
    
    public function prepare($sql) {
        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Prepare-Fehler: ' . $this->connection->error);
        }
        return $stmt;
    }
    
    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
    
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function beginTransaction() {
        $this->connection->begin_transaction();
    }
    
    public function commit() {
        $this->connection->commit();
    }
    
    public function rollback() {
        $this->connection->rollback();
    }
    
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    public function __destruct() {
        $this->close();
    }
    
    // Singleton-Methode (optional)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
