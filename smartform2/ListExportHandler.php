<?php
// ListExportHandler.php

class ListExportHandler
{
    private $db;
    private $format;
    private $listId;
    private $fields;
    private $filters;
    private $search;
    private $sort;
    private $sortDir;
    private $config;

    public function __construct($db, $configPath = null)
    {
        $this->db = $db;
        $this->format = $_GET['format'] ?? 'csv';
        $this->listId = $_GET['listId'] ?? '';
        $this->fields = !empty($_GET['fields']) ? json_decode($_GET['fields'], true) : [];
        $this->filters = !empty($_GET['filters']) ? json_decode($_GET['filters'], true) : [];
        $this->search = $_GET['search'] ?? '';
        $this->sort = $_GET['sort'] ?? 'id';
        $this->sortDir = $_GET['sortDir'] ?? 'ASC';

        // Lade die Konfiguration
        $configPath = $configPath ?? __DIR__ . '/../config/export_config.php';
        $this->config = require $configPath;
    }

    public function export()
    {
        // Konfiguration für die aktuelle Liste laden
        $exportConfig = $this->config[$this->listId] ?? null;
        if (!$exportConfig) {
            die("Ungültige Listen-ID oder keine Konfiguration gefunden");
        }

        // Wenn spezifische Felder angegeben wurden, Konfiguration anpassen
        if (!empty($this->fields)) {
            $this->adjustConfigForFields($exportConfig);
        }

        // Header für den Download setzen
        $this->setExportHeaders($exportConfig['filename']);

        // Daten abrufen und exportieren
        $data = $this->fetchData($exportConfig);
        $this->exportData($data, $exportConfig['headers']);
    }

    private function adjustConfigForFields(&$exportConfig)
    {
        // Nur die angeforderten Header behalten
        $exportConfig['headers'] = array_intersect_key(
            $exportConfig['headers'],
            array_flip($this->fields)
        );

        // SELECT-Klausel mit den spezifischen Feldern erstellen
        $selectFields = array_map(function ($field) use ($exportConfig) {
            return $exportConfig['fieldMappings'][$field] ?? $field;
        }, $this->fields);

        $selectClause = implode(', ', $selectFields);

        // Query anpassen
        $exportConfig['query'] = preg_replace(
            '/SELECT\s+.+?\sFROM/is',
            "SELECT $selectClause FROM",
            $exportConfig['query']
        );
    }

    private function setExportHeaders($baseFilename)
    {
        $timestamp = date('Y-m-d_His');
        $extension = $this->format === 'xlsx' ? 'xlsx' : 'csv';
        $filename = "{$baseFilename}_{$timestamp}.{$extension}";

        header('Content-Type: ' . ($this->format === 'csv'
            ? 'text/csv; charset=utf-8'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    }

    private function exportData($data, $headers)
    {
        switch ($this->format) {
            case 'csv':
                $this->exportCSV($data, $headers);
                break;
            case 'xlsx':
                $this->exportXLSX($data, $headers);
                break;
            default:
                die("Nicht unterstütztes Export-Format");
        }
    }

    private function fetchData($config)
    {
        $query = $config['query'];
        $whereConditions = [];
        $params = [];
        $types = '';

        if (!empty($this->search) && !empty($config['searchColumns'])) {
            $this->addSearchConditions($whereConditions, $params, $types, $config['searchColumns']);
        }

        $this->addFilterConditions($whereConditions, $params, $types);

        // WHERE und ORDER Klauseln einfügen
        $query = $this->buildFinalQuery($query, $whereConditions);

        // Query ausführen
        return $this->executeQuery($query, $params, $types);
    }

    private function addSearchConditions(&$whereConditions, &$params, &$types, $searchColumns)
    {
        $searchConditions = [];
        foreach ($searchColumns as $column) {
            $searchConditions[] = "$column LIKE ?";
            $params[] = "%{$this->search}%";
            $types .= 's';
        }
        $whereConditions[] = '(' . implode(' OR ', $searchConditions) . ')';
    }

    private function addFilterConditions(&$whereConditions, &$params, &$types)
    {
        foreach ($this->filters as $key => $value) {
            if ($value !== '') {
                $whereConditions[] = "$key = ?";
                $params[] = $value;
                $types .= 's';
            }
        }
    }

    private function buildFinalQuery($query, $whereConditions)
    {
        $whereClause = !empty($whereConditions)
            ? 'WHERE ' . implode(' AND ', $whereConditions)
            : '';
        $orderClause = "ORDER BY {$this->sort} {$this->sortDir}";

        return str_replace(
            ['{WHERE}', '{ORDER}'],
            [$whereClause, $orderClause],
            $query
        );
    }

    private function executeQuery($query, $params, $types)
    {
        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function exportCSV($data, $headers)
    {
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM für Excel

        fputcsv($output, array_values($headers));

        foreach ($data as $row) {
            $exportRow = array_map(function ($key) use ($row) {
                return $row[$key] ?? '';
            }, array_keys($headers));

            fputcsv($output, $exportRow);
        }

        fclose($output);
    }

    private function exportXLSX($data, $headers)
    {
        die("XLSX-Export noch nicht implementiert");
    }
}