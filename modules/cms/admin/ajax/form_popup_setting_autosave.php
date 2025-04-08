<?php
// Datenbankverbindung herstellen
include_once (__DIR__ . '/../../../login/config_main.inc.php');

$site_id = $_POST['site_id'];
$id = $GLOBALS['mysqli']->real_escape_string($_POST['id']);
// $value = $_POST['value'];

if ($_POST['value'])
    $value = $GLOBALS['mysqli']->real_escape_string($_POST['value']);

$array_option_fields = array(
    $id => "$value"
);

save_smart_option($array_option_fields, $_SESSION['smart_page_id'], $site_id);
set_update_site($site_id);

echo "ok";