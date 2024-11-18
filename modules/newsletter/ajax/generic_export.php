<?php
// ajax/generic_export.php

// Basis-Konfiguration und Handler laden 
require_once(__DIR__ . '/../n_config.php');
require_once(__DIR__ . '/../../../smartform2/ListExportHandler.php');

try {
    // Exporter mit Konfigurations-Pfad initialisieren und ausführen
    $exporter = new ListExportHandler(
        $db,
        __DIR__ . '/../config/export_config.php'
    );
    $exporter->export();

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]));
}