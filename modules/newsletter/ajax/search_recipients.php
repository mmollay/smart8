<?php
require_once(__DIR__ . '/../n_config.php');

// Sicherheitscheck
if (!isset($_SESSION['user_id'])) {
    die(json_encode([
        'success' => false,
        'message' => 'Keine Berechtigung'
    ]));
}

try {
    // Suchbegriff aus GET-Parameter
    $query = trim($_GET['q'] ?? '');

    if (empty($query)) {
        die(json_encode(['items' => []]));
    }

    // Sichere Wildcard-Suche vorbereiten
    $searchTerm = '%' . $query . '%';

    // SQL für die Suche
    $sql = "
        SELECT DISTINCT
            r.id,
            r.email,
            CONCAT(
                COALESCE(r.first_name, ''), 
                ' ', 
                COALESCE(r.last_name, ''),
                CASE 
                    WHEN r.company IS NOT NULL AND r.company != '' 
                    THEN CONCAT(' (', r.company, ')')
                    ELSE ''
                END
            ) as name,
            CASE 
                WHEN b.id IS NOT NULL THEN 1
                ELSE 0
            END as is_blacklisted,
            r.unsubscribed,
            r.bounce_status
        FROM 
            recipients r
            LEFT JOIN blacklist b ON r.email = b.email AND r.user_id = b.user_id
        WHERE 
            r.user_id = ? 
            AND (
                r.email LIKE ? 
                OR r.first_name LIKE ? 
                OR r.last_name LIKE ?
                OR r.company LIKE ?
            )
        ORDER BY 
            r.email ASC
        LIMIT 10
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("issss", $userId, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        // Status-Informationen vorbereiten
        $status = [];
        if ($row['is_blacklisted']) {
            $status[] = 'Auf Blacklist';
        }
        if ($row['unsubscribed']) {
            $status[] = 'Abgemeldet';
        }
        if ($row['bounce_status'] === 'hard') {
            $status[] = 'Hard Bounce';
        }
        if ($row['bounce_status'] === 'soft') {
            $status[] = 'Soft Bounce';
        }

        // Name aufbereiten
        $name = trim($row['name']);
        if (!empty($status)) {
            $name .= ' [' . implode(', ', $status) . ']';
        }

        $items[] = [
            'id' => $row['id'],
            'email' => $row['email'],
            'name' => $name,
            'title' => $row['email'],
            'description' => $name,
            'is_blacklisted' => $row['is_blacklisted'],
            'status' => $status
        ];
    }

    echo json_encode([
        'success' => true,
        'items' => $items
    ]);

} catch (Exception $e) {
    error_log("Fehler in search_recipients.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Fehler bei der Suche'
    ]);
}

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}
?>