<?php
if ($_POST['ajax']) {
	include_once ('../config.inc.php');
}

if (isset ( $_POST['group_id'] )) {
	$_SESSION['group_id'] = $_POST['group_id'];
}
// else $_SESSION['group_id'] = $_SESSION['group_default_id'] = 0;

// $_SESSION['group_dafault_id'] = Speichert die eigentlich Gruppe bei erstmaligen Aufrufen

$group_id = $_SESSION['group_id'];

if ($group_id) {
	
	/*
	 * Version 2 Call articles inner group
	 */
	$sql = "
	SELECT 
	temp_id, 
	art_nr art_nr2, 
	art_title,
	art_text,
	article_temp.internet_title internet_title,
	article_temp.internet_text internet_text, 
	netto,
	article_group.company_id company_id,
	article_group.title group_title,
	article_group.group_id group_id,
	article_temp.gallery gallery, 
	article_group.gallery group_gallery,
	(SELECT detail_id from bills INNER JOIN bill_details ON bills.bill_id = bill_details.bill_id WHERE art_nr = art_nr2 AND client_id = '{$_SESSION['client_user_id']}' LIMIT 1) as detail_id	
	FROM (article_group,article_temp) INNER JOIN (article2group) 
			ON article_group.group_id = article2group.group_id 
			AND article2group.article_id = article_temp.temp_id
				WHERE article_group.company_id = '$company_id' 
				AND article_temp.internet_show=1 
				AND article2group.group_id = '$group_id'
				ORDER by art_nr desc
				
	";
	// internet_title
} else {
	/*
	 * Version 1 Call just articles without groups
	 */
	$sql = "
	SELECT temp_id, art_nr art_nr2, internet_title,internet_text, netto, gallery, art_title,
	(SELECT detail_id from bills INNER JOIN bill_details ON bills.bill_id = bill_details.bill_id WHERE art_nr = art_nr2 AND client_id = '{$_SESSION['client_user_id']}' LIMIT 1) as detail_id 
	FROM article_temp
	WHERE company_id = '$company_id' 
	AND internet_show=1 
	";
}

/*
 * call articles from faktura with groups
 */
$query_group = $GLOBALS['mysqli']->query ( $sql ) or die ( mysqli_error ($GLOBALS['mysqli']) );
while ( $array = mysqli_fetch_array ( $query_group ) ) {
	
	/*
	 * Reading fields from db and preparing
	 */
	$group_id = $array['group_id'];
	$article_id = $array['temp_id'];
	$gallery = $array['gallery'];
	$group_gallery = $array['group_gallery'];
	$detail_id = $array['detail_id'];
	$group_title = $array['group_title'];
	$art_nr = $array['art_nr'];
	$netto = $array['netto'];
	//$company_id = $array['company_id'];
	
	$text = $array['internet_text'];
	if (! $text)
		$text = $array['art_text'];
	
	$title = $array['internet_title'];
	if (! $title)
		$title = $array['art_title'];
	
	if ($set_static == true) {
		$internet_text = preg_replace ( "/\/smart_users\/user$user_id\/explorer\/$page_id\//", "/explorer/", $array['internet_text'] );
		$internet_text = preg_replace ( "/\/users\/user$user_id\/explorer\/$page_id\//", "/explorer/", $array['internet_text'] );
	}
	
	
	// Deaktiveren wenn HP-Inside nicht aktiv ist
	// if (!$_SESSION['hp_inside'])
	// $list_products .= " <button class=add_cart disabled='disabled'>$strCartButtonShow</button>";
	// else
	$list_products = "";
	$list_products .= "$title";
	$list_products .= "<br>
	<button class='button ui mini icon' onclick=show_detail($article_id,true)><i class='icon external'></i> $strCartButtonShow</button>
	<button class='button ui mini icon' onclick=\"javascript:location.href='?code=$article_id#$title' \"><i class='icon linkify'></i></button>
	";
	/*
	 * Update 02.06.2015 - Wenn show_cart nicht angezeigt wenn in "config.inc.php" $show['cart'] nicht aktiviert ist
	 * Kein Anzeigen der "Hinzufügen" bei Abokunden und bei OEGT-Mitgliedern
	 */
	
	if ($show['cart']) {
		if (! $_SESSION['oegt_user'] and ! $_SESSION['abo']) {
			
			if (! $_SESSION['client_user_id']) {
				$list_products .= " <button class='button ui mini icon add_cart_first_login_msg' onclick=add_cart_first_login_msg($article_id) ><i class='icon shop'></i> $strCartButtonOrder</button>";
			} else if (! $_SESSION['hp_inside']) { // User Can't order if is not confirmed
				$list_products .= " <button class='button ui mini add_cart' disabled='disabled'><i class='icon shop'></i> $strCartButtonOrder</button>";
			} else if ($detail_id) // Allready Ordered
				$list_products .= " <button class='button ui mini add_cart' disabled='disabled'><i class='icon shop'></i> $strCartButtonOrderAlready</button>";
				// Usual Button
			else
				$list_products .= " <button class='button ui mini add_cart' onclick=add_article($article_id)><i class='icon shop'></i> $strCartButtonOrder</button>";
		}
	}
	
	$show_list_products .= "<tr><td>".$list_products."</td></tr>";
	//$list_products .= "</div>";
}

$list_products = "<table class='ui celled table unstackable very basic striped'>$show_list_products</table>";

// echo "<pre>";print_r($array_article);echo "</pre>";

if (! $group_id and $content_groups) {
	$products = '<div align=Center>Bitte Gruppe wählen</div>';
	if ($_POST['ajax']) {
		echo $products;
	}
	return;
} else if (! $show_list_products) {
	$products = '<div align=Center>Keine Artikel vorhanden</div>';
	if ($_POST['ajax']) {
		echo $products;
	}
	return;
}

//if ($group_id) $products = "<div class='ui header big'>$group_title</div>";

$products .= $list_products;

if ($_POST['ajax'] and $set_ajax != 'true') {
	echo $products;
}
?>