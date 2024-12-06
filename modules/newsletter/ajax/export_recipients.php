<?php
require_once(__DIR__ . '/../n_config.php');
$importConfig = require(__DIR__ . '/../config/import_export_config.php');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=empfaenger_export_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// CSV Header entsprechend der Konfiguration
$headers = array_values($importConfig['default_columns']);
fputcsv($output, $headers);

$where = ["1=1"];
$params = [];
$types = '';

$filters = isset($_GET['filters']) ? json_decode($_GET['filters'], true) : [];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

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

if (!empty($filters['group_id'])) {
    $where[] = "g.id = ?";
    $params[] = $filters['group_id'];
    $types .= 'i';
}

if (!empty($search)) {
    $where[] = "(r.email LIKE ? OR r.first_name LIKE ? OR r.last_name LIKE ? OR r.company LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'ssss';
}

$columnOrder = array_keys($importConfig['default_columns']);
$selectFields = implode(', ', array_map(function ($field) {
    return "r.$field";
}, $columnOrder));

$sql = "
    SELECT DISTINCT
        $selectFields
    FROM recipients r
    LEFT JOIN recipient_group rg ON r.id = rg.recipient_id
    LEFT JOIN groups g ON rg.group_id = g.id
    WHERE " . implode(" AND ", $where) . "
    GROUP BY r.id
    ORDER BY r.id DESC
";

$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $exportRow = [];
    foreach ($columnOrder as $field) {
        $exportRow[] = $row[$field] ?? '';
    }
    fputcsv($output, $exportRow);
}

fclose($output);
$db->close();