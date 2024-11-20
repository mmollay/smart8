<?php
// ajax/export.php

require_once '../n_config.php';
require_once(__DIR__ . '/../../../smartform2/DataExporter.php');

try {
    // Überprüfe Parameter
    if (empty($_GET['type'])) {
        throw new Exception('Kein Export-Typ angegeben');
    }

    $type = $_GET['type'];
    $format = $_GET['format'] ?? 'csv';

    // Führe Export durch
    $exporter = new DataExporter($db);
    $exporter->export($type, $format);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
