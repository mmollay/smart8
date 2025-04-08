<?php
/*
 * ruft Daten der jeweilige Firma ab
 */
include ('../../portal2/mysql_faktura.inc.php');

$client_id = $GLOBALS['mysqli']->real_escape_string ( $_POST ['client_id'] );

$query = $GLOBALS['mysqli']->query ( "Select * from client WHERE client_id = $client_id" );
$array = mysqli_fetch_array ( $query );

echo json_encode ( $array );
