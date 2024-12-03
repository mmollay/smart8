<?php
require_once(__DIR__ . '/../users_config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Ungültige Anfrage']));
}

$isInTransaction = false;

try {
    // Sicherstellen dass ein Benutzer eingeloggt ist
    if (!isset($_SESSION['client_id'])) {
        throw new Exception('Nicht autorisiert');
    }

    // Prüfen ob der eingeloggte Benutzer Superuser ist
    $stmt = $db->prepare("SELECT superuser FROM user2company WHERE user_id = ?");
    $stmt->bind_param('i', $_SESSION['client_id']);
    $stmt->execute();
    $isSuperuser = $stmt->get_result()->fetch_assoc()['superuser'] ?? 0;

    if (!$isSuperuser) {
        $stmt = $db->prepare("
            SELECT 1 FROM user_module_permissions 
            WHERE user_id = ? 
            AND module_id = (SELECT module_id FROM modules WHERE identifier = 'users')
            AND permission_key = 'manage_permissions'
        ");
        $stmt->bind_param('i', $_SESSION['client_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception('Keine Berechtigung zum Verwalten von Berechtigungen');
        }
    }

    $update_id = $_POST['update_id'] ?? null;
    $user_id = $_POST['user_id'] ?? null;
    $module_id = $_POST['module_id'] ?? null;
    $permissions = $_POST['permissions'] ?? [];
    $valid_until = !empty($_POST['valid_until']) ? $_POST['valid_until'] : null;
    $status = isset($_POST['status']) ? 1 : 0;

    // Validierung
    if (!$user_id || !$module_id || empty($permissions)) {
        throw new Exception('Bitte füllen Sie alle erforderlichen Felder aus');
    }

    // Überprüfen ob der Benutzer existiert
    $stmt = $db->prepare("SELECT user_id, superuser FROM user2company WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Benutzer nicht gefunden');
    }
    $targetUserSuperuser = $result->fetch_assoc()['superuser'] ?? 0;

    if ($targetUserSuperuser && !$isSuperuser) {
        throw new Exception('Keine Berechtigung zum Ändern von Superuser-Berechtigungen');
    }

    // Überprüfen ob das Modul existiert
    $stmt = $db->prepare("SELECT module_id FROM modules WHERE module_id = ?");
    $stmt->bind_param('i', $module_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Modul nicht gefunden');
    }

    // Start Transaction
    $db->autocommit(FALSE);
    $isInTransaction = true;

    // Wenn es ein Update ist, zuerst alle bestehenden Berechtigungen löschen
    if ($update_id) {
        $stmt = $db->prepare("DELETE FROM user_module_permissions WHERE id = ?");
        $stmt->bind_param('i', $update_id);
        $stmt->execute();
    } else {
        // Bei neuer Zuweisung prüfen ob das Modul bereits zugewiesen ist
        $stmt = $db->prepare("
            SELECT id FROM user_modules 
            WHERE user_id = ? AND module_id = ?
        ");
        $stmt->bind_param('ii', $user_id, $module_id);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            $stmt = $db->prepare("
                INSERT INTO user_modules (user_id, module_id, assigned_by, status, valid_until) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $assignedBy = $_SESSION['client_id'];
            $stmt->bind_param('iiiis', $user_id, $module_id, $assignedBy, $status, $valid_until);
            $stmt->execute();
        }
    }

    // Neue Berechtigungen einfügen
    $stmt = $db->prepare("
        INSERT INTO user_module_permissions 
        (user_id, module_id, permission_key, granted_by, granted_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");

    $grantedBy = $_SESSION['client_id'];
    foreach ($permissions as $permission) {
        if (!isValidPermission($permission)) {
            throw new Exception("Ungültige Berechtigung: $permission");
        }
        $stmt->bind_param('iisi', $user_id, $module_id, $permission, $grantedBy);
        $stmt->execute();
    }

    // Logging
    $userStmt = $db->prepare("SELECT user_name FROM user2company WHERE user_id = ?");
    $userStmt->bind_param('i', $user_id);
    $userStmt->execute();
    $userName = $userStmt->get_result()->fetch_assoc()['user_name'];

    $moduleStmt = $db->prepare("SELECT name FROM modules WHERE module_id = ?");
    $moduleStmt->bind_param('i', $module_id);
    $moduleStmt->execute();
    $moduleName = $moduleStmt->get_result()->fetch_assoc()['name'];

    $logMessage = $update_id
        ? "Berechtigungen aktualisiert für Benutzer $userName im Modul $moduleName"
        : "Neue Berechtigungen zugewiesen für Benutzer $userName im Modul $moduleName";

    logUserAction($grantedBy, $logMessage);

    // Commit Transaction
    $db->commit();
    $db->autocommit(TRUE);
    $isInTransaction = false;

    echo json_encode([
        'success' => true,
        'message' => 'Berechtigungen erfolgreich ' . ($update_id ? 'aktualisiert' : 'gespeichert'),
        'reload' => true
    ]);

} catch (Exception $e) {
    if ($isInTransaction) {
        $db->rollback();
        $db->autocommit(TRUE);
    }
    error_log("Fehler beim Speichern der Berechtigungen: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function isValidPermission($permission)
{
    $validPermissions = [
        'view',
        'create',
        'edit',
        'delete',
        'export',
        'import',
        'print',
        'manage_users',
        'manage_modules',
        'manage_permissions',
        'manage_settings'
    ];
    return in_array($permission, $validPermissions, true);
}