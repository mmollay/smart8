<?
function checkRememberMeToken($db)
{
    if (!isset($_SESSION['client_id']) && isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];

        // Suche nach gültigem Token
        $stmt = $db->prepare("
            SELECT u.user_id, us.token 
            FROM user_sessions us 
            JOIN user2company u ON us.user_id = u.user_id 
            WHERE us.expires_at > NOW()
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if (password_verify($token, $row['token'])) {
                // Token ist gültig - Session wiederherstellen
                $_SESSION['client_id'] = $row['user_id'];

                // Optional: Token erneuern
                $newToken = bin2hex(random_bytes(32));
                $newTokenHash = password_hash($newToken, PASSWORD_DEFAULT);

                $updateStmt = $db->prepare("
                    UPDATE user_sessions 
                    SET token = ?, 
                        expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY) 
                    WHERE user_id = ?
                ");
                $updateStmt->bind_param("si", $newTokenHash, $row['user_id']);
                $updateStmt->execute();

                // Neues Cookie setzen
                setcookie(
                    'remember_token',
                    $newToken,
                    [
                        'expires' => time() + (30 * 24 * 60 * 60),
                        'path' => '/',
                        'domain' => '',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]
                );

                return true;
            }
        }

        // Token nicht gefunden oder ungültig - Cookie löschen
        setcookie('remember_token', '', time() - 3600, '/');
    }

    return isset($_SESSION['client_id']);
}

function refreshSession($userId, $db)
{
    // Aktuelle Zeit
    $currentTime = time();

    // Prüfen ob es bereits ein Remember-Me Cookie gibt
    if (!isset($_COOKIE['remember_token'])) {
        // Generiere einen neuen Token
        $token = bin2hex(random_bytes(32));

        // Hash des Tokens für die Datenbank
        $tokenHash = password_hash($token, PASSWORD_DEFAULT);

        // Cookie für 30 Tage setzen
        setcookie(
            'remember_token',
            $token,
            [
                'expires' => $currentTime + (30 * 24 * 60 * 60),
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );

        // Berechne das Ablaufdatum im korrekten MySQL-Format
        $expiresAt = date('Y-m-d H:i:s', $currentTime + (30 * 24 * 60 * 60));

        // Token in der Datenbank speichern
        $stmt = $db->prepare("
            INSERT INTO user_sessions (user_id, token, expires_at)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE token = ?, expires_at = ?
        ");

        $stmt->bind_param('issss', $userId, $tokenHash, $expiresAt, $tokenHash, $expiresAt);
        $stmt->execute();
    }

    // Session-Timeout zurücksetzen
    $_SESSION['last_activity'] = $currentTime;
}

function checkSession($db)
{
    $timeout = 28800; // 8 Stunden in Sekunden

    // Prüfen ob Session abgelaufen ist
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        // Session ist abgelaufen
        session_destroy();
        return false;
    }

    // Wenn keine Session existiert, aber Remember-Me Cookie vorhanden ist
    if (!isset($_SESSION['client_id']) && isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];

        // Token in der Datenbank suchen
        $stmt = $db->prepare("
            SELECT u.user_id, u.user_name, s.token
            FROM user_sessions s
            JOIN user2company u ON s.user_id = u.user_id
            WHERE s.expires_at > NOW()
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if (password_verify($token, $row['token'])) {
                // Token ist gültig - neue Session starten
                $_SESSION['client_id'] = $row['user_id'];
                refreshSession($row['user_id'], $db);
                return true;
            }
        }

        // Token nicht gefunden oder ungültig - Cookie löschen
        setcookie('remember_token', '', time() - 3600, '/');
        return false;
    }

    // Session ist aktiv - Timer zurücksetzen
    if (isset($_SESSION['client_id'])) {
        refreshSession($_SESSION['client_id'], $db);
        return true;
    }

    return false;
}
// Ihre bestehenden Funktionen...
function getUserDetails($userId, $db)
{
    if (!$userId)
        return null;

    $stmt = $db->prepare("SELECT * FROM user2company WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}



// Read Value from Table
function mysql_singleoutput($sql, $indexColumn = false)
{
    $query = $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
    $array = mysqli_fetch_array($query);
    if ($indexColumn)
        return $array[$indexColumn];
    else
        return $array[0];
}


// Wandelt deutsches Format in englisches um
function nr_format2english($wert1)
{
    if ($wert1)
        return preg_replace("/,/", '.', $wert1);
}


