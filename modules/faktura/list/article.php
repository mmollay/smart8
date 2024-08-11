<?php
$query = $GLOBALS['mysqli']->query("SELECT account_id,accounts.title as konto  FROM accounts WHERE company_id = '{$_SESSION['faktura_company_id']}' AND `option` = 'in' ") or die(mysqli_error($GLOBALS['mysqli']));
while ($array = mysqli_fetch_array($query)) {
	$account_array[$array['account_id']] = $array['konto'];
}

$arr['mysql'] = array(
	'field' => "temp_id, art_nr,count, accounts.title as konto,art_title,netto,format,pdf,
		if (!internet_show,'<i class=\"icon disabled large unhide\"></i>','<i class=\"large green icon unhide\"></i>') internet_show,
		if (!free,'<i class=\"icon disabled large uncheck\"></i>','<i class=\"large green icon check\"></i>') free,
		if (pdf='','','<i class=\"icon red large file pdf outline\"></i>') pdf,
		timestamp",
	'table' => "article_temp LEFT JOIN accounts ON account = account_id LEFT JOIN article2group ON article_temp.temp_id = article2group.article_id",
	'order' => 'art_nr desc ',
	'limit' => 25,
	'group' => 'temp_id',
	//'where' => "AND article_temp.company_id = '{$_SESSION['faktura_company_id']}' " , 
	'like' => 'art_nr,  accounts.title, netto, art_title, internet_title,internet_text',
	//'debug' => true
);

$arr['list'] = array('id' => 'article_admin_list', 'width' => '1200px', 'align' => '', 'size' => 'small', 'class' => 'compact selectable celled striped definition'); // definition

$arr['order'] = array('default' => 'art_nr desc', 'array' => array('art_nr desc' => 'Artikelnummer', 'timestamp desc' => 'Nach Erstelldatum', 'art_nr desc' => 'Artikelnummer', 'netto desc' => 'Betrag'));

$arr['filter']['group_id'] = array('type' => 'select', 'array' => $group_array, 'placeholder' => '--Gruppen--', 'table' => 'article2group');
$arr['filter']['account_id'] = array('type' => 'dropdown', 'array' => $account_array, 'placeholder' => '--Konten--', 'table' => 'accounts');
$arr['filter']['pdf'] = array('type' => 'dropdown', 'query' => "{value}", 'array' => array('pdf>" "' => 'PDF vorhanden', 'pdf=""' => 'PDF nicht vorhanden'), 'placeholder' => '--PDF--');
$arr['filter']['internet_show'] = array('type' => 'dropdown', 'array' => array('1' => 'Sichtbar', '0' => 'Nicht sichtbar'), 'placeholder' => '--Internet Produkte--', 'table' => 'article_temp');

$arr['th']['art_nr'] = array('title' => "ArtNr.");
$arr['th']['art_title'] = array('title' => "Titel");
$arr['th']['netto'] = array('title' => "Netto", 'align' => 'right', 'format' => 'euro');
$arr['th']['format'] = array('title' => "Format");
$arr['th']['konto'] = array('title' => "Konto");
$arr['th']['timestamp'] = array('title' => "Update");
$arr['th']['pdf'] = array('title' => "PDF", 'align' => 'center');
$arr['th']['free'] = array('title' => "Frei", 'align' => 'center');
$arr['th']['internet_show'] = array('title' => "Internet", 'align' => 'center');

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '', 'icon' => 'edit', 'class' => 'blue mini', 'popup' => 'Bearbeiten');

$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash', 'class' => 'mini', 'popup' => 'Löschen');

$arr['modal']['modal_form'] = array('title' => 'Artikel bearbeiten', 'class' => '', 'url' => 'form_edit.php');
$arr['modal']['modal_form_delete'] = array('title' => 'Artikel entfernen', 'class' => 'small', 'url' => 'form_delete.php');

$arr['top']['button']['modal_form'] = array('title' => 'Artikel anlegen', 'icon' => 'plus', 'class' => 'blue circular', 'popup' => 'Neuen Artikel anlegen');