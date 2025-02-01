<?php
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../bitget/bitget_api.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID nicht angegeben');
    }

    $id = intval($_GET['id']);

    // API Credentials holen
    $stmt = $db->prepare("
        SELECT * FROM api_credentials 
        WHERE id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $cred = $stmt->get_result()->fetch_assoc();

    if (!$cred) {
        throw new Exception('API Credentials nicht gefunden');
    }

    // BitGet API testen
    $bitget = new BitGetAPI($cred['api_key'], $cred['api_secret'], $cred['api_passphrase']);
    $balance = $bitget->getAccountBalance();

    if (!isset($balance['data'][0])) {
        throw new Exception('Ungültige API Response: ' . json_encode($balance));
    }

    $accountData = $balance['data'][0];
    $availableBalance = $accountData['available'] ?? '0';
    $equity = $accountData['equity'] ?? '0';

    // Last Used aktualisieren
    $stmt = $db->prepare("
        UPDATE api_credentials 
        SET last_used = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => sprintf(
            'API Test erfolgreich! Balance: %s USDT (Equity: %s USDT)',
            $availableBalance,
            $equity
        )
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
