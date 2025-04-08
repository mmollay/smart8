<?php
session_start ();

$_SESSION['list_search'] = $_POST['list_search'];

if ($_POST['select_action'])
	$_SESSION['select_action'] = $_POST['select_action'];

$_SESSION['show_all'] = $_POST['show_all'];
?>