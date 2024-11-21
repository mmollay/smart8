<?php
require_once __DIR__ . '/../src/bootstrap.php';

// Nur für Development
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'true';

        if (!isset($auth)) {
            throw new Exception('Auth service not available');
        }

        $result = $auth->login($username, $password, $remember);

        // Header für JSON-Response setzen
        header('Content-Type: application/json');

        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Login erfolgreich',
                'redirect' => '../modules/main/index.php'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $result['message'] ?? 'Ungültige Anmeldedaten'
            ]);
        }

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Ein Fehler ist aufgetreten'
        ]);
    }
    exit;
}

// Wenn keine POST-Anfrage, zum Login-Formular umleiten
header('Location: login.php');
exit;