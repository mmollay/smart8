<?php
return [
    'mailjet' => [
        'api_key' => $_ENV['MAILJET_API_KEY'] ?? '',
        'api_secret' => $_ENV['MAILJET_API_SECRET'] ?? ''
    ],
    'default_sender' => [
        'email' => 'office@ssi.at',
        'name' => 'SSI Office'
    ],
    'smtp' => [
        'host' => $_ENV['SMTP_HOST'] ?? '',
        'port' => 587,
        'encryption' => 'tls',
        'username' => $_ENV['SMTP_USERNAME'] ?? '',
        'password' => $_ENV['SMTP_PASSWORD'] ?? ''
    ],
    'templates' => [
        'password_reset' => [
            'subject' => 'Passwort zurücksetzen - Smart System',
            'title' => 'Passwort zurücksetzen'
        ]
    ]
];