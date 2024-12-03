<?php
require_once(__DIR__ . '/../../../DashboardClass.php');

// Dashboard-Instanz erstellen
$dashboard = new Dashboard($title, $db, $_SESSION['user_id'], '1.0.0', 'main');

// Content Header
$content = "<div align='center'>
   <img src='../../img/logo.png' alt='Logo' style='width: 200px; height: auto;'><br><br>
</div>";

$content .= "<div class='ui link cards centered'>";

if ($isSuperuser) {
    // Load all active modules for superuser
    $query = "
        SELECT 
            module_id,
            name,
            identifier,
            description,
            icon
        FROM modules 
        WHERE status = 1
        ORDER BY menu_order, name
    ";

    $result = $db->query($query);

    while ($module = $result->fetch_assoc()) {
        $content .= call_field_small(
            $module['name'],
            $module['icon'],
            '',
            '../' . $module['identifier'] . '/',
            $module['description']
        );
    }
} else {
    // Load only assigned modules for regular users
    $query = "
        SELECT DISTINCT 
            m.module_id,
            m.name,
            m.identifier,
            m.description,
            m.icon
        FROM modules m
        INNER JOIN user_modules um ON m.module_id = um.module_id
        WHERE um.user_id = ? 
        AND um.status = 1
        AND m.status = 1
        ORDER BY m.menu_order, m.name
    ";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($module = $result->fetch_assoc()) {
        $content .= call_field_small(
            $module['name'],
            $module['icon'],
            '',
            '../' . $module['identifier'] . '/',
            $module['description']
        );
    }
}

$content .= "</div><br>";

// Modal für neue Webseite
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