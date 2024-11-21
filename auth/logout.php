<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . '/../src/bootstrap.php');

try {
    // Prüfen ob Auth Service verfügbar ist
    if (!isset($auth)) {
        throw new Exception('Auth Service nicht verfügbar');
    }

    // Session ID vor Logout speichern für Logging
    $userId = $_SESSION['client_id'] ?? $_SESSION['user_id'] ?? 'unknown';

    // Logout durchführen
    $result = $auth->logout();

    if ($result) {
        error_log("Successful logout for user ID: " . $userId);
        $message = 'success';
    } else {
        error_log("Logout failed for user ID: " . $userId);
        $message = 'error';
    }

} catch (Exception $e) {
    error_log("Logout exception: " . $e->getMessage());
    $message = 'error';
}

// AJAX oder normale Anfrage behandeln
if (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
) {

    header('Content-Type: application/json');
    echo json_encode([
        'status' => $message,
        'message' => $message === 'success' ? 'Erfolgreich abgemeldet' : 'Logout fehlgeschlagen'
    ]);
    exit;
}

// Bei normalem Request: Weiterleitung mit Statusmeldung
$redirectUrl = 'login.php';
if ($message === 'error') {
    $redirectUrl .= '?error=logout&message=' . urlencode('Logout fehlgeschlagen');
} else {
    $redirectUrl .= '?message=' . urlencode('Erfolgreich abgemeldet');
}

header('Location: ' . $redirectUrl);
exit;