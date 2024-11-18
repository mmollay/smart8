<?php
require_once __DIR__ . '/ListGenerator.php';
require_once __DIR__ . '/config.php';

try {
    // Parameter aus der URL holen
    $format = $_GET['format'] ?? 'csv';
    $fields = !empty($_GET['fields']) ? explode(',', $_GET['fields']) : null;
    $filters = json_decode($_GET['filters'] ?? '{}', true);
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? 'id';
    $sortDir = $_GET['sortDir'] ?? 'ASC';
    $listId = $_GET['listId'] ?? '';
    $contentId = $_GET['contentId'] ?? '';

    // ListGenerator Konfiguration
    $listConfig = [
        'listId' => $listId,
        'contentId' => $contentId,
        'itemsPerPage' => PHP_INT_MAX, // Alle Datensätze für Export
        'sortColumn' => $sort,
        'sortDirection' => $sortDir,
        'search' => $search
    ];

    $listGenerator = new ListGenerator($listConfig);

    // Setze die gleiche Datenbankverbindung wie in der Liste
    if (isset($db)) {
        // Hole die Original-Query aus der Session oder Config
        $originalQuery = $_SESSION['list_query_' . $listId] ?? '';
        if ($originalQuery) {
            $listGenerator->setDatabase($db, $originalQuery);
        }
    }

    // Setze die Filter
    if (!empty($filters)) {
        foreach ($filters as $key => $value) {
            $_GET['filters'][$key] = $value;
        }
    }

    // Führe den Export durch
    $filename = 'export_' . $listId . '_' . date('Y-m-d_His');
    $listGenerator->generateExport($format, $fields, $filename);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}