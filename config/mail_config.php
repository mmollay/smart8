<?
return [
    'mailjet' => [
        'api_key' => '452e5eca1f98da426a9a3542d1726c96',
        'api_secret' => '55b277cd54eaa3f1d8188fdc76e06535'
    ],
    'default_sender' => [
        'email' => 'office@ssi.at',
        'name' => 'SSI Office'
    ],
    'smtp' => [
        'host' => '', // Falls später SMTP benötigt wird
        'port' => 587,
        'encryption' => 'tls',
        'username' => '',
        'password' => ''
    ],
    'templates' => [
        'password_reset' => [
            'subject' => 'Passwort zurücksetzen - Smart System',
            'title' => 'Passwort zurücksetzen'
        ]
    ]
];