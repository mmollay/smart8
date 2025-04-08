<?
// Auslesen der Optionen aus der Datenbank
require ("../../../login/config_main.inc.php");

$template_id = $_POST ['template_id'];

// Auslesen
$sql = "SELECT * FROM smart_templates WHERE template_id = '$template_id'";
$query = $GLOBALS['mysqli']->query ( $sql ) or die ( mysqli_error ( $sql ) );
$array = mysqli_fetch_array ( $query );

echo json_encode ( $array );
?>