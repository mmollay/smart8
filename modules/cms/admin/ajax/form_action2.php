<?php
// Zugangsdaten fuer die Datenbank
include_once ('../../../login/config_main.inc.php');

// include ('../config.php');

foreach ($_POST as $key => $value) {
    if ($value) {
        $GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string($value);
    }
}

if (! $id) {
    echo "alert('Keine ID vorhanden.')";
    return;
}

switch ($action) {

    case 'clone':
    case 'clonemove':
        if (! $count)
            $count = 1;
        // Textfeld clonen
        for ($ii = 0; $ii < $count; $ii ++) {
            // clone_layer ( $id );
            $id_clone = clone_layer_splitter($id);
        }

        $message = "Das gewünschte Feld wurde $count mal geklont!";

        if ($action != 'clonemove') {
            echo "$('.button_reload').click();";
        }

        if (! $_SESSION['site_id']) {
            echo "alert('{$_SESSION['site_id']}')";
            echo "alert ( 'Seiten_ID ist nicht definiert' )";
        } else if ($action == 'clonemove') {
           
            if ($move_site_id) {

                // $id = $GLOBALS['first_clone_layer_id'];
                $id = $id_clone;
                // echo "alert('$id');";
                // exit;
                // Name auslesen
                $name = mysql_singleoutput("SELECT title FROM smart_langSite WHERE fk_id = '$move_site_id' ") or die(mysqli_error($GLOBALS['mysqli']));

                // Macht Platz für das Feld welches verschoben wird
                $GLOBALS['mysqli']->query("UPDATE smart_layer SET sort=sort+1 WHERE site_id = '$move_site_id' ") or die(mysqli_error($GLOBALS['mysqli']));
                // postioniert das verschoben Feld auf die Seite ganz oben links(kann nachträglich auf die gewünschte Position gebracht werden
                $GLOBALS['mysqli']->query("UPDATE smart_layer SET site_id = '$move_site_id', splitter_layer_id = '', position='left', sort='0' WHERE  layer_id = '$id' LIMIT 1") or die(mysqli_error($GLOBALS['mysqli']));
                $message = "Das gewünschte Feld wurde geklont und in \"$name\" verschoben!";
                set_update_site($_SESSION['site_id']);
                set_update_site($move_site_id);
            }
        } else
            set_update_site($_SESSION['site_id']);
        break;
    // VERSCHIEBT FELD
    case 'move':
        if ($move_site_id) {

            // Name auslesen
            $name = mysql_singleoutput("SELECT title FROM smart_langSite WHERE fk_id = $move_site_id ");

            // Macht Platz für das Feld welches verschoben wird
            $GLOBALS['mysqli']->query("UPDATE smart_layer SET sort=sort+1 WHERE site_id = '$move_site_id' ");
            // postioniert das verschoben Feld auf die Seite ganz oben links(kann nachträglich auf die gewünschte Position gebracht werden
            $GLOBALS['mysqli']->query("UPDATE smart_layer SET site_id = '$move_site_id',splitter_layer_id = '', position='left', sort='0' WHERE  layer_id = '$id' LIMIT 1") or die(mysqli_error($GLOBALS['mysqli']));
            $message = "Das gewünschte Feld wurde in \"$name\" verschoben!";
            set_update_site($_SESSION['site_id']);
            set_update_site($move_site_id);
        }
        break;
    // ARCHIVIERT FELD
    // VERSTECKT FELD (im public Bereich)
    case 'hidden':
        $GLOBALS['mysqli']->query("UPDATE smart_layer SET hidden='$hidden' WHERE layer_id = '$id' ") or die(mysqli_error($GLOBALS['mysqli']));
        if ($hidden) {
            $message = 'Das gewählte Element wird öffentlich nicht mehr angezeigt!';
            echo "$('#icon_hidden$id').addClass('hide').removeClass('unhide');";
            echo "$('#text_hidden$id').attr('data-tooltip','Element ist öffentlich verborgen');";
            echo "$('#icon2_hidden$id').css('visibility','');";
        } else {
            $message = 'Das gewählte Element wird öffentlich angezeigt!';
            echo "$('#icon_hidden$id').addClass('unhide').removeClass('hide');";
            echo "$('#text_hidden$id').attr('data-tooltip','Element ist öffentlich sichtbar');";
            echo "$('#icon2_hidden$id').css('visibility','hidden');";
        }
        set_update_site($_SESSION['site_id']);
        break;

    // LÖSCHT FELD
    case 'delete':
        require ('../../config.inc.php');
        include ('../inc/function_del.inc.php');

        // Abrufen der Struktur von Splittern falls diese gelöscht werden soll
        $array_layer_id = call_splitter_sturcture($id);
        // Löscht andere Layer sofern vorhanden sind
        if (is_array($array_layer_id)) {
            foreach ($array_layer_id as $key => $layer_id) {
                $abfrage = del_layer($layer_id);
                for ($i = 0; $i < count($abfrage); $i ++) {
                    $GLOBALS['mysqli']->query($abfrage[$i]) or die(mysqli_error($GLOBALS['mysqli']));
                }
            }
        }

        // Löscht die eigentlich Layer
        $abfrage = del_layer($id);

        // Löschvorgang wird durch geführt
        for ($i = 0; $i < count($abfrage); $i ++) {
            $GLOBALS['mysqli']->query($abfrage[$i]) or die(mysqli_error($GLOBALS['mysqli']));
        }

        // Seetzt Seite zum neuladen

        $message = 'Das gewünschte Feld wurde gelöscht!';
        set_update_site($_SESSION['site_id']);

    case 'archive':

        if ($matchcode) {
            $GLOBALS['mysqli']->query("UPDATE smart_layer SET matchcode = '$matchcode', archive = '1' WHERE layer_id = '$id' LIMIT 1") or die(mysqli_error($GLOBALS['mysqli']));
            $message = 'Das gewünschte Feld wurde archviert!';
            set_update_site($_SESSION['site_id']);
        }
        break;
}

if ($message) {
    set_update_site();
    if ($action != 'hidden' && $action != 'clone') {
        echo "$('#sort_$id').remove();";
        echo "$('#$id').remove();";
    }

    echo "$('#modal_small').modal('hide');";
    echo "
    $('body').toast({ message: '$message', class : 'info' });";
} else {
    echo "alert('Fehler beim ausführen')";
}
