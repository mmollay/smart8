<?php
require_once(__DIR__ . '/../n_config.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers für CSV-Download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=empfaenger_export_' . date('Y-m-d') . '.csv');

// CSV Output vorbereiten
$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM für Excel

// CSV Header
fputcsv($output, [
    'ID',
    'Vorname',
    'Nachname',
    'Firma',
    'E-Mail',
    'Status',
    'Gruppen',
    'Kommentar'
]);

// WHERE Bedingungen und Parameter
$where = ["1=1"]; // Basisbedingung
$params = [];
$types = '';

// Filter aus URL verarbeiten
$filters = isset($_GET['filters']) ? json_decode($_GET['filters'], true) : [];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Debug Log
error_log("Received filters: " . print_r($filters, true));
error_log("Search term: " . $search);

// Status Filter
if (!empty($filters['status'])) {
    switch ($filters['status']) {
        case 'active':
            $where[] = "(r.unsubscribed = 0 AND (r.bounce_status = 'none' OR r.bounce_status IS NULL))";
            break;
        case 'unsubscribed':
            $where[] = "r.unsubscribed = 1";
            break;
        case 'bounced_hard':
            $where[] = "r.bounce_status = 'hard'";
            break;
        case 'bounced_soft':
            $where[] = "r.bounce_status = 'soft'";
            break;
    }
}

// Gruppen Filter
if (!empty($filters['group_id'])) {
    $where[] = "g.id = ?";
    $params[] = $filters['group_id'];
    $types .= 'i';
}

// Suchfilter
if (!empty($search)) {
    $where[] = "(r.email LIKE ? OR r.first_name LIKE ? OR r.last_name LIKE ? OR r.company LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'ssss';
}

// Query zusammenbauen
$sql = "
    SELECT DISTINCT
        r.id,
        r.first_name,
        r.last_name,
        r.company,
        r.email,
        CASE 
            WHEN r.unsubscribed = 1 THEN 'Abgemeldet'
            WHEN r.bounce_status = 'hard' THEN 'Hard Bounce'
            WHEN r.bounce_status = 'soft' THEN 'Soft Bounce'
            ELSE 'Aktiv'
        END as status,
        GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') as groups,
        r.comment
    FROM recipients r
    LEFT JOIN recipient_group rg ON r.id = rg.recipient_id
    LEFT JOIN groups g ON rg.group_id = g.id
    WHERE " . implode(" AND ", $where) . "
    GROUP BY r.id
    ORDER BY r.id DESC
";

// Debug Log
error_log("Export SQL: " . $sql);
error_log("Parameters: " . print_r($params, true));

// Query ausführen
$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    error_log("Export SQL Error: " . $db->error);
    die("Datenbankfehler: " . $db->error);
}

$count = 0;
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['first_name'],
        $row['last_name'],
        $row['company'],
        $row['email'],
        $row['status'],
        $row['groups'],
        $row['comment']
    ]);
    $count++;
}

error_log("Exported {$count} records");

fclose($output);
$db->close();