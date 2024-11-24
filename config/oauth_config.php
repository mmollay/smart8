<?
return [
    'google' => [
        'client_id' => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
        'redirect_uri' => ($_ENV['APP_URL'] ?? 'http://localhost/smart8') . '/auth/google-callback.php',
    ]
];