<?php
//https://center.ssi.at/admin/ssi_service/manuel/transfer_smart_user2company_user.php
//localhost/smart7/ssi_service/manuel/transfer_smart_user2company_user.php

// Transferiert alle User in die neue Userverwaltung - Zentralverwaltung auf ssi_company
include_once ('../../login/config_main.inc.php');

// Inhalte werden gelöscht
//$GLOBALS['mysqli']->query ( "TRUNCATE TABLE ssi_company.`domain`" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );

$array_company = array ('1','2','3','8' );

foreach ( $array_company as $company_id ) {
	$count_user = '';
	$query = $GLOBALS ['mysqli']->query ( "SELECT * from ssi_smart$company_id.tbl_user" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	while ( $array = mysqli_fetch_array ( $query ) ) {
		$query_data = $GLOBALS ['mysqli']->query ( "SELECT smart_version,verified FROM ssi_company.user2company WHERE user_id = '{$array['user_id']}' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		$array_data = mysqli_fetch_array ( $query_data );
		$smart_version = $array_data ['smart_version'];
		$verified = $array_data ['verified'];
		//$smart_version = '';
		//echo $array['user_id'].",";
		if ($array ['sex'])
			$array ['gender'] = $array ['sex'];

		$verify_key_new = md5 ( uniqid ( rand (), TRUE ) );
		//$verify_key_new = $array['verify_key'];

		// Entrag in die globale Userverwaltung
		$GLOBALS ['mysqli']->query ( "
		REPLACE ssi_company.user2company SET
			user_id = '{$array['user_id']}',
			company_id = '$company_id',
			parent_id = '{$array['parent_id']}',
			reg_date = '{$array['reg_date']}',
			user_name = '{$array['user_name']}',  
			verified  = '$verified',
			smart_version  = '$smart_version',
			`login_count` =  '{$array['login_count']}',
			`confirm_agb` =  '{$array['confirm_agb']}',
			`verify_key` = '$verify_key_new ',
			`country` =  '{$array['country']}',
			`firstname` ='{$array['firstname']}',			
			`secondname` ='{$array['secondname']}', 
			`zip`='{$array['zip']}',
			`city`='{$array['city']}',
			`street`='{$array['street']}',
			`telefon`='{$array['telefon']}',
			`birthday`='{$array['birthday']}',
			`gender`='{$array['gender']}',
			`company1`='{$array['company1']}',
			`company2`='{$array['company2']}',
			`fbid`='{$array['fbid']}',
			`password`='{$array['password']}',
			`number_of_smartpage`='{$array['number_of_smartpage']}',
			`right_id`='{$array['right_id']}',
			`user_checked`='{$array['user_checked']}'
			" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		$count_user ++;
	}
	echo "Summe User: $count_user (ID: $company_id)<br>";
}

//$GLOBALS ['mysqli']->query ( "DROP TABLE `explorer`, `feedback_fields`, `gadget_feedback`, `gadget_gallery`, `gadget_guestbook`, `gadget_shop`, `gcm_logfile`, `gcm_users`, `id_layer2id_page`, `id_layer2id_seite`, `id_seite2id_page`, `menu`, `tbl_domain`, `tbl_layer`, `tbl_menu`, `tbl_page`, `tbl_profil`, `tbl_profil_menu`, `tbl_seite`, `tbl_user`, `tbl_useralias`, `tbl_user_paneon`;" );
//echo "alte Datenbank wurde gelöscht";
