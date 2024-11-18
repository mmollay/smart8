<?php
class PlaceholderService
{
    private static ?PlaceholderService $instance = null;
    private string $locale;
    private string $timezone;
    private array $monthNames;
    private array $weekdayNames;

    /**
     * Private constructor to prevent direct creation
     */
    private function __construct(string $locale = 'de_DE.UTF-8', string $timezone = 'Europe/Berlin')
    {
        $this->locale = $locale;
        $this->timezone = $timezone;

        // Setze Locale und Timezone
        setlocale(LC_TIME, $locale, 'de_DE', 'deu_deu');
        date_default_timezone_set($timezone);

        // Deutsche Monatsnamen
        $this->monthNames = [
            1 => 'Januar',
            'Februar',
            'März',
            'April',
            'Mai',
            'Juni',
            'Juli',
            'August',
            'September',
            'Oktober',
            'November',
            'Dezember'
        ];

        // Deutsche Wochentagsnamen
        $this->weekdayNames = [
            'Monday' => 'Montag',
            'Tuesday' => 'Dienstag',
            'Wednesday' => 'Mittwoch',
            'Thursday' => 'Donnerstag',
            'Friday' => 'Freitag',
            'Saturday' => 'Samstag',
            'Sunday' => 'Sonntag'
        ];
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Get the singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Generiert die Anrede basierend auf den Empfängerdaten
     */
    public function generateAnrede($gender, $title, $lastName): string
    {
        $anrede = 'Sehr geehrte';
        if ($gender === 'male') {
            $anrede .= 'r Herr';
        } elseif ($gender === 'female') {
            $anrede .= ' Frau';
        }
        if ($title) {
            $anrede .= ' ' . $title;
        }
        if ($lastName) {
            $anrede .= ' ' . $lastName;
        }
        return $anrede;
    }

    /**
     * Erstellt alle Platzhalter für einen Empfänger
     * @param array $recipientData Array mit Empfängerdaten
     * @return array Array mit allen Platzhaltern
     */
    public function createPlaceholders(array $recipientData): array
    {
        $now = new DateTime();

        $placeholders = [
            // Personendaten
            'vorname' => $recipientData['first_name'] ?? '',
            'nachname' => $recipientData['last_name'] ?? '',
            'titel' => $recipientData['title'] ?? '',
            'geschlecht' => $recipientData['gender'] ?? '',

            // Firmendaten
            'firma' => $recipientData['company'] ?? '',
            'company' => $recipientData['company'] ?? '', // Alias für Abwärtskompatibilität

            // Kontaktdaten
            'email' => $recipientData['email'] ?? '',

            // Datums- und Zeitangaben (deutsch formatiert)
            'datum' => $now->format('d.m.Y'),
            'datum_lang' => $this->weekdayNames[$now->format('l')] . ', ' .
                $now->format('d') . '. ' .
                $this->monthNames[(int) $now->format('n')] . ' ' .
                $now->format('Y'),
            'datum_kurz' => $now->format('d.m.y'),
            'uhrzeit' => $now->format('H:i'),
            'uhrzeit_lang' => $now->format('H:i:s'),

            // Zusätzliche Formatierungen
            'monat' => $this->monthNames[(int) $now->format('n')],
            'jahr' => $now->format('Y'),
            'wochentag' => $this->weekdayNames[$now->format('l')]
        ];

        // Generiere Anrede
        $placeholders['anrede'] = $this->generateAnrede(
            $recipientData['gender'] ?? '',
            $recipientData['title'] ?? '',
            $recipientData['last_name'] ?? ''
        );

        return $placeholders;
    }

    /**
     * Ersetzt Platzhalter im Text
     * @param string $text Text mit Platzhaltern
     * @param array $placeholders Array mit Platzhaltern
     * @return string Text mit ersetzten Platzhaltern
     */
    public function replacePlaceholders(string $text, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        return $text;
    }

    /**
     * Fügt Debug-Informationen für Test-Mails hinzu
     */
    public function addDebugInfo(string $message, array $placeholders): string
    {
        $placeholderInfo = [];
        foreach ($placeholders as $key => $value) {
            $placeholderInfo[] = "{{" . $key . "}} = " . $value;
        }

        $debugInfo = "\n\n
<hr>
<p><strong>Debug-Informationen für Test-Mail:</strong></p>";
        $debugInfo .= "
<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 4px;'>";
        $debugInfo .= "Verfügbare Platzhalter:\n";
        $debugInfo .= implode("\n", $placeholderInfo);
        $debugInfo .= "</pre>";

        return $message . $debugInfo;
    }

    /**
     * Gibt eine Liste aller verfügbaren Platzhalter zurück
     * @return array Array mit Platzhalter-Beschreibungen
     */
    public static function getAvailablePlaceholders(): array
    {
        return [
            'Personendaten' => [
                'vorname' => 'Vorname des Empfängers',
                'nachname' => 'Nachname des Empfängers',
                'titel' => 'Akademischer Titel',
                'geschlecht' => 'Geschlecht (male/female)',
                'anrede' => 'Automatisch generierte Anrede'
            ],
            'Firmendaten' => [
                'firma' => 'Firmenname',
                'company' => 'Firmenname (Alias)'
            ],
            'Kontaktdaten' => [
                'email' => 'E-Mail-Adresse'
            ],
            'Datum und Zeit' => [
                'datum' => 'Aktuelles Datum (z.B. 11.11.2024)',
                'datum_lang' => 'Ausführliches Datum (z.B. Montag, 11. November 2024)',
                'datum_kurz' => 'Kurzes Datum (z.B. 11.11.24)',
                'uhrzeit' => 'Aktuelle Uhrzeit (z.B. 14:30)',
                'uhrzeit_lang' => 'Ausführliche Uhrzeit (z.B. 14:30:45)',
                'monat' => 'Aktueller Monat ausgeschrieben',
                'jahr' => 'Aktuelles Jahr',
                'wochentag' => 'Aktueller Wochentag ausgeschrieben'
            ]
        ];
    }
}