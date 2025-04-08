<?php
session_start ();
unset ( $_SESSION ['client_username'] );
unset ( $_SESSION ['client_password'] );
unset ( $_SESSION ['client_user_id'] );
unset ( $_SESSION ['oegt_user'] );

// Destroy Cookies
setcookie ( "client_username", "", time () - 3600 );
setcookie ( "client_password", "", time () - 3600 );
setcookie ( "client_user_id", "", time () - 3600 );
setcookie ( "oegt_user", "", time () - 3600 );

include ('../sites/portal.php');
?>