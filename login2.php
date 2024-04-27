<?php
session_start();
include ('config.php'); // Stellen Sie sicher, dass Ihre config.php Datei die mysqli Verbindung herstellt

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($db, trim($_POST['username']));
    $password = mysqli_real_escape_string($db, trim($_POST['password']));

    $stmt = mysqli_prepare($db, "SELECT * FROM user2company WHERE user_name = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // Prüfen, ob das Passwort gehasht ist
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            // Passwort muss neu gehasht werden
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = mysqli_prepare($db, "UPDATE user2company SET password = ? WHERE user_name = ?");
            mysqli_stmt_bind_param($update_stmt, "ss", $hash, $username);
            if (mysqli_stmt_execute($update_stmt)) {
                echo "Passwort wurde erfolgreich neu gehasht und aktualisiert.";
            } else {
                echo "Fehler beim Aktualisieren des Passworts.";
            }
            mysqli_stmt_close($update_stmt);
        } else if (password_verify($password, $user['password'])) {
            // Das Passwort ist korrekt und bereits gehasht
            $_SESSION['client_id'] = $user['user_id'];
            echo "Erfolg";
        } else {
            // Das Passwort ist falsch
            echo "Fehler";
        }
    } else {
        // Kein Benutzer gefunden
        echo "Benutzer nicht gefunden.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($db);
    exit;
}
?>