<?php
// config/export_config.php

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

    'groups' => [
        'filename' => 'gruppen_export',
        'headers' => [
            'group_id' => 'ID',
            'group_name' => 'Gruppenname',
            'recipients_count' => 'Anzahl Empfänger',
            'created_at' => 'Erstellt am'
        ],
        'query' => "
            SELECT 
                g.id as group_id,
                CONCAT(g.color, ' ', g.name) as group_name,
                COUNT(DISTINCT r.id) as recipients_count,
                DATE_FORMAT(g.created_at, '%d.%m.%Y %H:%i') as created_at
            FROM 
                groups g
                LEFT JOIN recipient_group rg ON g.id = rg.group_id
                LEFT JOIN recipients r ON rg.recipient_id = r.id
            {WHERE}
            GROUP BY 
                g.id, 
                g.name,
                g.color, 
                g.created_at
            {ORDER}
        ",
        'searchColumns' => ['g.name'],
        'fieldMappings' => [
            'group_id' => 'g.id',
            'group_name' => "CONCAT(g.color, ' ', g.name)",
            'recipients_count' => 'COUNT(DISTINCT r.id)',
            'created_at' => "DATE_FORMAT(g.created_at, '%d.%m.%Y %H:%i')"
        ]
    ],

    'newsletters' => [
        'filename' => 'newsletter_export',
        'headers' => [
            'content_id' => 'ID',
            'subject' => 'Betreff',
            'sender_name' => 'Absender Name',
            'sender_email' => 'Absender E-Mail',
            'send_date' => 'Versanddatum',
            'total_recipients' => 'Gesamt Empfänger',
            'delivered_count' => 'Zugestellt',
            'opened_count' => 'Geöffnet',
            'clicked_count' => 'Geklickt',
            'failed_count' => 'Fehlgeschlagen',
            'unsub_count' => 'Abgemeldet',
            'groups' => 'Gruppen'
        ],
        'query' => "
            SELECT DISTINCT
                ec.id as content_id,
                ec.subject,
                CONCAT(s.first_name, ' ', s.last_name) as sender_name,
                s.email as sender_email,
                DATE_FORMAT(MAX(ej.sent_at), '%d.%m.%Y %H:%i') as send_date,
                COUNT(DISTINCT ej.recipient_id) as total_recipients,
                SUM(CASE WHEN ej.status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
                SUM(CASE WHEN ej.status = 'open' THEN 1 ELSE 0 END) as opened_count,
                SUM(CASE WHEN ej.status = 'click' THEN 1 ELSE 0 END) as clicked_count,
                SUM(CASE WHEN ej.status IN ('failed', 'bounce', 'blocked', 'spam') THEN 1 ELSE 0 END) as failed_count,
                SUM(CASE WHEN ej.status = 'unsub' THEN 1 ELSE 0 END) as unsub_count,
                GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') as groups
            FROM 
                email_contents ec
                LEFT JOIN senders s ON ec.sender_id = s.id
                LEFT JOIN email_jobs ej ON ec.id = ej.content_id
                LEFT JOIN email_content_groups ecg ON ec.id = ecg.email_content_id
                LEFT JOIN groups g ON ecg.group_id = g.id
            {WHERE}
            GROUP BY 
                ec.id,
                ec.subject,
                sender_name,
                sender_email
            {ORDER}
        ",
        'searchColumns' => ['ec.subject', 's.first_name', 's.last_name', 's.email', 'g.name'],
        'fieldMappings' => [
            'content_id' => 'ec.id',
            'subject' => 'ec.subject',
            'sender_name' => "CONCAT(s.first_name, ' ', s.last_name)",
            'sender_email' => 's.email',
            'send_date' => "DATE_FORMAT(MAX(ej.sent_at), '%d.%m.%Y %H:%i')",
            'total_recipients' => 'COUNT(DISTINCT ej.recipient_id)',
            'delivered_count' => "SUM(CASE WHEN ej.status = 'delivered' THEN 1 ELSE 0 END)",
            'opened_count' => "SUM(CASE WHEN ej.status = 'open' THEN 1 ELSE 0 END)",
            'clicked_count' => "SUM(CASE WHEN ej.status = 'click' THEN 1 ELSE 0 END)",
            'failed_count' => "SUM(CASE WHEN ej.status IN ('failed', 'bounce', 'blocked', 'spam') THEN 1 ELSE 0 END)",
            'unsub_count' => "SUM(CASE WHEN ej.status = 'unsub' THEN 1 ELSE 0 END)",
            'groups' => "GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ')"
        ]
    ]
];