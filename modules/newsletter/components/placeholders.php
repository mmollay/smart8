<?
function getPlaceholdersHTML()
{
    $placeholders = [
        // Empfänger-bezogene Platzhalter
        ['key' => 'anrede', 'tooltip' => 'Automatische Anrede (z.B. "Sehr geehrter Herr Dr. Mustermann")'],
        ['key' => 'titel', 'tooltip' => 'Akademischer Titel (z.B. "Dr.")'],
        ['key' => 'vorname', 'tooltip' => 'Vorname des Empfängers'],
        ['key' => 'nachname', 'tooltip' => 'Nachname des Empfängers'],
        ['key' => 'firma', 'tooltip' => 'Firmenname des Empfängers'],
        ['key' => 'email', 'tooltip' => 'E-Mail-Adresse'],
        // Datum/Zeit Platzhalter
        ['key' => 'datum', 'tooltip' => 'Aktuelles Datum (Format: DD.MM.YYYY)'],
        ['key' => 'uhrzeit', 'tooltip' => 'Aktuelle Uhrzeit (Format: HH:MM)']
    ];

    $html = '<div class="ui tiny compact buttons">';

    // Generate placeholder buttons
    foreach ($placeholders as $item) {
        $html .= sprintf(
            '<button type="button" class="ui compact button placeholder-button" 
             onclick="NewsletterEditor.insertPlaceholder(\'{{%s}}\')"
             data-placeholder="{{%s}}"
             data-tooltip="%s"
             data-position="top center"
             data-variation="tiny">%s</button>',
            $item['key'],
            $item['key'],
            $item['tooltip'],
            ucfirst($item['key'])
        );
    }

    $html .= '</div>';

    return $html;
}