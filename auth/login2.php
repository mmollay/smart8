<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Services/AuthService.php';

use Smart\Core\Database;
use Smart\Services\AuthService;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Datenbankverbindung erstellen
        $dbConfig = require __DIR__ . '/../config/database.php';
        $database = Database::getInstance($dbConfig);

        // AuthService initialisieren
        $auth = new AuthService($database);

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'true';

        // Login durchführen
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
            'message' => 'Ein Fehler ist aufgetreten',
            'debug' => $e->getMessage()
        ]);
    }
    exit;
}

// Wenn keine POST-Anfrage, zum Login-Formular umleiten
header('Location: login.php');
exit;