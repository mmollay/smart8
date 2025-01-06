<?
// Zugangsdaten fuer die Datenbank
include (__DIR__ . '/../f_config.php');

foreach ($_POST as $key => $value) {
	if ($value) {
		$GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string($value);
	}
}

if ($faktura_company_id)
	$_SESSION['faktura_company_id'] = $faktura_company_id;

switch ($_POST['list_id']) {

	case 'elba_list':

		$word = str_replace("\r", '', $_POST['word']);

		$GLOBALS['mysqli']->query("UPDATE automator SET
            word = '$word'
			where automator_id  = '$automator_id'
			") or die(mysqli_error($GLOBALS['mysqli']));

		// data_elba durchsuchen und mit automator_id verknüpfen
		$word_array = preg_split("/\\n/", $word);
		foreach ($word_array as $word) {
			if ($word) {
				$word = trim($word);
				if ($add_mysql) {
					$add_mysql .= ' OR ';
				}
				$add_mysql .= " text LIKE '%$word%' ";
			}
		}
		// SET connector_id for elba
		$sql_update = "UPDATE data_elba SET automator_id='$automator_id' WHERE ($add_mysql)";
		$GLOBALS['mysqli']->query($sql_update) or die(mysqli_error($GLOBALS['mysqli']));

		echo "ok";
		break;
	case 'automator_list':

		$GLOBALS['mysqli']->query("REPLACE INTO automator SET
			automator_id  = '$update_id',
            word = '$word',
            description = '$description',
            client_id   = '$client_id',
            comment = '$comment',
    		account_id     = '$account'
			") or die(mysqli_error($GLOBALS['mysqli']));
		echo 'ok';
		break;

	case 'bill_list':
	case 'offered_list':
		$_POST['discount'] = nr_format2english($_POST['discount']);
		$minutes_to_add = 5;

		// display the converted time
		$date_create = date('Y-m-d H:i:s', strtotime('+' . date('H') . 'hour +' . date('i') . 'minutes +' . date('s') . ' seconds', strtotime($date_create)));
		$date_booking = $date_booking ?? '0000-00-00';
		$date_storno = $date_storno ?? '0000-00-00';
		$date_send = $date_send ?? '0000-00-00';
		$no_mwst = $no_mwst ?? 0;
		$booking_total = $booking_total ?? 0;
		$post = $post ?? 0;
		$no_endsummery = $no_endsummery ?? 0;
		$bill_id = $bill_id ?? 0;
		$discount = $discount ?? 0;
		$sendet = $sendet ?? 0;

		$sql = "REPLACE INTO bills SET
		bill_id     = $bill_id,
		bill_number = '$bill_number',
		company_id  = '{$_SESSION['faktura_company_id']}',
		client_id   = '$client_id',
		client_number= '$client_number',
		document = '$document',
		company_1   = '$company_1',
		company_2   = '$company_2',
		title       = '$title',
		gender      = '$gender',
		firstname   = '$firstname',
		secondname  = '$secondname',
		street      = '$street',
		zip         = '$zip',
		city        = '$city',
		country     = '$country',
		date_create = '$date_create',
		date_booking = '$date_booking',
		date_storno = '$date_storno',
		date_send  = '$date_send',
		tel         = '$tel',
		email       = '$email',
		web         = '$web',
		uid         = '$uid',
		commend     = '$commend',
		description = '$description',
		text_after  = '$text_after',
		discount    = '$discount',
		no_mwst     = $no_mwst,
		sendet      = '$sendet',
		booking_total = '$booking_total',
		booking_command = '$booking_command',
		post        = '$post',
		no_endsummery = $no_endsummery
		";

		$GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
		$bill_id = mysqli_insert_id($GLOBALS['mysqli']);

		// leoschen der Details wenn Update gemacht wurde
		if ($_POST['bill_id'])
			$GLOBALS['mysqli']->query("DELETE FROM bill_details WHERE bill_id = '{$_POST['bill_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));

		if ($_SESSION['temp_cart']) {
			foreach ($_SESSION['temp_cart'] as $key => $value) {
				$iii++;
				$temp_id = $_SESSION['temp_cart'][$key]['temp_id'];
				$nr = $_SESSION['temp_cart'][$key]['art_nr'];
				$title = $_SESSION['temp_cart'][$key]['art_title'];
				$text = $_SESSION['temp_cart'][$key]['art_text'];
				$count = $_SESSION['temp_cart'][$key]['count'];
				$account = $_SESSION['temp_cart'][$key]['account'];
				$netto = $_SESSION['temp_cart'][$key]['netto'];
				$format = $_SESSION['temp_cart'][$key]['format'];
				$rabatt = $_SESSION['temp_cart'][$key]['rabatt'] ?? '0';
				$sum = $netto * $count;

				// Eintrage der Detail-Infos in die Datenbank
				$GLOBALS['mysqli']->query("REPLACE INTO bill_details SET
			bill_id   = '$bill_id',
			temp_id    = '$temp_id',
			art_nr    = '$nr',
			art_title = '$title',
			art_text  = '$text',
			count     = '$count',
			account   = '$account',
			tax       = (SELECT tax from accounts WHERE account_id='$account'),
			netto     = '$netto',
			rabatt    = '$rabatt',
			format    = '$format' ") or die(mysqli_error($GLOBALS['mysqli']));
			}
		}

		if ($_POST['discount']) {
			$mysql_add1 = "-(SUM(netto*count)/100*{$_POST['discount']})";
			$mysql_add2 = "-(SUM(netto*count*((tax+100)/100))/100*{$_POST['discount']})";
		}
		if ($_POST['no_mwst'])
			$sum_brutto = "(SELECT SUM(netto*count)$mysql_add1 FROM `bill_details` WHERE bill_id = $bill_id)";
		else
			$sum_brutto = "(SELECT SUM(netto*count*((tax+100)/100)) $mysql_add2  FROM `bill_details` WHERE bill_id = $bill_id)";

		// Ausrechnen von Brutto und Netto
		$GLOBALS['mysqli']->query("UPDATE bills SET
		netto =  (SELECT SUM(netto*count)$mysql_add1 FROM `bill_details` WHERE bill_id = $bill_id),
		brutto = ROUND($sum_brutto, 2)
		WHERE bill_id = $bill_id") or die(mysqli_error($GLOBALS['mysqli']));

		// Session nach Eintrag löschen
		unset($_SESSION['temp_cart']);
		// wird benötigt zum laden der richtigen Tabelle Ang oder Rn
		echo $_POST['document'];
		break;

	case 'group_list':
		// Template anlegen
		$GLOBALS['mysqli']->query("REPLACE INTO article_group SET
		group_id    = '$update_id',
		company_id = '{$_SESSION['faktura_company_id']}',
		title      = '$title',
		text       = '$text',
		parent_id  = '$parent_id',
		parent_id2  = '$parent_id2',
		internet_title = '$internet_title',
		internet_text = '$internet_text',
		internet_show = '$internet_show',
		gallery = '$gallery',
		sort    = '$sort'
		") or die(mysqli_error($GLOBALS['mysqli']));

		break;

	case 'client_list':
	case 'client_oegt_list':

		$post = $post ?? 0;
		$newsletter = $newsletter ?? 0;
		$abo = $abo ?? 0;
		$student = $student ?? 0;
		$own_practice = $own_practice ?? 0;
		$group_practice = $group_practice ?? 0;
		$employed = $employed ?? 0;
		$industry = $industry ?? 0;
		$administration = $administration ?? 0;
		$university = $university ?? 0;
		$no_exercise = $no_exercise ?? 0;
		$retirement = $retirement ?? 0;
		$other = $other ?? 0;
		$join_date = $join_date ?? '0000-00-00';
		$hp_inside = $hp_inside ?? 0;

		// Check ob Eintrag bereits vorhanden ist
		// Art_NR UND Art_Title
		$check_client_number = mysql_singleoutput("SELECT client_number FROM client WHERE client_number='$client_number' AND company_id = '$company_id' AND client_id != '{$_POST['update_id']}' ", "client_number");

		if (!$client_number) {
			echo 'empty_client_number';
		} elseif ($check_email) {
			echo 'email_exists';
		} elseif ($check_client_number) {
			echo 'double_client_number';
		} elseif ($check_company_1) {
			echo 'double_company_name';
		} else {

			if (!$birth)
				$birth = '0000-00-00';

			// Template anlegen
			$GLOBALS['mysqli']->query("REPLACE INTO client SET
            user_id       = '{$_SESSION['user_id']}',
			abo           = '$abo',
			send_date     = '$send_date',
			client_id     = '{$_POST['update_id']}',
			client_number = '$client_number',
			company_id = '$company_id',
			company_1 = '$company_1',
			company_2 = '$company_2',
			title = '$title',
			gender = '$gender',
			firstname = '$firstname',
			secondname = '$secondname',
			street = '$street',
			city = '$city',
			zip = '$zip',
			country = '$country',
			tel = '$tel',
			mobil = '$mobil',
			fax = '$fax',
			email = '$email',
			web = '$web',
			uid = '$uid',
			post = '$post',
			logo = '',
			newsletter = '$newsletter',
			password = '$password',
			id_card_no = '$id_card_no',
			student = '$student',
			matrical_nr = '$matrical_nr',
			specialist_species_for = '$specialist_species_for',
			own_practice = '$own_practice',
			group_practice = '$group_practice',
			employed = '$employed',
			birth = '$birth',
			join_date = '$join_date',
			industry = '$industry',
			administration = '$administration',
			university = '$university',
			no_exercise = '$no_exercise',
			retirement = '$retirement',
			other = '$other',
			hp_inside = '$hp_inside',
			`commend` = '$commend',
			reg_ip = '',
			reg_date = 0000-00-00,
			reg_domain = '',
			verify_key = '',
			blocked = 0,
 			additive = '',
			`desc` = '',
			map_user_id   = 0,
			map_page_id   = 0,
			map_company_id  = 0,
			activ         = 0,
			activate      = 0,
			delivery_company1   = '$delivery_company1',
			delivery_company2   = '$delivery_company2',
			delivery_title      = '$delivery_title',
			delivery_gender     = '$delivery_gender',
			delivery_firstname  = '$delivery_firstname',
			delivery_secondname = '$delivery_secondname',
			delivery_street     = '$delivery_street',
			delivery_city       = '$delivery_city',
			delivery_zip        = '$delivery_zip',
			delivery_country    = '$delivery_country',
			delivery_tel        = '$delivery_tel'
			") or die(mysqli_error($GLOBALS['mysqli']));
			$client_id = mysqli_insert_id($GLOBALS['mysqli']);


			/*
			 * Eweiterung OEGT
			 */

			if ($_POST['list_id'] == 'client_oegt_list') {
				include_once ('../oegt/save_client.php');
			}

			echo $client_id;
		}
		break;

	case 'accountgroup_in_list':
	case 'accountgroup_out_list':

		if ($_POST['update_id']) {
			$GLOBALS['mysqli']->query("UPDATE accountgroup SET
			company_id  = '$company_id',
			title       = '$title'
			WHERE accountgroup_id  = '{$_POST['update_id']}'
			") or die(mysqli_error($GLOBALS['mysqli']));
			echo "update";
		} else {

			if ($_POST['list_id'] == 'accountgroup_in_list')
				$option = 'in';
			else
				$option = 'out';

			$GLOBALS['mysqli']->query("INSERT INTO accountgroup SET
			`option`    = '$option',
			title       = '$title',
			company_id  = '$company_id',
			user_id = '{$_SESSION['user_id']}'
			") or die(mysqli_error($GLOBALS['mysqli']));
			echo "ok";
		}

		break;

	case 'option_list':
		// Template anlegen
		$GLOBALS['mysqli']->query("REPLACE INTO company SET
		company_id = '$update_id',
		user_id   = '{$_SESSION['user_id']}',
		company_1 = '$company_1',
		company_2 = '$company_2',
		title = '$title',
		firstname = '$firstname',
		secondname = '$secondname',
		street = '$street',
		city = '$city',
		zip = '$zip',
		country = '$country',
		tel = '$tel',
		email = '$email',
		web = '$web',
		uid = '$uid',
		company_number  = '$company_number',
		blz = '$blz',
		kdo = '$kdo',
		of_jurisdiction = '$of_jurisdiction',
		bank_name = '$bank_name',
		iban = '$iban',
		bic = '$bic',
		zvr = '$zvr',
		default_bill_number  = '$default_bill_number',
		conditions = '$conditions',
		content_footer = '$content_footer',
		remind_mail1 =  '$remind_mail1',
		remind_mail_subject1 = '$remind_mail_subject1',
		remind_mail2 =  '$remind_mail2',
		remind_mail_subject2 = '$remind_mail_subject2',
		remind_mail3 =  '$remind_mail3',
		remind_mail_subject3 = '$remind_mail_subject3',
		remind_time1 = '$remind_time1',
		remind_time2 = '$remind_time2',
		remind_time3 = '$remind_time3',
		remind_time4 = '$remind_time4',
		subject           = '$subject',
		grafic_head       = '$grafic_head',
		smtp_title   = '$smtp_title',
		smtp_server  = '$smtp_server',
		smtp_user    = '$smtp_user',
		smtp_password= '$smtp_password',
		smtp_port    = '$smtp_port',
		smtp_secure  = '$smtp_secure',
		smtp_email    = '$smtp_email',
		smtp_return = '$smtp_return',
		headline = '$headline',
		ang_headline =  '$ang_headline',
		ang_subject =  '$ang_subject',
		ang_conditions = '$ang_conditions',
		rn_send_mail =  '$rn_send_mail',
		rn_send_mail_subject = '$rn_send_mail_subject',
		ang_send_mail_subject = '$ang_send_mail_subject',
		ang_send_mail = '$ang_send_mail',
		ang_remind_time1= '$ang_remind_time1',
		ls_send_mail_subject= '$ls_send_mail_subject',
		ls_send_mail	= '$ls_send_mail',
		ls_remind_time1= '$ls_remind_time1',
		

	") or die(mysqli_error($GLOBALS['mysqli']));
		echo "ok";
		break;

	case 'account_out_list':
		$option = 'out';
	case 'account_in_list':

		if (!$option)
			$ouption = 'in';

		// Checked ob account schon verwendet wird und verhindert, das ändern
		$sql_check = $GLOBALS['mysqli']->query("SELECT * FROM accounts INNER JOIN issues ON account = account_id where account_id = '$update_id' ") or die(mysqli_error($GLOBALS['mysqli']));
		$set_lock = mysqli_num_rows($sql_check);

		if (!$set_lock) {
			if (isset($_POST['tax'])) {
				if (!$_POST['tax'])
					$tax = '0';
				$add_update = "tax = '$tax',";
			}
		}

		if (!$afa_400)
			$afa_400 = 0;

		if ($update_id) {
			$GLOBALS['mysqli']->query("UPDATE accounts SET
			company_id  = '{$company_id}',
			code        = '{$code}',
			$add_update
			title       = '{$title}',
			afa_400     = '{$afa_400}',
			accountgroup_id = '{$accountgroup_id}'
			WHERE account_id  = '$update_id'
			") or die(mysqli_error($GLOBALS['mysqli']));
			echo "update";
		} else {
			$GLOBALS['mysqli']->query("INSERT INTO accounts SET
			company_id  = '{$company_id}',
			code        = '{$code}',
			title       = '{$title}',
			tax         = '{$tax}',
			`option`    = '{$option}',
			afa_400     = '{$afa_400}',
			accountgroup_id = '{$accountgroup_id}'
			") or die(mysqli_error($GLOBALS['mysqli']));
			echo "ok";
		}

		break;

	case 'issues_list':

		if (!$bill_number)
			$bill_number = mysql_singleoutput("SELECT MAX(bill_number) as bill_number FROM issues  ", "bill_number") + 1; // WHERE company_id = '{$_SESSION['faktura_company_id']}'

		$query = $GLOBALS['mysqli']->query("SELECT bill_number FROM issues WHERE bill_number = '$bill_number' ") or die(mysqli_error($GLOBALS['mysqli']));
		if (mysqli_num_rows($query) and !$bill_id) {
			echo "number_exist";
			break;
		}

		$netto = preg_replace("/,/", ".", $_POST['netto']);
		$brutto = preg_replace("/,/", ".", $_POST['brutto']);

		// Auslesen der Prozent aus der Datenbank
		$tax = mysql_singleoutput("SELECT tax FROM accounts WHERE account_id = '{$_POST['account']}' ");
		if (!$tax)
			$tax = 0;

		if ($netto)
			$brutto = $netto + ($netto / 100 * $tax);
		else if ($brutto)
			$netto = $brutto / (100 + $tax) * 100;

		if (!$client_id)
			$client_id = 0;

		$mwst = $brutto - $netto;

		if (!$account)
			$account = 0;

		$amazon_order_nr = '';

		if (!$bill_id) {
			// New Insert from issue
			$GLOBALS['mysqli']->query("INSERT INTO issues SET
    		company_id  = '{$_SESSION['faktura_company_id']}',
    		client_id   = '$client_id',
    		bill_number = '$bill_number',
    		date_create = '$date_create',
    		date_booking= '$date_create', 
    		account     = $account,
    		description = '$description',
    		company_1   = '$company_1',
    		netto       = '$netto',
    		brutto      = ROUND($brutto, 2),
    		mwst        = '$mwst',
    		tax         = $tax,
            elba_id     = 0,
    		comment     = '$comment',
			amazon_order_nr = '$amazon_order_nr'
		") or die(mysqli_error($GLOBALS['mysqli']));
		} else {
			// UPDATE from an issue
			$GLOBALS['mysqli']->query("UPDATE issues SET
    		company_id  = '{$_SESSION['faktura_company_id']}',
    		client_id   = '$client_id',
    		bill_number = '$bill_number',
    		date_create = '$date_create',
    		date_booking= '$date_create',
    		account     = $account,
    		description = '$description',
    		company_1   = '$company_1',
    		netto       = '$netto',
    		brutto      = ROUND($brutto, 2),
    		mwst        = '$mwst',
    		tax         = $tax,
            elba_id     = 0,
    		comment     = '$comment'
			amazon_order_nr = '$amazon_order_nr'
                WHERE bill_id     = '$bill_id'
		") or die(mysqli_error($GLOBALS['mysqli']));
		}

		// Wird aufgerufden wenn Rechnungen manuell eingegben werden
		if (!$set_import) {
			// save last date insert for next issue
			setcookie("last_date_create", $_POST['date_create'], time() + 3600);

			if ($_POST['bill_id'])
				echo "update";
			else
				echo "ok";
		}
		break;

	/**
	 * *******************************************************************************
	 * GROUP - FORM 2
	 * *******************************************************************************
	 */

	case 'article_admin_list':

		// Check ob Eintrag bereits vorhanden ist
		// Art_NR UND Art_Title
		$temp_id = $_POST['update_id'];

		if (!$temp_id) {
			$check_art_nr = mysql_singleoutput("SELECT * FROM article_temp WHERE company_id = '{$_SESSION['faktura_company_id']}' AND art_nr = '$art_nr' ", "art_title");
			$check_title = mysql_singleoutput("SELECT * FROM article_temp WHERE company_id = '{$_SESSION['faktura_company_id']}' AND art_title = '$art_title' ", "art_title");
			$_POST['temp_id'] = '';
		}

		$netto = preg_replace("/,/", ".", $_POST['netto']);

		if ($check_art_nr) {
			echo 'double_art_nr';
		} elseif ($check_title) {
			echo 'double_title';
		} elseif (!$_POST['art_nr']) {
			echo 'empty_art_nr';
		} elseif (!$_POST['art_title']) {
			echo 'empty_title';
		} else {

			if (!$internet_show)
				$internet_show = 0;
			if (!$group_id)
				$group_id = 0;
			if (!$free)
				$free = 0;

			echo "REPLACE INTO article_temp SET
			temp_id    = '$temp_id',
			company_id = '{$_SESSION['faktura_company_id']}',
			format = '$format',
			count  = '$count',
			art_nr = '$art_nr',
			art_title = '$art_title',
			art_text  = '$art_text',
			account   = '$account',
			netto     = '$netto',
			internet_title = '$internet_title',
			internet_text = '$internet_text',
			internet_show = '$internet_show',
			internet_inside_title = '$internet_inside_title',
			internet_inside_text = '$internet_inside_text',
			gallery = '$gallery',
			gallery_inside = '$gallery_inside',
			group_id = '$group_id',
			free = '$free',
			pdf = '$pdf'
			";

			// Template anlegen
			// $GLOBALS['mysqli']->query ( "SET NAMES 'utf8'" ); // SonderZeichen äöü,... werden in Db uft8 gespeichert
			$GLOBALS['mysqli']->query("REPLACE INTO article_temp SET
			temp_id    = '$temp_id',
			company_id = '{$_SESSION['faktura_company_id']}',
			format = '$format',
			count  = '$count',
			art_nr = '$art_nr',
			art_title = '$art_title',
			art_text  = '$art_text',
			account   = '$account',
			netto     = '$netto',
			internet_title = '$internet_title',
			internet_text = '$internet_text',
			internet_show = '$internet_show',
			internet_inside_title = '$internet_inside_title',
			internet_inside_text = '$internet_inside_text',
			gallery = '$gallery',
			gallery_inside = '$gallery_inside',
			group_id = '$group_id',
			free = '$free',
			pdf = '$pdf'
			") or die(mysqli_error($GLOBALS['mysqli']));

			$art_id = mysqli_insert_id($GLOBALS['mysqli']);
			// echo $art_id;

			if ($groups) {
				$groups = explode(',', $groups);

				// print_r ( $groups );
				// Remove old connects
				$GLOBALS['mysqli']->query("DELETE FROM article2group WHERE article_id = '$art_id' ") or die(mysqli_error($GLOBALS['mysqli']));
				foreach ($groups as $key => $value) {
					$GLOBALS['mysqli']->query("INSERT INTO article2group SET article_id = $art_id, group_id = $value ") or die(mysqli_error($GLOBALS['mysqli']));
				}
			}
		}

		echo "ok";
		break;
}
?>