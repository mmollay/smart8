<?php
namespace Smart\Services;

use Smart\Core\Session;

class AuthService
{
    private $db;
    private $session;
    private $loginAttempts = 5; // Maximale Anzahl von Login-Versuchen
    private $lockoutTime = 900; // Sperrzeit in Sekunden (15 Minuten)

    public function __construct($db)
    {
        $this->db = $db;
        $this->session = Session::getInstance($db);
    }

    /**
     * Benutzer-Login durchführen
     */
    public function login($username, $password, $remember = false)
    {
        
        try {
            // Prüfe auf zu viele Login-Versuche
            if ($this->isAccountLocked($username)) {
                $this->logLoginAttempt($username, false, 'Account temporär gesperrt');
                return [
                    'success' => false,
                    'message' => 'Zu viele Login-Versuche. Bitte warten Sie 15 Minuten.'
                ];
            }

            $stmt = $this->db->prepare("SELECT * FROM user2company WHERE user_name = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user) {
                $this->logLoginAttempt($username, false, 'Benutzer nicht gefunden');
                return [
                    'success' => false,
                    'message' => 'Ungültige Anmeldedaten'
                ];
            }

            // Prüfe, ob Account gesperrt ist
            if (!empty($user['locked'])) {
                $this->logLoginAttempt($username, false, 'Account gesperrt');
                return [
                    'success' => false,
                    'message' => 'Dieser Account ist gesperrt. Bitte kontaktieren Sie den Administrator.'
                ];
            }

            // Überprüfe Passwort
            if ($this->verifyPassword($user, $password)) {
                // Erfolgreicher Login
                $_SESSION['client_id'] = $user['user_id'];
                $_SESSION['last_activity'] = time();

                // Remember Me Token erstellen wenn gewünscht
                if ($remember) {,
                    $this->createRememberMeToken($user['user_id']);
                }

                // Erfolgreichen Login loggen
                $this->logLoginAttempt($username, true, 'Login erfolgreich');

                // Login-Versuche zurücksetzen
                $this->resetLoginAttempts($username);

                return [
                    'success' => true,
                    'message' => 'Login erfolgreich'
                ];
            }

            // Fehlgeschlagener Login
            $this->logLoginAttempt($username, false, 'Falsches Passwort');
            $this->incrementLoginAttempts($username);

            return [
                'success' => false,
                'message' => 'Ungültige Anmeldedaten'
            ];

        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ein Fehler ist aufgetreten'
            ];
        }
    }

    /**
     * Benutzer ausloggen
     */
    public function logout()
    {
        try {
            // Remember-Me Token aus DB und Cookie entfernen
            if (isset($_SESSION['client_id'])) {
                $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE user_id = ?");
                $stmt->bind_param("i", $_SESSION['client_id']);
                $stmt->execute();
            }

            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            }

            session_destroy();
            return true;
        } catch (\Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Passwort überprüfen mit Legacy-Support
     */
    private function verifyPassword($user, $password)
    {
        $stored_password = $user['password'];

        // Prüfe, ob es ein bcrypt Hash ist
        if (strpos($stored_password, '$2y$') === 0) {
            return password_verify($password, $stored_password);
        }

        // Legacy Passwort
        $isValid = ($password === $stored_password);
        if ($isValid) {
            // Update auf bcrypt Hash
            $this->updatePasswordHash($user['user_name'], $password);
        }
        return $isValid;
    }

    /**
     * Altes Passwort auf neuen Hash updaten
     */
    private function updatePasswordHash($username, $password)
    {
        try {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE user2company SET password = ? WHERE user_name = ?");
            $stmt->bind_param("ss", $newHash, $username);
            $stmt->execute();
        } catch (\Exception $e) {
            error_log("Password hash update error: " . $e->getMessage());
        }
    }

    /**
     * Remember Me Token erstellen
     */
    private function createRememberMeToken($userId)
    {
        try {
            $token = bin2hex(random_bytes(32));
            $tokenHash = password_hash($token, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("
                INSERT INTO user_sessions (user_id, token, expires_at) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
                ON DUPLICATE KEY UPDATE token = ?, expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->bind_param("iss", $userId, $tokenHash, $tokenHash);
            $stmt->execute();

            setcookie(
                'remember_token',
                $token,
                [
                    'expires' => time() + (30 * 24 * 60 * 60),
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
            return true;
        } catch (\Exception $e) {
            error_log("Create remember token error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prüfen ob Account gesperrt ist
     */
    private function isAccountLocked($username)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as attempts 
                FROM login_attempts 
                WHERE username = ? 
                AND success = 0 
                AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
            ");
            $stmt->bind_param("si", $username, $this->lockoutTime);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return $row['attempts'] >= $this->loginAttempts;
        } catch (\Exception $e) {
            error_log("Check account lock error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Login-Versuche zurücksetzen
     */
    private function resetLoginAttempts($username)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM login_attempts 
                WHERE username = ? 
                AND success = 0
            ");
            $stmt->bind_param("s", $username);
            $stmt->execute();
        } catch (\Exception $e) {
            error_log("Reset login attempts error: " . $e->getMessage());
        }
    }

    /**
     * Login-Versuche erhöhen
     */
    private function incrementLoginAttempts($username)
    {
        $this->logLoginAttempt($username, false, 'Fehlgeschlagener Login-Versuch');
    }

    /**
     * Login-Versuch protokollieren
     */
    private function logLoginAttempt($username, $success, $message = '')
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO login_attempts (
                    username, 
                    success, 
                    ip_address, 
                    user_agent,
                    message,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, NOW())
            ");

            $ip = $_SERVER['REMOTE_ADDR'];
            $userAgent = $_SERVER['HTTP_USER_AGENT'];

            $stmt->bind_param(
                "sisss",
                $username,
                $success ? '1' : '0',
                $ip,
                $userAgent,
                $message
            );

            $stmt->execute();
        } catch (\Exception $e) {
            error_log("Log login attempt error: " . $e->getMessage());
        }
    }

    /**
     * Session-Status prüfen
     */
    public function checkSession()
    {
        return isset($_SESSION['client_id']);
    }

    /**
     * Aktuellen Benutzer abrufen
     */
    public function getCurrentUser()
    {
        if (!$this->checkSession()) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user2company 
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $_SESSION['client_id']);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (\Exception $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }
}