<?
session_start ();
require ("../config.inc.php");

$GLOBALS['mysqli']->query ( "INSERT INTO todo SET
company_id = {$_SESSION['faktura_company_id']},
text = '{$_POST[todo_text]}',
todo_date = '{$_POST['todo_date']}'
" ) or die ( mysqli_error ($GLOBALS['mysqli']) );

echo "Todo-Eintrag wurde gespeichert";
?>