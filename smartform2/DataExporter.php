<?php
// classes/DataExporter.php

class DataExporter
{
    protected $db;
    protected $config;

    public function __construct($db)
    {
        $this->db = $db;
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $this->config = [
            'groups' => [
                'query' => "
                    SELECT 
                        g.id,
                        g.name,
                        g.color,
                        COUNT(DISTINCT r.id) as recipient_count,
                        DATE_FORMAT(g.created_at, '%d.%m.%Y %H:%i') as created
                    FROM groups g
                    LEFT JOIN recipient_group rg ON g.id = rg.group_id
                    LEFT JOIN recipients r ON rg.recipient_id = r.id
                    GROUP BY g.id, g.name, g.color, g.created_at
                    ORDER BY g.id ASC
                ",
                'headers' => [
                    'id' => 'ID',
                    'name' => 'Gruppenname',
                    'color' => 'Farbe',
                    'recipient_count' => 'Anzahl Empfänger',
                    'created' => 'Erstellt am'
                ]
            ],
            'recipients' => [
                'query' => "
                    SELECT 
                        r.id,
                        r.first_name,
                        r.last_name,
                        r.email,
                        r.company,
                        CASE 
                            WHEN r.unsubscribed = 1 THEN 'Abgemeldet'
                            WHEN r.bounce_status = 'hard' THEN 'Hard Bounce'
                            WHEN r.bounce_status = 'soft' THEN 'Soft Bounce'
                            ELSE 'Aktiv'
                        END as status,
                        GROUP_CONCAT(g.name ORDER BY g.name SEPARATOR ', ') as groups,
                        DATE_FORMAT(r.created_at, '%d.%m.%Y %H:%i') as created
                    FROM recipients r
                    LEFT JOIN recipient_group rg ON r.id = rg.recipient_id
                    LEFT JOIN groups g ON rg.group_id = g.id
                    GROUP BY r.id
                    ORDER BY r.id ASC
                ",
                'headers' => [
                    'id' => 'ID',
                    'first_name' => 'Vorname',
                    'last_name' => 'Nachname',
                    'email' => 'E-Mail',
                    'company' => 'Firma',
                    'status' => 'Status',
                    'groups' => 'Gruppen',
                    'created' => 'Erstellt am'
                ]
            ],
            'newsletters' => [
                'query' => "
                    SELECT 
                        ec.id,
                        ec.subject,
                        CONCAT(s.first_name, ' ', s.last_name) as sender,
                        s.email as sender_email,
                        COUNT(DISTINCT ej.recipient_id) as total_recipients,
                        SUM(CASE WHEN ej.status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                        SUM(CASE WHEN ej.status = 'open' THEN 1 ELSE 0 END) as opened,
                        SUM(CASE WHEN ej.status = 'click' THEN 1 ELSE 0 END) as clicked,
                        DATE_FORMAT(MAX(ej.sent_at), '%d.%m.%Y %H:%i') as sent_date,
                        GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') as groups
                    FROM email_contents ec
                    LEFT JOIN senders s ON ec.sender_id = s.id
                    LEFT JOIN email_jobs ej ON ec.id = ej.content_id
                    LEFT JOIN email_content_groups ecg ON ec.id = ecg.email_content_id
                    LEFT JOIN groups g ON ecg.group_id = g.id
                    GROUP BY ec.id
                    ORDER BY ec.id DESC
                ",
                'headers' => [
                    'id' => 'ID',
                    'subject' => 'Betreff',
                    'sender' => 'Absender',
                    'sender_email' => 'Absender E-Mail',
                    'total_recipients' => 'Empfänger',
                    'delivered' => 'Zugestellt',
                    'opened' => 'Geöffnet',
                    'clicked' => 'Geklickt',
                    'sent_date' => 'Gesendet am',
                    'groups' => 'Gruppen'
                ]
            ]
        ];
    }

    public function export($type, $format = 'csv')
    {
        if (!isset($this->config[$type])) {
            throw new Exception("Ungültiger Export-Typ: $type");
        }

        $data = $this->getData($type);

        switch ($format) {
            case 'csv':
                $this->exportCSV($data, $this->config[$type]['headers']);
                break;
            case 'excel':
                $this->exportExcel($data, $this->config[$type]['headers']);
                break;
            default:
                throw new Exception("Ungültiges Export-Format: $format");
        }
    }

    protected function getData($type)
    {
        $result = $this->db->query($this->config[$type]['query']);
        if (!$result) {
            throw new Exception("Datenbankfehler: " . $this->db->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    protected function exportCSV($data, $headers)
    {
        // Setze Header für Download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="export_' . date('Y-m-d_His') . '.csv"');

        // Öffne Output-Stream
        $output = fopen('php://output', 'w');

        // BOM für Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Schreibe Header
        fputcsv($output, array_values($headers));

        // Schreibe Daten
        foreach ($data as $row) {
            $exportRow = [];
            foreach (array_keys($headers) as $key) {
                $exportRow[] = $row[$key] ?? '';
            }
            fputcsv($output, $exportRow);
        }

        fclose($output);
    }

    protected function exportExcel($data, $headers)
    {
        // Implementierung für Excel-Export
        throw new Exception("Excel-Export noch nicht implementiert");
    }
}