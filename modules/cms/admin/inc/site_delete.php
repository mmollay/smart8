<?php
include_once ('../../../login/config_main.inc.php');
require ('../../config.inc.php');
include ('../inc/function_del.inc.php');

if ($_POST['site_id']) {
	$abfrage = del_site ( $_POST['site_id'] );
	for($i = 0; $i < count ( $abfrage ); $i ++) {		$GLOBALS['mysqli']->query ( $abfrage[$i] ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	}
}
?>