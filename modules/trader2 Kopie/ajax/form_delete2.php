<?php
require_once(__DIR__ . '/../t_config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Ungültige Anfragemethode']));
}

$delete_id = intval($_POST['delete_id']);
$list_id = $_POST['list_id'];

if (!$delete_id || !$list_id) {
    die(json_encode(['success' => false, 'message' => 'Ungültige Parameter']));
}

$db->begin_transaction();

try {
    // Prüfe Abhängigkeiten
    $dependencyCheck = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM trades WHERE client_id = ?) as trade_count,
            (SELECT COUNT(*) FROM positions WHERE client_id = ?) as position_count,
            (SELECT COUNT(*) FROM balances WHERE client_id = ?) as balance_count
    ");
    $dependencyCheck->bind_param("iii", $delete_id, $delete_id, $delete_id);
    $dependencyCheck->execute();
    $result = $dependencyCheck->get_result()->fetch_assoc();

    if ($result['trade_count'] > 0 || $result['position_count'] > 0 || $result['balance_count'] > 0) {
        throw new Exception("Benutzer kann nicht gelöscht werden, da noch Handelsdaten existieren");
    }

    // Lösche verknüpfte Daten
    $tables = [
        'api_access_log' => "DELETE al FROM api_access_log al 
                            JOIN api_credentials ac ON al.api_credential_id = ac.id 
                            WHERE ac.user_id = ?",
        'api_credentials' => "DELETE FROM api_credentials WHERE user_id = ?",
        'bank_accounts' => "DELETE FROM bank_accounts WHERE user_id = ?",
        'users' => "DELETE FROM users WHERE id = ? LIMIT 1"
    ];

    foreach ($tables as $table => $query) {
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        if ($table === 'users' && $stmt->affected_rows === 0) {
            throw new Exception("Benutzer nicht gefunden");
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Benutzer erfolgreich gelöscht']);
} catch (Exception $e) {
    $db->rollback();
    error_log("Fehler beim Löschen des Benutzers: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>