<?php
include_once __DIR__ . '/../../config.php';

// Permission definitions
$MODULE_PERMISSIONS = [
    'view' => [
        'key' => 'view',
        'name' => 'Ansehen',
        'description' => 'Grundlegende Ansichtsrechte'
    ],
    'edit' => [
        'key' => 'edit',
        'name' => 'Bearbeiten',
        'description' => 'Bearbeiten von Einträgen'
    ],
    'delete' => [
        'key' => 'delete',
        'name' => 'Löschen',
        'description' => 'Löschen von Einträgen'
    ],
    'manage_modules' => [
        'key' => 'manage_modules',
        'name' => 'Module verwalten',
        'description' => 'Verwaltung von Modulzuweisungen'
    ]
];

// User management functions
function getUserById($userId)
{
    global $db;
    $stmt = $db->prepare("
        SELECT * FROM user2company 
        WHERE user_id = ? LIMIT 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserModules($userId)
{
    global $db;
    $stmt = $db->prepare("
        SELECT m.* 
        FROM user_modules um
        JOIN modules m ON m.module_id = um.module_id
        WHERE um.user_id = ? AND um.status = 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function checkUserPermission($userId, $moduleId, $permission)
{
    global $db;

    // Check superuser status
    $stmt = $db->prepare("SELECT superuser FROM user2company WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['superuser'] == 1)
        return true;

    // Check specific permission
    $stmt = $db->prepare("
        SELECT 1 FROM user_module_permissions 
        WHERE user_id = ? AND module_id = ? AND permission_key = ?
    ");
    $stmt->bind_param('iis', $userId, $moduleId, $permission);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Security functions
function sanitizeInput($input)
{
    global $db;
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return $db->real_escape_string(trim($input));
}

function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Response handlers
function jsonResponse($success, $message = '', $data = [])
{
    return json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
}

// Logging function
function logUserAction($userId, $action, $details = '')
{
    global $db;
    $stmt = $db->prepare("
        INSERT INTO login_attempts (username, success, ip_address, message)
        VALUES ((SELECT user_name FROM user2company WHERE user_id = ?), 1, ?, ?)
    ");
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $message = $action . ($details ? ': ' . $details : '');
    $stmt->bind_param('iss', $userId, $ip, $message);
    $stmt->execute();
}

// Session handling
// session_start();
// if (!isset($_SESSION['user_id'])) {
//     header('Location: ../../auth/login.php');
//     exit;
// }

// $currentUser = getUserById($_SESSION['user_id']);
// if (!$currentUser) {
//     header('Location: ../../auth/logout.php');
//     exit;
// }