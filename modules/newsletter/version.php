<?php
return [
    'version' => '1.2.5',
    'changelog' => [
        '1.2.5' => [
            'date' => '2025-12-13',
            'changes' => [
                'Neu' => [
                    'Follow-Up E-Mails durch klicken auf die jeweiligen Labels',
                    'Newsletter-Liste komplett überarbeitet (version2)',
                ]
            ]
        ],
        '1.2.4' => [
            'date' => '2024-12-11',
            'changes' => [
                'Neu' => [
                    'Senden an mehrere Testuser möglich',
                    'Startseite mit Statistik eingführt',
                    'Blacklist eingeführt',
                    'E-Mail-Adressen können auf die Blacklist gesetzt werden',
                    'E-Mail-Adressen auf der Blacklist erhalten keine Newsletter mehr',
                ],
                'Verbessert' => [
                    'Eingabemaske für Newsletter verbessert',
                ],
                'Behoben' => [
                    'User kann mehrerer Gruppen zugewiesen werden',
                    'Importschnittstelle korrigiert',
                    'Absender von E-Mails korrigiert',
                ]
            ]
        ],

        '1.1.3' => [
            'date' => '2025-12-05',
            'changes' => [
                'Behoben' => [
                    'Importschnitte erweitert',
                    'Newsletter Versand verbessert'
                ]
            ]
        ],
        '1.1.0' => [
            'date' => '2024-12-04',
            'changes' => [
                'Neu' => [
                    'Anzeige von abgemeldeten Empfängern in der Gruppenübersicht',
                    'Verbesserte Versandverarbeitung verhindert E-Mails an abgemeldete Empfänger',
                    'Changelog-System zur Verfolgung von Änderungen'
                ],
                'Verbessert' => [
                    'Optimierte Batch-Verarbeitung beim Newsletter-Versand',
                    'Erweiterte Statistiken in der Gruppenansicht'
                ],
                'Behoben' => [
                    'Fehlerhafte Zählung von Empfängern in Gruppen korrigiert',
                    'Verbesserte Fehlerbehandlung beim E-Mail-Versand'
                ]
            ]
        ],
        '1.0.0' => [
            'date' => '2024-11-01',
            'changes' => [
                'Neu' => [
                    'Initiale Version des Newsletter-Systems',
                    'Grundlegende Newsletter-Verwaltung',
                    'Empfänger- und Gruppenverwaltung',
                    'Template-System',
                    'E-Mail-Versand über Mailjet'
                ]
            ]
        ]
    ]
];