<?php
session_start();

// $user_id = $_SESSION['user_id'];
$userbar_id = $_SESSION['userbar_id'];

if ($_POST['color'])
    $color = $_POST['color'];
if ($_POST['button_text'])
    $button_text = $_POST['button_text'];
else
    $button_text = 'Anmelden';
if ($_POST['icon'])
    $icon = $_POST['icon'];
if ($_POST['verify_key'])
    $verify_key = $_POST['verify_key'];

$output = '';

include ('../config.php');
// $user_id = '';
if ($userbar_id) {
    $query = $GLOBALS['mysqli']->query("SELECT * FROM ssi_company.user2company comp  WHERE user_id = '$userbar_id'") or die(mysqli_error($GLOBALS['mysqli']));
    $array = mysqli_fetch_array($query);
    $user = $array['user_name'];
    $fbid = $array['fbid'];
    $verified = $array['verified'];

    if ($verified or $fbid) {
        $sign = "<i class='icon large green check circle tooltip' title='User ist verifiziert'></i>";
    } else {
        $sign = "<i class='icon large warning red sign tooltip' title='User noch nicht verifiziert'></i>";
    }

    $output .= "$sign";

    // Falls der User wärend der Nutzung entfernt wird, wird Session zerstört
    // if (!$user) session_destroy();
    // $fbid = '10153003379043398';

    $name = $array['firstname'] . " " . $array['secondname'];
    if (! $name)
        $name = $user;
    if ($fbid)
        $img = "<a class='ui spaced image' style='height:40px; width:36px; position:relativ; left:9px;'><img class='ui medium rounded image' style='height:36px; width:36px' src='//graph.facebook.com/$fbid/picture'></a>";

    $output .= $fb_button ?? '';
    $output .= $img ?? ''."<button title='Benutzerprofil' class='ui icon $color button icon tooltip' id=userbar_button_user onclick=bazar_call_form('login_bar/form_edit') >$name</button>";
    $output .= "<button onclick='logout()' title='Abmelden' class='ui icon button tooltip $color' id='userbar_button_logout'><i class='sign out icon'></i></button>";
} else {

    if (! $icon)
        $login_icon = "<i class='sign out icon'></i>";
    else
        $login_icon = "<i class='$icon icon'></i>";

    $output .= $fb_button;
    $output .= "<a class='ui $color icon button' onclick=bazar_call_form('login/index') id='userbar_button_login' >$login_icon $button_text</a>";
}

echo $output;