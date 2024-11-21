<?php
namespace Smart\Core;

class Database
{
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct($config)
    {

        if (!isset($config['database'])) {
            throw new \Exception("Datenbank-Name fehlt in der Konfiguration");
        }

        $this->config = $config;
        $this->connect();
    }

    private function connect()
    {
        try {
            if (
                !isset($this->config['host']) ||
                !isset($this->config['username']) ||
                !isset($this->config['password']) ||
                !isset($this->config['database'])
            ) {
                throw new \Exception("Unvollständige Datenbank-Konfiguration");
            }

            $this->connection = new \mysqli(
                $this->config['host'],
                $this->config['username'],
                $this->config['password'],
                $this->config['database']
            );

            if ($this->connection->connect_error) {
                throw new \Exception("Verbindungsfehler: " . $this->connection->connect_error);
            }

            $this->connection->set_charset('utf8mb4');

        } catch (\Exception $e) {
            error_log("Datenbankfehler: " . $e->getMessage());
            throw new \Exception("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
        }
    }

    public static function getInstance($config = null)
    {
        if (self::$instance === null) {
            if ($config === null) {
                throw new \Exception("Datenbank-Konfiguration fehlt");
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function prepare($query)
    {
        try {
            $stmt = $this->connection->prepare($query);
            if (!$stmt) {
                throw new \Exception("Prepare Statement fehlgeschlagen: " . $this->connection->error);
            }
            return $stmt;
        } catch (\Exception $e) {
            error_log("Prepare Fehler: " . $e->getMessage());
            throw new \Exception("Query Vorbereitung fehlgeschlagen");
        }
    }

    public function query($query)
    {
        try {
            $result = $this->connection->query($query);
            if (!$result) {
                throw new \Exception("Query fehlgeschlagen: " . $this->connection->error);
            }
            return $result;
        } catch (\Exception $e) {
            error_log("Query Fehler: " . $e->getMessage());
            throw new \Exception("Datenbankabfrage fehlgeschlagen");
        }
    }

    public function escape($string)
    {
        return $this->connection->real_escape_string($string);
    }

    public function getLastInsertId()
    {
        return $this->connection->insert_id;
    }

    public function beginTransaction()
    {
        $this->connection->begin_transaction();
    }

    public function commit()
    {
        $this->connection->commit();
    }

    public function rollback()
    {
        $this->connection->rollback();
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function __destruct()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    // Prevent cloning of the instance
    private function __clone()
    {
    }

    // Prevent unserializing of the instance
    public function __wakeup()
    {
    }

    // Debug method
    public function getDatabaseInfo()
    {
        return [
            'host' => $this->config['host'],
            'database' => $this->config['database'],
            'connected' => ($this->connection && !$this->connection->connect_error),
            'charset' => $this->connection->character_set_name(),
            'client_info' => $this->connection->client_info,
            'server_info' => $this->connection->server_info
        ];
    }
}