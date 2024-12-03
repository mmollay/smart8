<?php
// auth/google-callback.php
require_once __DIR__ . '/../src/bootstrap.php';

$config = require_once __DIR__ . '/../config/oauth_config.php';
$googleAuth = new Smart\Services\GoogleAuthService($db, $config['google']);

if (isset($_GET['code'])) {
    $result = $googleAuth->handleCallback($_GET['code']);

    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }
}

// Redirect back to login with error
header('Location: login.php?error=google_auth_failed');
exit;