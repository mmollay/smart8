<?
session_start();
include (__DIR__ . '/../f_config.php');

$GLOBALS['mysqli']->query("INSERT INTO todo SET
company_id = {$_SESSION['faktura_company_id']},
text = '{$_POST[todo_text]}',
todo_date = '{$_POST['todo_date']}'
") or die(mysqli_error($GLOBALS['mysqli']));

echo "Todo-Eintrag wurde gespeichert";
?>