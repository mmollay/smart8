<?php
// ajax/import_recipients.php
include(__DIR__ . '/../../n_config.php');

header('Content-Type: application/json');

if (!isset($_POST['import_data'])) {
    die(json_encode([
        'success' => false,
        'message' => 'Keine Daten zum Importieren gefunden.'
    ]));
}

// Initialize counters
$stats = [
    'total' => 0,
    'imported' => 0,
    'updated' => 0,
    'skipped' => 0,
    'errors' => 0
];

$skip_duplicates = isset($_POST['skip_duplicates']) && $_POST['skip_duplicates'] === 'on';
$update_existing = isset($_POST['update_existing']) && $_POST['update_existing'] === 'on';
$group_id = isset($_POST['group_id']) && !empty($_POST['group_id']) ? (int) $_POST['group_id'] : null;

// Prepare statements
$check_stmt = $db->prepare("SELECT id FROM recipients WHERE email = ? AND user_id = ?");
$insert_stmt = $db->prepare("INSERT INTO recipients (email, first_name, last_name, company, gender, user_id) VALUES (?, ?, ?, ?, ?, ?)");
$update_stmt = $db->prepare("UPDATE recipients SET first_name = ?, last_name = ?, company = ?, gender = ? WHERE email = ? AND user_id = ?");
$group_stmt = null;
if ($group_id) {
    $group_stmt = $db->prepare("INSERT IGNORE INTO recipient_group (recipient_id, group_id) VALUES (?, ?)");
}

// Process input data
$lines = explode("\n", trim($_POST['import_data']));
foreach ($lines as $line) {
    $stats['total']++;

    // Skip empty lines
    if (empty(trim($line))) {
        continue;
    }

    // Split line by tabs
    $data = explode("\t", trim($line));

    // Validate email (minimum requirement)
    $email = trim($data[0]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stats['errors']++;
        continue;
    }

    // Extract other fields with defaults
    $first_name = isset($data[1]) ? trim($data[1]) : '';
    $last_name = isset($data[2]) ? trim($data[2]) : '';
    $company = isset($data[3]) ? trim($data[3]) : '';
    $gender = isset($data[4]) ? trim($data[4]) : '';

    // Normalize gender
    if (strtolower($gender) === 'weiblich' || strtolower($gender) === 'w') {
        $gender = 'female';
    } elseif (strtolower($gender) === 'männlich' || strtolower($gender) === 'm') {
        $gender = 'male';
    } else {
        $gender = 'other';
    }

    // Check if email exists
    $check_stmt->bind_param("si", $email, $userId);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $exists = $result->num_rows > 0;

    if ($exists) {
        if ($skip_duplicates) {
            $stats['skipped']++;
            continue;
        } elseif ($update_existing) {
            // Update existing recipient
            $update_stmt->bind_param("sssss", $first_name, $last_name, $company, $gender, $email, $userId);
            if ($update_stmt->execute()) {
                $stats['updated']++;
                $recipient_id = $result->fetch_assoc()['id'];
            } else {
                $stats['errors']++;
                continue;
            }
        } else {
            $stats['skipped']++;
            continue;
        }
    } else {
        // Insert new recipient
        $insert_stmt->bind_param("sssssi", $email, $first_name, $last_name, $company, $gender, $userId);
        if ($insert_stmt->execute()) {
            $stats['imported']++;
            $recipient_id = $db->insert_id;
        } else {
            $stats['errors']++;
            continue;
        }
    }

    // Add to group if specified
    if ($group_id && $recipient_id) {
        $group_stmt->bind_param("ii", $recipient_id, $group_id);
        $group_stmt->execute();
    }
}

// Close prepared statements
$check_stmt->close();
$insert_stmt->close();
$update_stmt->close();
if ($group_stmt) {
    $group_stmt->close();
}

// Prepare response message
$message = sprintf(
    "Import abgeschlossen:\n- %d neue Empfänger importiert\n- %d aktualisiert\n- %d übersprungen\n- %d Fehler",
    $stats['imported'],
    $stats['updated'],
    $stats['skipped'],
    $stats['errors']
);

echo json_encode([
    'success' => true,
    'message' => $message,
    'stats' => $stats
]);
?>