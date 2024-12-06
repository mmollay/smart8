<?php
include_once __DIR__ . '/../../../config.php';
$config = require(__DIR__ . '/../config/config.php');
$packageConfig = $config['packages'];

// Überprüfe Superuser-Berechtigung
if (!isset($_SESSION['superuser']) || $_SESSION['superuser'] != 1) {
    die(json_encode([
        'success' => false,
        'message' => 'Keine Berechtigung für diese Aktion'
    ]));
}

$response = ['success' => false, 'message' => ''];

try {
    // Parameter validieren
    if (!isset($_POST['user_id'])) {
        throw new Exception('User ID fehlt');
    }

    $userId = intval($_POST['user_id']);
    $packageType = trim($_POST['package_type'] ?? '');
    $newsletterActive = isset($_POST['newsletter_active']) && $_POST['newsletter_active'] == '1';

    // Validierung
    if ($userId === 0) {
        throw new Exception('Ungültige User ID');
    }

    if ($newsletterActive && !isset($packageConfig[$packageType])) {
        throw new Exception('Ungültiges Paket ausgewählt');
    }

    // Überprüfe ob User existiert und nicht gesperrt ist
    $stmt = $db->prepare("
        SELECT user_id 
        FROM user2company 
        WHERE user_id = ? AND locked = 0
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        throw new Exception('User nicht gefunden oder gesperrt');
    }

    // Starte Transaktion
    $db->begin_transaction();

    if (!$newsletterActive) {
        // User deaktivieren
        handleDeactivation($db, $userId);
        $response['message'] = 'Newsletter-Zugang erfolgreich deaktiviert';
    } else {
        // Neues Paket zuweisen
        handlePackageAssignment($db, $userId, $packageType, $packageConfig);
        $response['message'] = 'Paket erfolgreich zugewiesen';
    }

    // Erfolgreich committen
    $db->commit();
    $response['success'] = true;

} catch (Exception $e) {
    if ($db && $db->ping()) {
        $db->rollback();
    }
    $response['message'] = 'Fehler: ' . $e->getMessage();
    error_log("Fehler bei Package-Verwaltung: " . $e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}

// Sende JSON Response
header('Content-Type: application/json');
echo json_encode($response);

/**
 * Behandelt die Deaktivierung eines Users
 */
function handleDeactivation($db, $userId)
{
    // Deaktiviere das Modul
    $stmt = $db->prepare("
        UPDATE user_modules 
        SET status = 0 
        WHERE user_id = ? AND module_id = 6
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    // Beende aktives Paket
    $stmt = $db->prepare("
        UPDATE newsletter_user_packages 
        SET valid_until = CURRENT_TIMESTAMP 
        WHERE user_id = ? AND valid_until IS NULL
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    // Log-Eintrag für Deaktivierung
    createLogEntry($db, $userId, 'Newsletter-Zugang deaktiviert');
}

/**
 * Behandelt die Zuweisung eines neuen Pakets
 */
function handlePackageAssignment($db, $userId, $packageType, $packageConfig)
{
    // Altes Paket deaktivieren
    $stmt = $db->prepare("
        UPDATE newsletter_user_packages 
        SET valid_until = CURRENT_TIMESTAMP 
        WHERE user_id = ? AND valid_until IS NULL
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    // Neues Paket zuweisen
    $stmt = $db->prepare("
        INSERT INTO newsletter_user_packages 
        (user_id, package_type, emails_limit, emails_sent) 
        VALUES (?, ?, ?, 0)
    ");
    $stmt->bind_param(
        "isi",
        $userId,
        $packageType,
        $packageConfig[$packageType]['emails_per_month']
    );
    $stmt->execute();

    // Newsletter-Modul aktivieren
    $stmt = $db->prepare("
        INSERT INTO user_modules (user_id, module_id, assigned_by, status) 
        VALUES (?, 6, ?, 1) 
        ON DUPLICATE KEY UPDATE status = 1
    ");
    $stmt->bind_param("ii", $userId, $_SESSION['user_id']);
    $stmt->execute();

    // Log-Eintrag erstellen
    createLogEntry($db, $userId, "Newsletter-Paket zugewiesen: $packageType");
}

/**
 * Erstellt einen Log-Eintrag
 */
function createLogEntry($db, $userId, $message)
{
    $stmt = $db->prepare("
        INSERT INTO login_attempts 
        (username, success, ip_address, message) 
        SELECT 
            user_name,
            1,
            ?,
            ?
        FROM user2company 
        WHERE user_id = ?
    ");
    $stmt->bind_param("ssi", $_SERVER['REMOTE_ADDR'], $message, $userId);
    $stmt->execute();
}