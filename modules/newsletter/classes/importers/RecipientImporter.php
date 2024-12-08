<?php

class RecipientImporter
{
    private $db;
    private $userId;
    private $config;
    private $errors = [];
    private $imported = 0;
    private $updated = 0;
    private $skipped = 0;
    private $importId;
    private $batchSize = 100; // Verarbeite 100 Datensätze pro Durchgang

    public function __construct($db)
    {
        global $userId;
        $this->db = $db;
        $this->userId = $userId;
        $this->config = require(__DIR__ . '/../../config/config.php');

        // Erstelle neuen Import-Eintrag
        $stmt = $this->db->prepare("INSERT INTO import_progress (user_id) VALUES (?)");
        $stmt->bind_param('i', $this->userId);
        $stmt->execute();
        $this->importId = $this->db->insert_id;
        $_SESSION['current_import_id'] = $this->importId;
    }

    public function processImport($file, $group_ids = [], $skipFirstRow = true, $delimiter = ',', $overwriteExisting = false)
    {
        // Delimiter-Behandlung
        if ($delimiter === '\t') {
            $delimiter = "\t";
        }

        // Validiere Gruppen-IDs
        if (!empty($group_ids)) {
            if (!$this->validateGroups($group_ids)) {
                throw new Exception('Ungültige Gruppen ausgewählt');
            }
        }

        // Datei-Validierung
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Keine Datei hochgeladen');
        }

        $fileHandle = fopen($file['tmp_name'], 'r');
        if (!$fileHandle) {
            throw new Exception('Datei konnte nicht geöffnet werden');
        }

