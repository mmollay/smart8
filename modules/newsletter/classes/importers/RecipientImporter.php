<?php
class RecipientImporter
{
    private $db;
    private $userId;
    private $allowedColumns = [
        'first_name' => 'Vorname',
        'last_name' => 'Nachname',
        'company' => 'Firma',
        'email' => 'E-Mail',
        'gender' => 'Geschlecht',
        'title' => 'Titel',
        'comment' => 'Kommentar'
    ];
    private $errors = [];
    private $imported = 0;
    private $updated = 0;
    private $skipped = 0;

    public function __construct($db)
    {
        global $userId;
        $this->db = $db;
        $this->userId = $userId;
    }

    public function processImport($file, $group_ids = [], $skipFirstRow = true, $delimiter = ',', $overwriteExisting = false)
    {
        // Prüfe ob die Gruppen dem User gehören
        if (!empty($group_ids)) {
            $groupCheck = $this->db->prepare("SELECT COUNT(*) as count FROM groups WHERE id IN (" . implode(',', array_fill(0, count($group_ids), '?')) . ") AND user_id = ?");
            $types = str_repeat('i', count($group_ids)) . 'i';
            $params = array_merge($group_ids, [$this->userId]);
            $groupCheck->bind_param($types, ...$params);
            $groupCheck->execute();
            $result = $groupCheck->get_result()->fetch_assoc();
            if ($result['count'] != count($group_ids)) {
                throw new Exception('Ungültige Gruppen ausgewählt');
            }
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Keine Datei hochgeladen');
        }

        $fileHandle = fopen($file['tmp_name'], 'r');
        if (!$fileHandle) {
            throw new Exception('Datei konnte nicht geöffnet werden');
        }

        $this->db->begin_transaction();
        try {
            // Erste Zeile lesen für Header
            $headers = fgetcsv($fileHandle, 0, $delimiter);
            if (!$headers) {
                throw new Exception('Datei ist leer oder fehlerhaft');
            }

            // Header normalisieren
            $headers = array_map('trim', $headers);
            $headerMap = $this->validateHeaders($headers);

            // Erste Zeile überspringen wenn gewünscht
            if ($skipFirstRow) {
                rewind($fileHandle);
                fgetcsv($fileHandle);
            } else {
                rewind($fileHandle);
            }

            // Zeile für Zeile verarbeiten
            while (($row = fgetcsv($fileHandle, 0, $delimiter)) !== false) {
                $this->processRow($row, $headerMap, $group_ids, $overwriteExisting);
            }

            $this->db->commit();
            fclose($fileHandle);

            return [
                'success' => true,
                'imported' => $this->imported,
                'updated' => $this->updated,
                'skipped' => $this->skipped,
                'errors' => $this->errors
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            fclose($fileHandle);
            throw $e;
        }
    }

    private function validateHeaders($headers)
    {
        $headerMap = [];
        foreach ($headers as $index => $header) {
            $normalizedHeader = strtolower(trim($header));
            if (array_key_exists($normalizedHeader, $this->allowedColumns)) {
                $headerMap[$index] = $normalizedHeader;
            }
        }
        if (empty($headerMap)) {
            throw new Exception('Keine gültigen Spalten gefunden');
        }
        return $headerMap;
    }

    private function processRow($row, $headerMap, $group_ids, $overwriteExisting)
    {
        $recipientData = [];
        foreach ($headerMap as $index => $field) {
            if (isset($row[$index])) {
                $recipientData[$field] = trim($row[$index]);
            }
        }

        // Füge user_id hinzu
        $recipientData['user_id'] = $this->userId;

        // Überprüfe ob mindestens E-Mail vorhanden ist
        if (empty($recipientData['email'])) {
            $this->errors[] = "Zeile übersprungen: Keine E-Mail-Adresse angegeben";
            $this->skipped++;
            return;
        }

        // Prüfe ob E-Mail bereits existiert für diesen User
        $stmt = $this->db->prepare("SELECT id FROM recipients WHERE email = ? AND user_id = ?");
        $stmt->bind_param("si", $recipientData['email'], $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingUser = $result->fetch_assoc();

        if ($existingUser) {
            if ($overwriteExisting) {
                // Update bestehenden Benutzer
                $updateFields = [];
                $updateValues = [];
                $types = "";

                foreach ($recipientData as $field => $value) {
                    if ($field !== 'email' && $field !== 'user_id') { // E-Mail und user_id nicht updaten
                        $updateFields[] = "$field = ?";
                        $updateValues[] = $value;
                        $types .= "s";
                    }
                }

                if (!empty($updateFields)) {
                    $sql = "UPDATE recipients SET " . implode(', ', $updateFields) . " WHERE id = ? AND user_id = ?";
                    $stmt = $this->db->prepare($sql);

                    $updateValues[] = $existingUser['id'];
                    $updateValues[] = $this->userId;
                    $types .= "ii";

                    $stmt->bind_param($types, ...$updateValues);

                    if ($stmt->execute()) {
                        $this->updated++;

                        if (!empty($group_ids)) {
                            // Lösche bestehende Gruppenzuordnungen
                            $deleteStmt = $this->db->prepare("
                                DELETE rg FROM recipient_group rg 
                                JOIN groups g ON rg.group_id = g.id 
                                WHERE rg.recipient_id = ? AND g.user_id = ?
                            ");
                            $deleteStmt->bind_param("ii", $existingUser['id'], $this->userId);
                            $deleteStmt->execute();

                            // Füge neue Gruppenzuordnungen hinzu
                            $groupInsertStmt = $this->db->prepare(
                                "INSERT INTO recipient_group (recipient_id, group_id) VALUES (?, ?)"
                            );

                            foreach ($group_ids as $group_id) {
                                $groupInsertStmt->bind_param("ii", $existingUser['id'], $group_id);
                                $groupInsertStmt->execute();
                            }
                        }
                    } else {
                        $this->errors[] = "Fehler beim Update von: {$recipientData['email']}";
                        $this->skipped++;
                    }
                }
            } else {
                $this->errors[] = "E-Mail bereits vorhanden (übersprungen): {$recipientData['email']}";
                $this->skipped++;
            }
            return;
        }

        // Erstelle neuen Empfänger
        $columns = array_keys($recipientData);
        $values = array_values($recipientData);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';

        $sql = "INSERT INTO recipients (" . implode(',', $columns) . ") VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);

        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            $recipient_id = $this->db->insert_id;

            if (!empty($group_ids)) {
                $groupInsertStmt = $this->db->prepare(
                    "INSERT INTO recipient_group (recipient_id, group_id) VALUES (?, ?)"
                );

                foreach ($group_ids as $group_id) {
                    $groupInsertStmt->bind_param("ii", $recipient_id, $group_id);
                    $groupInsertStmt->execute();
                }
            }

            $this->imported++;
        } else {
            $this->errors[] = "Fehler beim Import von: {$recipientData['email']}";
            $this->skipped++;
        }
    }
}