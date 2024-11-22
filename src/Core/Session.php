<?php
namespace Smart\Core;

class Session
{
    private static $instance = null;
    private $db;
    private $lifetime;

    private function __construct(Database $db)
    {
        $this->db = $db;
        $this->lifetime = $_ENV['SESSION_LIFETIME'] ?? 120; // 120 Minuten Standard

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Setze initial last_activity
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
    }

    public static function getInstance(Database $db): self
    {
        if (self::$instance === null) {
            self::$instance = new self($db);
        }
        return self::$instance;
    }

    public function isExpired(): bool
    {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }

        $inactive = time() - $_SESSION['last_activity'];
        if ($inactive >= ($this->lifetime * 60)) {
            // Session ist abgelaufen
            $this->clear();
            return true;
        }

        // Aktualisiere last_activity
        $_SESSION['last_activity'] = time();
        return false;
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function clear(): void
    {
        // Session-Variablen löschen
        session_unset();

        // Session zerstören
        session_destroy();

        // Session-Cookie löschen
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Neue Session starten
        session_start();

        // Setze initial last_activity für neue Session
        $_SESSION['last_activity'] = time();
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function regenerate(): bool
    {
        return session_regenerate_id(true);
    }
}