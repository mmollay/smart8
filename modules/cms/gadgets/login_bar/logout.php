<?php

// Session starten oder wiederherstellen
session_start();

unset($_SESSION['userbar_id']);

echo "
$('body').toast({
    title: 'Abmelden',
    message: 'Bis zum nächsten mal! :)',
    showProgress: 'bottom',
    class: 'success',
    displayTime: 2000,
    onHidden: function(){
        setTimeout(function(){
            location.reload();
            $('body').append('<div class=\"ui active dimmer\" id=\"page-loader\"><div class=\"ui massive loader\"></div></div>');
        }, 2000);
    }
});
";

exit;
//location.reload();


// Alle relevanten Session-Variablen löschen
// unset($_SESSION['user']);
// unset($_SESSION['password']);
// unset($_SESSION['user_id']);
// unset($_SESSION['login_user_id']);
// unset($_SESSION['fbid']);

// unset($_SESSION['verify_key']);
// unset($_SESSION['user_id']);

// if (isset($_SERVER['HTTP_COOKIE'])) {
//     $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
//     foreach ($cookies as $cookie) {
//         $parts = explode('=', $cookie);
//         $name = trim($parts[0]);
//         setcookie($name, '', time() - 1000);
//         setcookie($name, '', time() - 1000, '/', $_SERVER['HTTP_HOST']);
//     }
// }

// // Falls erforderlich, die gesamte Session löschen
// session_destroy();

// // Alle relevanten Cookies löschen
// $cookie_names = ["user","password","fbid","user_id"];
// foreach ($cookie_names as $cookie_name) {
//     setcookie($cookie_name, "", time() - 3600, '/', $_SERVER['HTTP_HOST']);
// }

// echo "
// alert('Sie werden ausgeloggt.');
// location.reload();
// ";