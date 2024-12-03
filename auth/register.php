<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../services/MailService.php';

use Smart\Services\MailService;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        throw new Exception('Bitte füllen Sie alle Felder aus');
    }

    // Prüfen ob E-Mail bereits existiert
    $stmt = $GLOBALS['mysqli']->prepare("SELECT user_id FROM user2company WHERE user_name = ?");
    if (!$stmt) {
        throw new Exception('Datenbankfehler: ' . $GLOBALS['mysqli']->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Diese E-Mail-Adresse ist bereits registriert');
    }
    $stmt->close();

    // Passwort-Validierung
    if (strlen($password) < 8) {
        throw new Exception('Das Passwort muss mindestens 8 Zeichen lang sein');
    }
    if (!preg_match('/[A-Z]/', $password)) {
        throw new Exception('Das Passwort muss mindestens einen Großbuchstaben enthalten');
    }
    if (!preg_match('/[a-z]/', $password)) {
        throw new Exception('Das Passwort muss mindestens einen Kleinbuchstaben enthalten');
    }
    if (!preg_match('/[0-9]/', $password)) {
        throw new Exception('Das Passwort muss mindestens eine Zahl enthalten');
    }

    // Verify Key generieren
    $verifyKey = md5(uniqid(rand(), true));

    // Benutzer erstellen
    $stmt = $GLOBALS['mysqli']->prepare("
    INSERT INTO user2company (
        company_id,
        user_name,
        password,
        verified,
        right_id,
        user_checked,
        verify_key,
        number_of_smartpage,
        reg_date,
        firstname,
        secondname,
        street,
        zip,
        city,
        gender,
        country,
        superuser,    /* Hinzugefügt */
        locked,       /* Hinzugefügt */
        parent_id
    ) VALUES (
        1,
        ?,
        ?,
        0,
        1000,
        '1',
        ?,
        1,
        NOW(),
        '',
        '',
        '',
        0,
        '',
        'n',
        'at',
        0,           /* Standardwert für superuser */
        0 ,           /* Standardwert für locked */
        0
    )
");


    if (!$stmt) {
        throw new Exception('Datenbankfehler beim Vorbereiten des Inserts: ' . $GLOBALS['mysqli']->error);
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("sss", $email, $hashedPassword, $verifyKey);

    if (!$stmt->execute()) {
        throw new Exception('Fehler beim Erstellen des Accounts: ' . $stmt->error);
    }

    $userId = $stmt->insert_id;
    $stmt->close();

    // Verification E-Mail senden
    $verifyLink = "https://" . $_SERVER['HTTP_HOST'] . "/auth/verify.php?key=" . $verifyKey;

    try {
        $mailService = MailService::getInstance();
        $mailResult = $mailService->sendMail(
            $email,
            'E-Mail Verifizierung - Smart System',
            "
            <h2>E-Mail Verifizierung</h2>
            <p>Vielen Dank für Ihre Registrierung bei Smart System!</p>
            <p>Bitte bestätigen Sie Ihre E-Mail-Adresse durch Klick auf folgenden Link:</p>
            <p><a href='{$verifyLink}'>{$verifyLink}</a></p>
            <p>Der Link ist 24 Stunden gültig.</p>
            <p>Mit freundlichen Grüßen<br>Ihr Smart-Team</p>
            "
        );

        if (!$mailResult) {
            error_log("Warning: Failed to send verification email to: $email");
        }
    } catch (Exception $e) {
        error_log("Mail Error: " . $e->getMessage());
    }

    echo json_encode([
        'success' => true,
        'message' => 'Registrierung erfolgreich! Bitte bestätigen Sie Ihre E-Mail-Adresse.',
        'redirect' => 'login.php?message=' . urlencode('Bitte bestätigen Sie Ihre E-Mail-Adresse')
    ]);

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'post' => $_POST,
            'error' => $e->getMessage()
        ]
    ]);
}