<?php
session_start ();

if ($_GET ['share_id']) {
	$uri_parts = explode ( '?', $_SESSION ['page_link'] ['report_list'], 2 );
	$request_uri = $uri_parts [0];
	$input = "$request_uri?id={$_GET ['share_id']}";
} else
	$input = $_SESSION ['page_link'] ['report_list'];

echo "Link herauskopieren:<br>";
echo "<div class='ui fluid icon input focus'><input value='$input' onFocus='this.select()'></div>";
echo "<br><div class='ui label'>ctrl + c</div> =  Copy";