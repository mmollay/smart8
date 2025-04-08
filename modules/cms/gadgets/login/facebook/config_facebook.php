<?php
// $query = $GLOBALS['mysqli']->query ( "SELECT * FROM $db_smart.register" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
// $array = mysqli_fetch_array ( $query );
// $register_allowed = $array['register_allowed'];
// $facebook_login = $array['facebook_login'];
if (! $option_array['facebook_login'])
    return;

// TEST
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    // $appId = '439407779546981';
    // $appSecret = '97ca3026c85e31d0d6d56e9942eb2209';
    $appId = '1568917546580920';
    $appSecret = '12a8ef0e187f7cdccd1bbc9fe693603b';
    // SSI
} elseif ($_SERVER['HTTP_HOST'] == 'center.ssi.at') {
    $appId = '439014386252987';
    $appSecret = 'ed893a050bd3610f19bc1e5d9b7d86a7';
    // INBS
} elseif ($_SESSION['company'] == 'center.inbs.at') {
    $appId = '774752799275480';
    $appSecret = '25718da0cfb29ad6ca19b32d0142e772';
} 
else {
    $host = $_SERVER['HTTP_HOST'];
    $host = preg_replace('[www.]', '', $host);
    $query = $GLOBALS['mysqli']->query("SELECT appSecret, appID FROM $db_smart.smart_page WHERE smart_domain = '$host' ");
    $array = mysqli_fetch_array($query);
    $appId = $array['appID'];
    $appSecret = $array['appSecret'];
}

$fbPermissions = 'public_profile, email'; // ,user_friends,user_likes // more permissions : https://developers.facebook.com/docs/authentication/permissions/

$button_facebook_login = "
		<a rel='nofollow' onClick='javascript:CallAfterLogin();return false;' class='button facebook ui' ><i class='facebook icon'></i>Mit Facebook anmelden</a>
		<div id = results></div>
		";

if (isset($add_link)) {
    $facebook_login_div = "
    <script type='text/javascript'>
    var appId = '$appId';
    var scope = '$fbPermissions';
    var lp = '{$_GET['lp']}';
    var channelUrl = 'facebook/channel.php';
    </script>
    <script src='$add_link" . "facebook/facebook.js' type='text/javascript'></script>";
}
?>