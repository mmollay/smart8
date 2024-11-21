<?php
namespace Smart\Services;

use Smart\Core\Session;
use Smart\Core\Database;

class AuthService
{
    private Database $db;
    private Session $session;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->session = Session::getInstance($db);
    }

    public function login(string $username, string $password, bool $remember = false): array
    {
        try {
            // Benutzer in der Datenbank suchen
            $stmt = $this->db->prepare("SELECT * FROM user2company WHERE user_name = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Benutzer nicht gefunden'
                ];
            }

            // Passwort überprüfen
            if ($this->verifyPassword($user, $password)) {
                // Session setzen
                $this->session->set('client_id', $user['user_id']);

                // Remember Me Token erstellen wenn gewünscht
                if ($remember) {
                    $this->createRememberMeToken($user['user_id']);
                }

                // Login-Versuch loggen
                $this->logLoginAttempt($username, true);

                return [
                    'success' => true,
                    'message' => 'Login erfolgreich'
                ];
            }

            // Fehlgeschlagenen Login loggen
            $this->logLoginAttempt($username, false);

            return [
                'success' => false,
                'message' => 'Ungültiges Passwort'
            ];

        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ein Fehler ist aufgetreten',
                'debug' => $e->getMessage()
            ];
        }
    }

    private function verifyPassword(array $user, string $password): bool
    {
        // Prüfen ob es ein bcrypt Hash ist
        if (strpos($user['password'], '$2y$') === 0) {
            return password_verify($password, $user['password']);
        }

        // Legacy-Passwort Überprüfung
        $isValid = ($password === $user['password']);
        if ($isValid) {
            $this->updatePasswordHash($user['user_name'], $password);
        }
        return $isValid;
    }

    private function updatePasswordHash(string $username, string $password): void
    {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE user2company SET password = ? WHERE user_name = ?");
            $stmt->bind_param("ss", $hash, $username);
            $stmt->execute();
        } catch (\Exception $e) {
            error_log("Password hash update failed: " . $e->getMessage());
        }
    }

    private function logLoginAttempt(string $username, bool $success): void
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO login_attempts (username, success, ip_address, created_at) 
                VALUES (?, ?, ?, NOW())"
            );
            $successInt = $success ? 1 : 0;
            $ip = $_SERVER['REMOTE_ADDR'];
            $stmt->bind_param("sis", $username, $successInt, $ip);
            $stmt->execute();
        } catch (\Exception $e) {
            error_log("Login attempt logging failed: " . $e->getMessage());
        }
    }

    private function createRememberMeToken(int $userId): void
    {
        try {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

            $stmt = $this->db->prepare(
                "INSERT INTO auth_tokens (user_id, token, expires) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("iss", $userId, $token, $expires);
            $stmt->execute();

            setcookie(
                'remember_token',
                $token,
                strtotime('+30 days'),
                '/',
                '',
                true, // Nur über HTTPS
                true  // Nur HTTP
            );
        } catch (\Exception $e) {
            error_log("Remember me token creation failed: " . $e->getMessage());
        }
    }
    public function logout(): bool
    {
        try {
            // Session-ID für Logging speichern
            $userId = $_SESSION['client_id'] ?? 'unknown';

            // Session-Variablen löschen
            foreach ($_SESSION as $key => $value) {
                unset($_SESSION[$key]);
            }

            // Session zerstören
            session_destroy();

            // Neue Session starten für Nachrichten
            session_start();

            // Remember-Me Token entfernen falls vorhanden
            if (isset($_COOKIE['remember_token'])) {
                $token = $_COOKIE['remember_token'];
                setcookie('remember_token', '', time() - 3600, '/');

                try {
                    $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE token = ?");
                    $stmt->bind_param("s", $token);
                    $stmt->execute();
                } catch (\Exception $e) {
                    error_log("Failed to delete remember token: " . $e->getMessage());
                }
            }

            error_log("Successful logout for user ID: " . $userId);
            return true;

        } catch (\Exception $e) {
            error_log("Logout failed: " . $e->getMessage());
            return false;
        }
    }


}