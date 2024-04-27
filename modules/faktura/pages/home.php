<?php
include (__DIR__ . '/../f_config.php');

/*
 * Todo-eintrag
 */
$todo_entry = '<br><div align=center><form><div id=todo_border>Todo: <input type=text id=todo_text> Datum: <input type=text id=todo_date><input id=todo_button class=button type=submit value=Speichern></div></form></div>';
$todo_entry .= "<div id=window></div><div id=list></div>";

// Ausgabe der Mahnliste
include ('../inc/call_remind_list.php');

// $info_list_table .= "<br><button id=button_send_remind>Mahner erinnern</button><br>";
$setContent = '';
$setContent .= "<div align=center>";
$setContent .= "<div style='width:800px'>";
$setContent .= "<br><br><br>";

$setContent .= "<table align='center' border=0><tr>";
$setContent .= "<td align=center width=160 valign=top><a href=# onclick=\"loadContent('faktura','list_earnings')\"><b>Einnahmen</b><br><br><img src='img/earning.png' height=85px></td>";
$setContent .= "<td align=center width=160 valign=top><a href=# onclick=\"loadContent('faktura','list_client2')\"><b>Clienten</b><br><img src='img/client.png' height=110px></a></td>";
$setContent .= "<td align=center width=160 valign=top><a href=# onclick=\"loadContent('faktura','list_article')\"><b>Artikel</b><br><br><img src='img/article.png' height=90px></td>";
if ($show_menu['finance_output'])
    $setContent .= "<td align=center width=160 valign=top><a onclick=\"loadContent('faktura','list_issues')\"><b>Ausgaben</b><br><br><img src='im/outgoings.png' height=100p></td>";
$setContent .= "</tr>";
$setContent .= "</table>";

$setContent .= $info_list_table;
$setContent .= "</div>";
$setContent .= "</div>";
// if ($show_menu['todo']) {
// 	$setContent .= "<tr><td colspan=6><br> $todo_entry";	
// 	$setContent .= "</td></tr></table>";
// 	$setContent .= "<script>var company_id = {$_SESSION['faktura_company_id']}; </script>";
// 	$setContent .= "<script type=\"text/javascript\" src=\"js/list_todo.js\"></script>";
// }
echo $setContent;
