<?php
return [
    'database' => [
        'host' => $_ENV['NEWSLETTER_DB_HOST'] ?? 'localhost',
        'port' => $_ENV['NEWSLETTER_DB_PORT'] ?? 3306,
        'username' => $_ENV['NEWSLETTER_DB_USERNAME'] ?? 'root',
        'password' => $_ENV['NEWSLETTER_DB_PASSWORD'] ?? '',
        'dbname' => $_ENV['NEWSLETTER_DB_NAME'] ?? 'ssi_newsletter'
    ],

    'packages' => [
        'free' => [
            'emails_per_month' => 1000,
            'max_senders' => 1,
            'max_groups' => 2
        ],
        'standard' => [
            'emails_per_month' => 10000,
            'max_senders' => 3,
            'max_groups' => 0  // 0 = unbegrenzt
        ],
        'professional' => [
            'emails_per_month' => 50000,
            'max_senders' => 10,
            'max_groups' => 0
        ]
    ],

    'mail' => [
        'mailjet' => [
            'api_key' => $_ENV['MAILJET_API_KEY'] ?? '',
            'api_secret' => $_ENV['MAILJET_API_SECRET'] ?? ''
        ]
    ],

    'eventTypes' => [
        'send' => '<i class="paper plane blue icon"></i> Versendet',
        'delivered' => '<i class="check circle green icon"></i> Zugestellt',
        'open' => '<i class="eye blue icon"></i> Geöffnet',
        'click' => '<i class="mouse pointer blue icon"></i> Angeklickt',
        'bounce' => '<i class="exclamation circle red icon"></i> Zurückgewiesen',
        'failed' => '<i class="times circle red icon"></i> Fehlgeschlagen',
        'blocked' => '<i class="ban red icon"></i> Blockiert',
        'spam' => '<i class="warning sign orange icon"></i> Als Spam markiert'
    ],
    // Ergänze in config.php im return Array:
    'import_export' => [
        'column_order' => [
            'email',
            'first_name',
            'last_name',
            'gender',
            'title',
            'company',
            'comment'
        ],
        'required_fields' => [
            'email',
        ]
    ],
];