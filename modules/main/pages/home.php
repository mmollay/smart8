<?php
include (__DIR__ . '/../config.php');

$set_module['faktura'] = true;
$set_module['newsletter'] = true;
$set_module['service'] = true;

// $set_module['userlist'] = true;
// $set_module['setting'] = true;

$content = "<div align='center'>
    <img src='../../img/logo.png' alt='Logo' style='width: 200px; height: auto;'><br><br>
</div>";

$content .= "<div class='ui link cards centered'>";

// Definition der Module
$modules = [
    //'newsletter' => ["Newsletter", 'send outline', '', '../newsletter/', 'Versende wichtige Mail an deine Newsletterliste'],
    'faktura' => ["Faktura", 'book', '', '../faktura/', 'Deine Buchhaltung immer im Griff'],
    // 'trader' => ["Trader", 'wallet', '', 'modules/trader/', 'Make your mouney'],
    // 'learning' => ["Learning", 'student', '', 'modules/learning/', 'Für Prüfungen üben'],
    // 'kmlist' => ["KM-Liste", 'road', '', 'modules/km/', 'Jeder Kilometer zählt :)'],
    // 'map' => ["Fruit-Map", 'fruit-apple', '', 'modules/map/', 'Maps'],
    // 'userlist' => ["User/Domain", 'list', '', 'modules/userlist/', 'User/Domain und andere Übersichten'],
    // 'setting' => ["Einstellungen", 'settings layout', '', 'modules/setting/', 'Registrierseite bearbeiten und andere Einstellungen'],
    'service' => ["Service", 'configure', '', '../service/', 'Sicherheit, Reinigung, Überprüfungen'],
    // 'paneon' => ["Paneon", 'blue dove', '', 'modules/paneon/', 'Paneon-Userverwaltung'],
];

foreach ($modules as $module => $fields) {
    if (isset($set_module[$module])) {
        $content .= call_field_small(...$fields);
    }
}

$content .= "</div><br>";

// Modal hinzufügen
$content .= "<div class='ui modal new_page'>
    <div class='header'>Neue Webseite anlegen</div>
    <div class='content'></div>
    <div class='actions'>
        <div class='ui button green approve'><i class='icon checkmark'></i> Webseite erzeugen</div>
        <div class='ui button deny'>Schließen</div>
    </div>
</div>";

echo $content;

function call_field_small($title, $icon, $text, $link = '', $tooltip = '')
{
    $output = "<a href='" . htmlspecialchars($link) . "' class='card tooltip-top' title='" . htmlspecialchars($tooltip) . "' style='width:130px; height:130px'>";
    $output .= "<div class='content' align='center'>";
    $output .= "<div class='ui small header'>" . htmlspecialchars($title) . "</div>";
    if ($icon) {
        $output .= "<i class='" . htmlspecialchars($icon) . " huge icon'></i><br>";
    }
    $output .= htmlspecialchars($text);
    $output .= "</div>";
    $output .= "</a>";
    return $output;
}
?>