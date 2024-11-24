<?php
require_once __DIR__ . '/../src/bootstrap.php';

class LogoutHandler
{
    private $db;
    private $userId;

    public function __construct($db)
    {
        $this->db = $db;
        $this->userId = $_SESSION['user_id'] ?? $_SESSION['client_id'] ?? null;
    }

    public function logout(): array
    {
        try {
            // Backup der User-ID für Logging
            $userId = $this->userId;

            // Remember-Me Token aus der Datenbank entfernen
            $this->clearRememberMeToken();

            // Alle Session-Cookies löschen
            $this->clearSessionCookies();

            // Session zerstören
            $this->destroySession();

            // Erfolgreich ausgeloggt
            $this->logLogout($userId, true);

            return [
                'success' => true,
                'message' => 'Erfolgreich abgemeldet'
            ];

        } catch (\Exception $e) {
            error_log("Logout error for user {$this->userId}: " . $e->getMessage());
            $this->logLogout($this->userId, false, $e->getMessage());

            return [
                'success' => false,
                'message' => 'Fehler bei der Abmeldung'
            ];
        }
    }

    private function clearRememberMeToken(): void
    {
        if ($this->userId) {
            // Remember-Me Token aus der Datenbank entfernen
            $stmt = $this->db->prepare("
                UPDATE users 
                SET remember_token = NULL, 
                    remember_token_expires_at = NULL 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $this->userId);
            $stmt->execute();
        }

        // Remember-Me Cookie löschen
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
    }

    private function clearSessionCookies(): void
    {
        // Alle Session-Cookies löschen
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
    }

    private function destroySession(): void
    {
        // Alle Session-Variablen löschen
        $_SESSION = array();

        // Session-Cookie löschen wenn vorhanden
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params["path"],
                'domain' => $params["domain"],
                'secure' => $params["secure"],
                'httponly' => $params["httponly"],
                'samesite' => 'Strict'
            ]);
        }

        // Session zerstören
        session_destroy();
    }

    private function logLogout($userId, bool $success, string $errorMessage = null): void
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
                ) VALUES (
                    (SELECT email FROM users WHERE id = ?),
                    ?,
                    ?,
                    ?,
                    ?,
                    NOW()
                )
            ");

            $successInt = $success ? 1 : 0;
            $ip = $_SERVER['REMOTE_ADDR'];
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $message = $success ? 'Logout successful' : "Logout failed: $errorMessage";

            $stmt->bind_param("iisss", $userId, $successInt, $ip, $userAgent, $message);
            $stmt->execute();

        } catch (\Exception $e) {
            error_log("Failed to log logout attempt: " . $e->getMessage());
        }
    }
}

// Logout durchführen
$logoutHandler = new LogoutHandler($db);
$result = $logoutHandler->logout();

// AJAX Request behandeln
if (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Normaler Request: Weiterleitung mit Statusmeldung
$redirectUrl = 'login.php';
if (!$result['success']) {
    $redirectUrl .= '?error=logout_failed&message=' . urlencode($result['message']);
} else {
    $redirectUrl .= '?message=' . urlencode($result['message']);
}

header('Location: ' . $redirectUrl);
exit;