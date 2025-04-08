<?php
if ($_SESSION['smart_page_id']) {

    // Sollte die Webseite speziell eingstellt sein wird die Rechtevergabe verwendet
    $query = $GLOBALS['mysqli']->query("SELECT * FROM smart_page where page_id = '{$_SESSION['smart_page_id']}' AND user_id = '{$_SESSION['user_id']}'  ") or die(mysqli_error($GLOBALS['mysqli']));
    $array = mysqli_fetch_array($query);

    // Wenn User versucht über Page_id eines anderen einzusteigen wird diese verhindert
    if (! mysqli_num_rows($query)) {
        // $abortpage['message'] = "Dieser Zugang hat keine Rechte diese Seite zu bearbeiten<br> Bitte wenden Sie sich an den Administratior!<br><a href = 'https://www.ssi.at'>www.ssi.at</a>";
        // $abortpage['class'] = "message error";
        // include (__DIR__ . '/../../pages/error.php');
        include (__DIR__ . '/../../login/logout.php');
        exit();
    }
}

if (! $array['right_id']) {
    // Defaultmäßig wird die Rechte-defintion vom User geholt
    $query = $GLOBALS['mysqli']->query("SELECT right_id FROM ssi_company.user2company where user_id = '{$_SESSION['user_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
    $array = mysqli_fetch_array($query);
}

// Rechte der einzelnen Elementen ermitteln
if ($array['right_id']) {

    $query_right = $GLOBALS['mysqli']->query("SELECT * FROM smart_user_right WHERE right_id = '{$array['right_id']}' ");
    $array_right = mysqli_fetch_array($query_right);
    if (! $array_right['max_number_sites'])
        $array_right['max_number_sites'] = 5;

    $right_id = $array['right_id'];

    foreach ($array_right as $key => $value) {
        if ($value) {
            $GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string($value);
        }
    }
}

// Anzahl der bestehenden Seiten ermittlen
$query = $GLOBALS['mysqli']->query("SELECT * FROM smart_id_site2id_page WHERE page_id = '{$_SESSION['smart_page_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
$count_sites = mysqli_num_rows($query);

if ($count_sites >= $array_right['max_number_sites'] and $array['right_id'])
    $hide_add_site_button = true;

// Rechte fuer die Nutzung der Module
// module2user_id
// call permissions for the modules
$sql = $GLOBALS['mysqli']->query("SELECT module from module2id_user WHERE user_id = '{$_SESSION['user_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
while ($array = mysqli_fetch_array($sql)) {
    if ($array['module'])
        $set_modul[$array['module']] = true;
}

// SuperUser hat alle Rechte und kann sogar Webseiten von anderen bearbeiten
// TODO: Explorer der User ist noch nicht eingebunden - muss unbedingt berücksichtigt werden
if (in_array($_SESSION['user_id'], $_SESSION['array_superuser_id'])) {
    $right_id = '';
}