        $this->db->begin_transaction();
        try {
            // Zähle Gesamtanzahl der Zeilen
            $lineCount = count(file($file['tmp_name'])) - ($skipFirstRow ? 1 : 0);
            $this->updateProgress(['total_records' => $lineCount]);

            // Erste Zeile lesen
            $firstRow = fgetcsv($fileHandle, 0, $delimiter);
            if (!$firstRow) {
                throw new Exception('Datei ist leer oder fehlerhaft');
            }

            // Konvertiere Encoding falls nötig
            $firstRow = array_map(function ($value) {
                return mb_convert_encoding($value, 'UTF-8', 'auto');
            }, $firstRow);

            // Header verarbeiten
            $headerMap = $this->validateHeaders($firstRow);

            // Zurückspulen wenn es sich bei der ersten Zeile um Daten handelt
            if (!$skipFirstRow || filter_var($firstRow[0], FILTER_VALIDATE_EMAIL)) {
                rewind($fileHandle);
            }

            $processedRows = 0;
            // Zeile für Zeile verarbeiten
            while (($row = fgetcsv($fileHandle, 0, $delimiter)) !== false) {
                // Encoding konvertieren
                $row = array_map(function ($value) {
                    return mb_convert_encoding($value, 'UTF-8', 'auto');
                }, $row);

                $this->processRow($row, $headerMap, $group_ids, $overwriteExisting);

                $processedRows++;
                if ($processedRows % $this->batchSize === 0) {
                    $this->updateProgress([
                        'processed_records' => $processedRows,
                        'imported' => $this->imported,
                        'updated' => $this->updated,
                        'skipped' => $this->skipped
                    ]);
                }
            }

            // Finaler Update
            $this->updateProgress([
                'processed_records' => $processedRows,
                'imported' => $this->imported,
                'updated' => $this->updated,
                'skipped' => $this->skipped,
                'status' => 'completed'
            ]);

            $this->db->commit();
            fclose($fileHandle);

            return [
                'success' => true,
                'import_id' => $this->importId,
                'imported' => $this->imported,
                'updated' => $this->updated,
                'skipped' => $this->skipped,
                'errors' => $this->errors
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            fclose($fileHandle);
            $this->updateProgress([
                'status' => 'error',
                'error_message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function validateHeaders($headers)
    {
        // Wenn die erste Zeile eine E-Mail ist, nutzen wir die konfigurierte Reihenfolge
        if (filter_var($headers[0], FILTER_VALIDATE_EMAIL)) {
            $headerMap = [];
            foreach ($this->config['import_export']['column_order'] as $index => $field) {
                if (isset($headers[$index])) {
                    $headerMap[$index] = $field;
                }
            }
            return $headerMap;
        }

        // Header-Validierung für den Fall mit Überschriften
        $headerMap = [];
        foreach ($headers as $index => $header) {
            $normalizedHeader = strtolower(trim($header));
            if (in_array($normalizedHeader, $this->config['import_export']['column_order'])) {
                $headerMap[$index] = $normalizedHeader;
            }
        }

        if (empty($headerMap)) {
            throw new Exception('Keine gültigen Spalten gefunden');
        }
        return $headerMap;
    }

    private function validateGroups($group_ids)
    {
        $groupCheck = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM groups 
            WHERE id IN (" . implode(',', array_fill(0, count($group_ids), '?')) . ") 
            AND user_id = ?
        ");

        $types = str_repeat('i', count($group_ids)) . 'i';
        $params = array_merge($group_ids, [$this->userId]);
        $groupCheck->bind_param($types, ...$params);
        $groupCheck->execute();
        $result = $groupCheck->get_result()->fetch_assoc();

        return $result['count'] == count($group_ids);
    }

    private function processRow($row, $headerMap, $group_ids, $overwriteExisting)
    {
        $recipientData = [];
        foreach ($headerMap as $index => $field) {
            if (isset($row[$index])) {
                $recipientData[$field] = trim($row[$index]);
            }
        }

        // Pflichtfelder prüfen
        foreach ($this->config['import_export']['required_fields'] as $required) {
            if (empty($recipientData[$required])) {
                $this->errors[] = "Zeile übersprungen: Pflichtfeld '$required' fehlt";
                $this->skipped++;
                return;
            }
        }

        // E-Mail-Validierung
        if (!filter_var($recipientData['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Ungültige E-Mail-Adresse: {$recipientData['email']}";
            $this->skipped++;
            return;
        }

        // User ID hinzufügen
        $recipientData['user_id'] = $this->userId;

        // Prüfe ob E-Mail bereits existiert
        $stmt = $this->db->prepare("SELECT id FROM recipients WHERE email = ? AND user_id = ?");
        $stmt->bind_param("si", $recipientData['email'], $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingUser = $result->fetch_assoc();

        if ($existingUser) {
            if ($overwriteExisting) {
                $this->updateRecipient($existingUser['id'], $recipientData, $group_ids);
            } else {
                $this->errors[] = "E-Mail bereits vorhanden (übersprungen): {$recipientData['email']}";
                $this->skipped++;
            }
            return;
        }

        $this->createRecipient($recipientData, $group_ids);
    }

    private function updateRecipient($id, $data, $group_ids)
    {
        $updateFields = [];
        $updateValues = [];
        $types = "";

        foreach ($data as $field => $value) {
            if ($field !== 'email' && $field !== 'user_id') {
                $updateFields[] = "$field = ?";
                $updateValues[] = $value;
                $types .= "s";
            }
        }

        if (!empty($updateFields)) {
            $sql = "UPDATE recipients SET " . implode(', ', $updateFields) . " WHERE id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);

            $updateValues[] = $id;
            $updateValues[] = $this->userId;
            $types .= "ii";

            $stmt->bind_param($types, ...$updateValues);

            if ($stmt->execute()) {
                $this->updated++;
                $this->updateGroups($id, $group_ids);
            } else {
                $this->errors[] = "Fehler beim Update von: {$data['email']}";
                $this->skipped++;
            }
        }
    }

    private function createRecipient($data, $group_ids)
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';

        $sql = "INSERT INTO recipients (" . implode(',', $columns) . ") VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);

        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            $recipient_id = $this->db->insert_id;
            $this->updateGroups($recipient_id, $group_ids);
            $this->imported++;
        } else {
            $this->errors[] = "Fehler beim Import von: {$data['email']}";
            $this->skipped++;
        }
    }

    private function updateGroups($recipient_id, $group_ids)
    {
        if (empty($group_ids)) {
            return;
        }

        // Bestehende Gruppen löschen
        $deleteStmt = $this->db->prepare("
            DELETE rg FROM recipient_group rg 
            JOIN groups g ON rg.group_id = g.id 
            WHERE rg.recipient_id = ? AND g.user_id = ?
        ");
        $deleteStmt->bind_param("ii", $recipient_id, $this->userId);
        $deleteStmt->execute();

        // Neue Gruppen zuweisen
        $groupInsertStmt = $this->db->prepare(
            "INSERT INTO recipient_group (recipient_id, group_id) VALUES (?, ?)"
        );
        foreach ($group_ids as $group_id) {
            $groupInsertStmt->bind_param("ii", $recipient_id, $group_id);
            $groupInsertStmt->execute();
        }
    }

    private function updateProgress($data)
    {
        $updates = [];
        $types = '';
        $values = [];

        foreach ($data as $key => $value) {
            $updates[] = "$key = ?";
            $types .= is_int($value) ? 'i' : 's';
            $values[] = $value;
        }

        $values[] = $this->importId;
        $types .= 'i';

        $sql = "UPDATE import_progress SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
    }
}