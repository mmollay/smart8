<?php
require_once(__DIR__ . '/../users_config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

try {
    $update_id = $_POST['update_id'] ?? null;
    $assigned_by = $_SESSION['client_id'] ?? 1;

    // Validierung
    if (empty($_POST['user_name']) || !filter_var($_POST['user_name'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Bitte geben Sie eine gültige E-Mail-Adresse ein');
    }

    $data = [
        'company_id' => 1,
        'user_name' => trim($_POST['user_name']),
        'firstname' => trim($_POST['firstname']),
        'secondname' => trim($_POST['secondname']),
        'zip' => $_POST['zip'] ?? '',
        'city' => trim($_POST['city'] ?? ''),
        'street' => trim($_POST['street'] ?? ''),
        'gender' => $_POST['gender'] ?? 'n',
        'country' => trim($_POST['country'] ?? 'at'),
        'number_of_smartpage' => 1,
        'verified' => $_POST['verified'] ? 1 : 0,
        'right_id' => 1000,
        'user_checked' => '1',
        'superuser' => isset($_POST['superuser']) ? 1 : 0,
        'locked' => 0,
        'parent_id' => 0
    ];

    // Start transaction
    $db->autocommit(FALSE);

    if ($update_id) {
        // Update existing user
        $sql = "UPDATE user2company SET 
               user_name = ?,
               firstname = ?,
               secondname = ?,
               zip = ?,
               city = ?,
               street = ?,
               gender = ?,
               country = ?,
               number_of_smartpage = ?,
               verified = ?,
               right_id = ?,
               user_checked = ?,
               superuser = ?,
               locked = ?,
               parent_id = ?
               WHERE user_id = ?";

        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            'sssissssiiiiiiii',
            $data['user_name'],
            $data['firstname'],
            $data['secondname'],
            $data['zip'],
            $data['city'],
            $data['street'],
            $data['gender'],
            $data['country'],
            $data['number_of_smartpage'],
            $data['verified'],
            $data['right_id'],
            $data['user_checked'],
            $data['superuser'],
            $data['locked'],
            $data['parent_id'],
            $update_id
        );
        $stmt->execute();

        $user_id = $update_id;
    } else {
        // New user
        if (empty($_POST['password'])) {
            throw new Exception('Passwort ist erforderlich');
        }

        $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $data['verify_key'] = bin2hex(random_bytes(16));

        $sql = "INSERT INTO user2company (
           company_id, user_name, firstname, secondname, password,
           verified, right_id, user_checked, zip, city, street,
           gender, country, number_of_smartpage, verify_key,
           superuser, locked, parent_id
       ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            'issssiisissssiiiis',
            $data['company_id'],
            $data['user_name'],
            $data['firstname'],
            $data['secondname'],
            $data['password'],
            $data['verified'],
            $data['right_id'],
            $data['user_checked'],
            $data['zip'],
            $data['city'],
            $data['street'],
            $data['gender'],
            $data['country'],
            $data['number_of_smartpage'],
            $data['verify_key'],
            $data['superuser'],
            $data['locked'],
            $data['parent_id']
        );
        $stmt->execute();

        $user_id = $db->insert_id;
    }

    // Handle module assignments
    // First, deactivate all existing module assignments
    $stmt = $db->prepare("UPDATE user_modules SET status = 0 WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    // Get and process the modules from POST
    $selectedModules = [];
    if (isset($_POST['modules']) && is_array($_POST['modules']) && !empty($_POST['modules'])) {

        // Split the first array element by comma since it contains "8,5" format
        $selectedModules = explode(',', $_POST['modules'][0]);

        // Filter out empty values and ensure integers
        $selectedModules = array_filter($selectedModules, function ($value) {
            return !empty($value) && is_numeric($value);
        });

        // Convert to integers
        $selectedModules = array_map('intval', $selectedModules);
    }

    // Process module assignments if we have any selected modules
    if (!empty($selectedModules)) {
        // Prepare statements for module and permission assignments
        $moduleStmt = $db->prepare("
            INSERT INTO user_modules (user_id, module_id, assigned_by, status)
            VALUES (?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE status = 1, assigned_at = CURRENT_TIMESTAMP
        ");

        $permissionStmt = $db->prepare("
            INSERT INTO user_module_permissions (user_id, module_id, permission_key, granted_by)
            VALUES (?, ?, 'view', ?), (?, ?, 'edit', ?), (?, ?, 'delete', ?), (?, ?, 'manage_modules', ?)
            ON DUPLICATE KEY UPDATE granted_at = CURRENT_TIMESTAMP
        ");

        foreach ($selectedModules as $module_id) {
            // Assign module
            $moduleStmt->bind_param('iii', $user_id, $module_id, $assigned_by);
            $moduleStmt->execute();

            // Assign permissions including manage_modules
            $permissionStmt->bind_param(
                'iiiiiiiiiiii',
                $user_id,
                $module_id,
                $assigned_by,
                $user_id,
                $module_id,
                $assigned_by,
                $user_id,
                $module_id,
                $assigned_by,
                $user_id,
                $module_id,
                $assigned_by
            );
            $permissionStmt->execute();
        }
    }

    // Add log entry
    $action = $update_id ? "updated" : "created";
    logUserAction($user_id, "User {$action}", $data['user_name']);

    // Commit transaction
    $db->commit();
    $db->autocommit(TRUE);

    echo json_encode([
        'success' => true,
        'message' => 'Benutzer erfolgreich ' . ($update_id ? 'aktualisiert' : 'erstellt')
    ]);

} catch (Exception $e) {
    // Rollback on error
    $db->rollback();
    $db->autocommit(TRUE);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}