<?php
class DatabaseManager
{
    private $mainDb;  // Verbindung zur Hauptdatenbank (ssi_company2)

    public function __construct($mainDb)
    {
        $this->mainDb = $mainDb;
    }

    public function createUserDatabase($userId, $company = '')
    {
        // Generiere einen sicheren Datenbanknamen
        $dbName = 'ssi_newsletter_' . $this->sanitizeDatabaseName($company) . '_' . $userId;

        try {
            // Erstelle neue Datenbank
            $this->mainDb->query("CREATE DATABASE IF NOT EXISTS `$dbName` 
                                 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Verbinde zur neuen Datenbank
            $userDb = new mysqli(
                $_ENV['NEWSLETTER_DB_HOST'],
                $_ENV['NEWSLETTER_DB_USERNAME'],
                $_ENV['NEWSLETTER_DB_PASSWORD'],
                $dbName
            );

            // Importiere Grundstruktur
            $this->importBaseStructure($userDb);

            return $dbName;

        } catch (Exception $e) {
            error_log("Fehler beim Erstellen der User-Datenbank: " . $e->getMessage());
            throw $e;
        }
    }

    private function sanitizeDatabaseName($name)
    {
        // Entferne alle nicht-alphanumerischen Zeichen
        $clean = preg_replace('/[^a-z0-9]/i', '_', strtolower($name));
        // Begrenze die Länge
        return substr($clean, 0, 20);
    }

    private function importBaseStructure($db)
    {
        // Lese SQL-Struktur aus Datei
        $sql = file_get_contents(__DIR__ . '/../sql/newsletter_base_structure.sql');

        // Führe jedes Statement einzeln aus
        foreach (explode(';', $sql) as $statement) {
            if (trim($statement)) {
                $db->query($statement);
            }
        }
    }

    public function getUserDatabaseConnection($userId)
    {
        // Hole den Datenbanknamen aus der Zuweisung
        $stmt = $this->mainDb->prepare("
            SELECT database_name 
            FROM newsletter_user_assignments 
            WHERE user_id = ? 
            AND valid_until IS NULL
            LIMIT 1
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Verbinde zur User-Datenbank
            return new mysqli(
                $_ENV['NEWSLETTER_DB_HOST'],
                $_ENV['NEWSLETTER_DB_USERNAME'],
                $_ENV['NEWSLETTER_DB_PASSWORD'],
                $row['database_name']
            );
        }

        throw new Exception("Keine aktive Datenbank für User $userId gefunden");
    }
}