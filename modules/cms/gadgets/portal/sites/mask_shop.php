<?php
session_start ();

$_SESSION ['group_id'] = $_SESSION ['group_default_id'];

include ('../config.inc.php');
include ('../cart/call_cart.php');
include ('../cart/call_main.php'); // $content
echo "<link rel=stylesheet type='text/css' href='$relative_path" . "cart/shop.css'>";
echo $content;