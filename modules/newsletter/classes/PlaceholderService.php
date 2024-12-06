<?php
class PlaceholderService
{
    private static ?PlaceholderService $instance = null;
    private string $locale;
    private string $timezone;
    private array $monthNames;
    private array $weekdayNames;

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

    // Singleton-Methoden bleiben unverändert
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Erstellt Platzhalter für einen Empfänger
     */
    public function createPlaceholders(array $recipientData): array
    {
        $now = new DateTime();

        $placeholders = [
            // Formelle Anrede
            'anrede_formell' => $this->getAnredeFormal(
                $recipientData['gender'] ?? '',
                $recipientData['title'] ?? '',
                $recipientData['first_name'] ?? '',
                $recipientData['last_name'] ?? ''
            ),
            // Persönliche Anrede
            'anrede_persoenlich' => $this->getAnredePersonal(
                $recipientData['gender'] ?? '',
                $recipientData['first_name'] ?? ''
            ),

            // Personendaten
            'titel' => $recipientData['title'] ?? '',
            'vorname' => $recipientData['first_name'] ?? '',
            'nachname' => $recipientData['last_name'] ?? '',
            'firma' => $recipientData['company'] ?? '',
            'email' => $recipientData['email'] ?? '',

            // Datum und Zeit
            'datum' => $now->format('d.m.Y'),
            'datum_lang' => $this->weekdayNames[$now->format('l')] . ', ' .
                $now->format('d') . '. ' .
                $this->monthNames[(int) $now->format('n')] . ' ' .
                $now->format('Y'),
            'uhrzeit' => $now->format('H:i'),
            'monat' => $this->monthNames[(int) $now->format('n')],
            'jahr' => $now->format('Y'),
            'wochentag' => $this->weekdayNames[$now->format('l')]
        ];

        return $placeholders;
    }

    private function getAnredeFormal(string $gender, string $title, string $firstName, string $lastName): string
    {
        $anrede = 'Sehr ';

        if ($gender === 'female') {
            $anrede .= 'geehrte' . ($title ? ' Frau ' . $title : ' Frau');
        } elseif ($gender === 'male') {
            $anrede .= 'geehrter' . ($title ? ' Herr ' . $title : ' Herr');
        } else {
            return 'Sehr geehrte Damen und Herren';
        }

        return $anrede . ' ' . $lastName;
    }

    private function getAnredePersonal(string $gender, string $firstName): string
    {
        if (empty($firstName)) {
            return 'Hallo';
        }

        if ($gender === 'female') {
            return 'Liebe ' . $firstName;
        } elseif ($gender === 'male') {
            return 'Lieber ' . $firstName;
        }

        return 'Hallo ' . $firstName;
    }

    /**
     * Ersetzt Platzhalter im Text
     */
    public function replacePlaceholders(string $text, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        return $text;
    }

    /**
     * Liste aller verfügbaren Platzhalter
     */
    public static function getAvailablePlaceholders(): array
    {
        return [
            'Anrede' => [
                'anrede_formell' => 'Formelle Anrede (z.B. Sehr geehrter Herr Dr. Mustermann)',
                'anrede_persoenlich' => 'Persönliche Anrede (z.B. Lieber Thomas)',
            ],
            'Personendaten' => [
                'titel' => 'Akademischer Titel (z.B. Dr., Prof.)',
                'vorname' => 'Vorname des Empfängers',
                'nachname' => 'Nachname des Empfängers',
                'firma' => 'Firmenname',
                'email' => 'E-Mail-Adresse'
            ],
            'Datum und Zeit' => [
                'datum' => 'Aktuelles Datum (DD.MM.YYYY)',
                'datum_lang' => 'Ausführliches Datum',
                'uhrzeit' => 'Aktuelle Uhrzeit (HH:MM)',
                'monat' => 'Aktueller Monat ausgeschrieben',
                'jahr' => 'Aktuelles Jahr',
                'wochentag' => 'Aktueller Wochentag'
            ]
        ];
    }

    /**
     * Debug-Informationen für Test-Mails
     */
    public function addDebugInfo(string $message, array $placeholders): string
    {
        $debugInfo = "\n\n<hr><div style='background-color: #f8f9fa; padding: 15px; margin-top: 20px;'>";
        $debugInfo .= "<h4>Debug-Informationen für Test-Mail</h4>";
        $debugInfo .= "<table style='width: 100%; border-collapse: collapse;'>";
        $debugInfo .= "<tr><th style='text-align: left; padding: 5px;'>Platzhalter</th><th style='text-align: left; padding: 5px;'>Wert</th></tr>";

        foreach ($placeholders as $key => $value) {
            $debugInfo .= sprintf(
                "<tr><td style='padding: 5px;'>{{%s}}</td><td style='padding: 5px;'>%s</td></tr>",
                htmlspecialchars($key),
                htmlspecialchars($value)
            );
        }

        $debugInfo .= "</table></div>";

        return $message . $debugInfo;
    }
}