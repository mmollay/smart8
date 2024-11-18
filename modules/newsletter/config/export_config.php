<?

return [
    'recipients' => [
        'filename' => 'empfaenger_export',
        'headers' => [
            'id' => 'ID',
            'first_name' => 'Vorname',
            'last_name' => 'Nachname',
            'company' => 'Firma',
            'email' => 'E-Mail',
            'status' => 'Status',
            'groups' => 'Gruppen',
            'comment' => 'Kommentar'
        ],
        'query' => "
            SELECT 
                r.id,
                r.first_name,
                r.last_name,
                r.company,
                r.email,
                CASE 
                    WHEN r.unsubscribed = 1 THEN 'Abgemeldet'
                    WHEN r.bounce_status = 'hard' THEN 'Hard Bounce'
                    WHEN r.bounce_status = 'soft' THEN 'Soft Bounce'
                    ELSE 'Aktiv'
                END as status,
                GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') as groups,
                r.comment
            FROM recipients r
            LEFT JOIN recipient_group rg ON r.id = rg.recipient_id
            LEFT JOIN groups g ON rg.group_id = g.id
            {WHERE}
            GROUP BY r.id
            {ORDER}
        ",
        'searchColumns' => ['r.email', 'r.first_name', 'r.last_name', 'r.company'],
        'fieldMappings' => [
            'id' => 'r.id',
            'first_name' => 'r.first_name',
            'last_name' => 'r.last_name',
            'email' => 'r.email',
            'company' => 'r.company',
            'comment' => 'r.comment',
            'status' => "CASE 
                WHEN r.unsubscribed = 1 THEN 'Abgemeldet'
                WHEN r.bounce_status = 'hard' THEN 'Hard Bounce'
                WHEN r.bounce_status = 'soft' THEN 'Soft Bounce'
                ELSE 'Aktiv'
            END",
            'groups' => "GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ')"
        ]
    ],
    'users' => [
        'filename' => 'benutzer_export',
        'headers' => [
            'id' => 'ID',
            'first_name' => 'Vorname',
            'last_name' => 'Nachname',
            'email' => 'E-Mail',
            'role' => 'Rolle',
            'status' => 'Status',
            'department_name' => 'Abteilung'
        ],
        'query' => "
            SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                u.role,
                u.status,
                d.name as department_name
            FROM users u
            LEFT JOIN departments d ON u.department_id = d.id
            {WHERE}
            {ORDER}
        ",
        'searchColumns' => ['u.first_name', 'u.last_name', 'u.email', 'u.role'],
        'fieldMappings' => [
            'id' => 'u.id',
            'first_name' => 'u.first_name',
            'last_name' => 'u.last_name',
            'email' => 'u.email',
            'role' => 'u.role',
            'status' => 'u.status',
            'department_name' => 'd.name'
        ]
    ]
];