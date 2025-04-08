<?
/****
 * Übergibt den verify_key nicht user_id in den Cookie
 ***/
include (__DIR__ . '/../../config.php');

$_POST['password_md5'] = md5($_POST['password']);

// now validating the username and password
$sql = "SELECT verify_key,user_id FROM ssi_company.user2company WHERE user_name='{$_POST['user']}' and ( password='{$_POST['password_md5']}' or password = '{$_POST['password']}' )";

// now validating the username and password
$result = $GLOBALS['mysqli']->query($sql);

// if username exists
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
    $_SESSION['verify_key'] = $row['verify_key'];
    $_SESSION['userbar_id'] = $row['user_id'];

    setcookie("verify_key", $_SESSION['verify_key'], time() + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST']);
    echo "$('#form_message').html(\"<div class='ui positive tiny message'><i class='close icon'></i><div id='form_message_info' class='header'>Anmeldung ist erfolgreich! <i class='notched circle loading icon'></i></div>\");";
    if ($_GET['lp'] == 'center')
        echo "$(location).attr('href','../../../');"; // lp = landing_page
    else
        echo "window.top.location.reload();";
} else {
    $_SESSION['verify_key'] = '';
    $_SESSION['userbar_id'] = '';
    setcookie("verify_key", "", time() - 3600, '/', $_SERVER['HTTP_HOST']);
    echo "$('#form_message').html(\"<div class='ui negative tiny message'><i class='close icon'></i><div id='form_message_info' class='header'>User oder Passwort sind falsch</div>\");";
}