<?php
include_once __DIR__ . '/../classes/PlaceholderService.php';

function getPlaceholdersHTML()
{
    // Hole verfügbare Platzhalter aus dem Service
    $availablePlaceholders = PlaceholderService::getAvailablePlaceholders();

    $html = '<div class="ui tiny compact buttons">';

    // Gruppiere die Buttons nach Kategorien
    foreach ($availablePlaceholders as $category => $placeholders) {
        // Kategorie als Überschrift
        // $html .= sprintf(
        //     '<div class="ui basic label" style="margin-right: 10px;">%s</div>',
        //     htmlspecialchars($category)
        // );

        // Buttons für jede Kategorie
        foreach ($placeholders as $key => $tooltip) {
            $html .= sprintf(
                '<button type="button" class="ui compact button placeholder-button" 
                 onclick="NewsletterEditor.insertPlaceholder(\'{{%s}}\')"
                 data-placeholder="{{%s}}"
                 data-tooltip="%s"
                 data-position="top center"
                 data-variation="tiny">%s</button>',
                $key,
                $key,
                htmlspecialchars($tooltip),
                ucfirst($key)
            );
        }

    }

    $html .= '</div>';
    return $html;
}