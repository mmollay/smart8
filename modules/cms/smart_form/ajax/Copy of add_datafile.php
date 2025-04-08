<?php 
session_start();
include('../fu_filelist.php');

$url = $_SESSION['upload_url'];
$content = $_POST['content'];
$name = $_POST['name']; //wird mit Ajax übergeben

echo upload_card_admin($url, $name, '')
?>