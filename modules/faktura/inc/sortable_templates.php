<?php 
session_start();
$data = explode(',',$_POST['data']);

setcookie ( "array_template_import_faktura", $_POST['data'], time () + 60 * 60 * 24 * 365, '/', $_SERVER ['HTTP_HOST'] );

?>