<?php
session_start();
include(__DIR__ . '/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($db, trim($_POST['username']));
    $password = $_POST['password']; // Das vom Benutzer eingegebene Passwort (Klartext)

    $stmt = mysqli_prepare($db, "SELECT * FROM user2company WHERE user_name = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $stored_password = $user['password'];
        $password_verified = false;

        // Überprüfen, ob das gespeicherte Passwort ein bcrypt Hash ist
        if (strpos($stored_password, '$2y$') === 0) {
            // Es ist ein bcrypt Hash, verwende password_verify
            $password_verified = password_verify($password, $stored_password);
        } else {
            // Es könnte ein Klartext-Passwort oder ein anderer Hash-Typ sein
            $password_verified = ($password === $stored_password);
        }

        if ($password_verified) {
            // Passwort ist korrekt
            $_SESSION['client_id'] = $user['user_id'];

            // Wenn es kein bcrypt Hash war, jetzt einen erstellen und in der DB aktualisieren
            if (strpos($stored_password, '$2y$') !== 0) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = mysqli_prepare($db, "UPDATE user2company SET password = ? WHERE user_name = ?");
                mysqli_stmt_bind_param($update_stmt, "ss", $new_hash, $username);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            }
            echo "Erfolg";
        } else {
            // Passwort ist falsch
            echo "Fehler";
        }
    } else {
        // Kein Benutzer gefunden
        echo "Fehler";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($db);
    exit;
}
?>