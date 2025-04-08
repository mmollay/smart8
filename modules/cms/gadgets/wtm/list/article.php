<?php
session_start ();
$group_id = $_SESSION['group_id'];
$company_id = 31;

if ($group_id == '12' or $group_id == '15') {
	if ($_SESSION['filter_year'])
		$add_filter .= "AND art_nr LIKE '{$_SESSION['filter_year']}%'";
}
else $add_filter = '';

if ($group_id) {
	
	/*
	 * Version 2 Call articles inner group
	 */
	$arr['mysql']['table'] = "(article_group,article_temp) INNER JOIN (article2group)
	ON article_group.group_id = article2group.group_id
	AND article2group.article_id = article_temp.temp_id";
	
	$arr['mysql']['field'] = "temp_id,
	art_nr art_nr2, pdf,art_title,free,
	article_temp.internet_title internet_title,
	article_temp.internet_text internet_text,
	(SELECT detail_id from bills INNER JOIN bill_details ON bills.bill_id = bill_details.bill_id WHERE art_nr = art_nr2 AND client_id = '{$_SESSION['client_user_id']}' LIMIT 1) as detail_id
	";
	
	$arr['mysql']['where'] = "AND article_group.company_id = '$company_id' AND article_temp.internet_show=1 AND article2group.group_id = '$group_id' $add_filter";
}

//$arr['mysql']['like'] = 'article_temp.internet_title,article_temp.internet_text';
$arr['mysql']['match'] = 'article_temp.internet_text,article_temp.internet_title';
$arr['mysql']['limit'] = '20';
//$arr['mysql']['debug'] = true;
$arr['mysql']['group'] = 'temp_id';
$arr['mysql']['order'] = 'art_nr desc';
$arr['mysql']['charset'] = 'utf8';

date_default_timezone_set ( 'Europe/Berlin' );
for($year = date ( Y ); $year >= 2003; $year --) {
	$array_year[$year] = $year;
}

// $arr['filter']['year'] = array ( 'query' => "art_nr LIKE '{value}%'", 'type' => 'select', 'array' => $array_year, 'placeholder' => '--Jahr wählen--', 'class'=>'' );

$arr['list'] = array ( 'id' => 'article_list' , 'align' => '' , 'size' => '' , 'class' => 'ui unstackable very basic' ); // definition

$arr['th']['internet_title'] = array ( 'title' =>"" );
//$arr['th']['pdf'] = array ( 'title' =>"pdf" );

$arr['tr']['buttons']['left'] = array ( 'class' => 'tiny' );
$arr['tr']['button']['left']['detail'] = array (popup=>'Abstract ansehen', 'title' =>'Abstract', 'class' => 'tiny');

if ($_SESSION['client_user_id']) {
	$arr['tr']['button']['left']['pdf'] = array (popup=>'PDF herunterladen', href=>'{pdf}', download=>'{art_title}.pdf', 'icon' => 'file pdf outline', 'class' => 'tiny red', 'filter' => array(['field' => 'pdf', 'value' => true ,'operator' => '==' ]) , single=>true  );
}
else {
	$arr['tr']['button']['left']['pdf'] = array (popup=>'PDF herunterladen', href=>'{pdf}', download=>'{art_title}.pdf', 'icon' => 'file pdf outline', 'class' => 'tiny red', 'filter' => array(['field' => 'pdf', 'value' => true ,'operator' => '==' ],[link=>'and','field' => 'free', 'value' => true ,'operator' => '==' ]) , single=>true  );
	$arr['tr']['button']['left']['pdf2'] = array (popup=>'Dieser Artikel ist nur eingeloggt verfügbar', 'icon' => 'file pdf outline', 'class' => 'disabled tiny red', 'filter' => array(['field' => 'pdf', 'value' => true ,'operator' => '==' ],[link=>'and','field' => 'free', 'value' => false ,'operator' => '==' ]) , single=>true  );
	//$arr['tr']['button']['left']['pdf2'] = array (popup=>'Dieser Artikel ist nur eingeloggt verfügbar', 'icon' => 'file pdf outline', 'class' => 'disabled tiny red', 'filter' => array(['field' => 'free', 'value' => true ,'operator' => '!=' ]) , single=>true  );
}

$arr['modal']['detail'] = array ( 'title' =>'Details' , 'url' => 'details.php' , 'class' => 'large' );
