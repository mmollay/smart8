<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT * FROM elements ORDER BY position");
    $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'elements' => $elements]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
