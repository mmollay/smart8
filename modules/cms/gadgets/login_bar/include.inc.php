<?php
session_start ();

include_once('function.php');

$verify_key = get_verify_form_user2 ();

if (! $color)
	$color = $_SESSION['loginbar_color'];

$output = "<span class='userbar'></span>";
$output .= "<div class='ui modal login'><i class='close icon'></i><div class='content' id=modal_login></div></div>";
//$output .= "$facebook_login_div";


$add_path_js .= "\n<script type='text/javascript' src='gadgets/login_bar/login_bar.js'></script>";

$add_path_js .=
"
<script type='text/javascript' >
    user_bar(" . json_encode($verify_key) . ", " . json_encode($color) . ", " . json_encode($button_text) . ", " . json_encode($icon) . ");
</script>
";