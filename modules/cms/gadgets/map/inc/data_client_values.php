<?php
/*
 * ruft Daten der jeweilige Firma ab
 */
include ('../mysql_map.inc.php');

$client_id = $GLOBALS['mysqli']->real_escape_string ( $_POST ['client_id'] );

$query = $GLOBALS['mysqli']->query ( "Select * from tree_client WHERE client_id = $client_id" );
$array = mysqli_fetch_array ( $query );

echo json_encode ( $array );
