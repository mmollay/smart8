<?php
session_start ();
$_SESSION['list_filter'] = $_POST['list_filter'];
$_SESSION['filter_table'] = $_POST['table'];
$_SESSION['filter_section'] = $_POST['filter_section'];
$_SESSION['filter_membership'] = $_POST['filter_membership'];
?>