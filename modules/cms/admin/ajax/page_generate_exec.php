<?
session_start();
$page_id = $_SESSION['smart_page_id'];
//$company = $_SESSION['company'];
$user_id = $_SESSION['user_id'];
$path = "/var/www/ssi/center/ssi_smart/admin/ajax/page_generate.php?page_id=$page_id&user_id=$user_id&company=$company";

//exec("php p-f $path > /dev/null &2>/dev/null &");
shell_exec("cd /var/www/ssi/center/ssi_smart/admin/ajax/  php page_generate.php?page_id=$page_id&user_id=$user_id&company=$company");

//echo "ok";
?>