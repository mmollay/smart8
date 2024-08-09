<?php
session_start();
include ('config.php'); // Stellen Sie sicher, dass Ihre config.php Datei die mysqli Verbindung herstellt

// Beispiel für eine config.php Datei
// $db = mysqli_connect('localhost', 'mein_benutzer', 'mein_passwort', 'meine_db');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($db, trim($_POST['username']));
    $password = mysqli_real_escape_string($db, trim($_POST['password']));

    $stmt = mysqli_prepare($db, "SELECT * FROM clients WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['client_id'] = $user['client_id'];
        echo "Erfolg";
    } else {
        echo "Fehler";
    }
    mysqli_stmt_close($stmt);
    mysqli_close($db);
    exit;
}
?>