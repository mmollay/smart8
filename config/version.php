<?php
// config/version.php

return [
    'version' => '8.1.0',
    'releaseDate' => '2024-11-24',
    'changelog' => [
        '8.1.0' => [
            'date' => '2024-11-24',
            'changes' => [
                'Neues Login System mit verbesserter Sicherheit',
                'Password Reset Funktionalität überarbeitet',
                'Google OAuth Integration',
                'Verbesserte Session Verwaltung',
                'Neue Benutzereinstellungen'
            ]
        ],
        '8.0.1' => [
            'date' => '2024-11-01',
            'changes' => [
                'Bugfixes und Performance Optimierungen',
                'UI/UX Verbesserungen',
                'Verbesserte Fehlerbehandlung'
            ]
        ],
        '8.0.0' => [
            'date' => '2024-10-15',
            'changes' => [
                'Initiale Version von Smart 8',
                'Komplette Überarbeitung des Backends',
                'Neue moderne Benutzeroberfläche',
                'Verbesserte Sicherheitsfunktionen'
            ]
        ]
    ],
    'minimumRequirements' => [
        'php' => '8.1.0',
        'mysql' => '5.7.0',
        'browser' => [
            'Chrome' => '88',
            'Firefox' => '87',
            'Safari' => '14',
            'Edge' => '88'
        ]
    ]
];