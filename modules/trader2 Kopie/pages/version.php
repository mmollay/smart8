<?php
if (isset($_GET['ajax'])) {
    include '../../../config.php';
}

echo '<div class="ui segment">
<div class="ui message">
<h3>Version History</h3>';

// Changelog aus Datei lesen
$changelogFile = __DIR__ . '/../change_log.txt';

if (file_exists($changelogFile)) {
    $lines = file($changelogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // Variable für aktuelle Version
    $currentVersion = '';
    $currentCategory = '';
    
    foreach ($lines as $line) {
        // Leere Zeilen überspringen
        if (trim($line) === '') {
            continue;
        }
        
        // Prüfen ob es eine Versionszeile ist
        if (preg_match('/^Version\s+(.+)\s+\((.*)\)$/', $line, $matches)) {
            // Wenn wir eine vorherige Version verarbeitet haben, div schließen
            if ($currentVersion !== '') {
                if ($currentCategory !== '') {
                    echo '</ul>';
                }
                echo '</div>';
            }
            
            $currentVersion = $matches[1];
            $date = $matches[2];
            echo "<div class=\"message ui\">
                <div align=\"left\"><b>Version $currentVersion</b> <span class=\"ui text grey\">($date)</span></div>";
            $currentCategory = '';
        }
        // Prüfen ob es eine Kategorie ist
        elseif (substr(trim($line), -1) === ':') {
            if ($currentCategory !== '') {
                echo '</ul>';
            }
            $currentCategory = trim($line, ':');
            echo "<div class=\"ui small header\">$currentCategory</div><ul class=\"list\">";
        }
        // Wenn es keine Version und keine Kategorie ist, ist es ein Änderungseintrag
        elseif ($currentVersion !== '' && trim($line) !== '') {
            // Wenn noch keine Kategorie definiert ist, "Allgemein" als Standard nehmen
            if ($currentCategory === '') {
                $currentCategory = 'Allgemein';
                echo "<div class=\"ui small header\">$currentCategory</div><ul class=\"list\">";
            }
            echo "<li>" . htmlspecialchars(ltrim($line, '- ')) . "</li>";
        }
    }
    
    // Letztes div schließen falls vorhanden
    if ($currentVersion !== '') {
        if ($currentCategory !== '') {
            echo '</ul>';
        }
        echo '</div>';
    }
} else {
    echo '<p>Changelog nicht gefunden.</p>';
}

echo '</div></div>';
?>