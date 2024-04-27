<?php
// ACHTUNG - hat keine Funktion mehr, dient nur mehr als Nachvollzug


exit;

//https://develop.ssi.at/ssi_service/manuel/transfer_smart_domain2company_domain.php

// Transferiert alle Domains in die neue Domainverwaltung - Zentralverwaltung auf ssi_company
include_once ('../../login/config_main.inc.php');
include_once ('../../ssi_userlist/fu_virtualhost_generator.php');


// Inhalte werden gelöscht
$GLOBALS['mysqli']->query ( "TRUNCATE TABLE ssi_company.`domain`" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );

$array_company = array ( '1' , '2' , '3' , '7' , '8' );

foreach ( $array_company as $company_id ) {
	
	$query = $GLOBALS['mysqli']->query ( "
	SELECT page_id, set_ssl, smart_page_locked, smart_domain domain, smart_domain_alias domain_alias, smart_page.user_id user_id  
	from ssi_smart$company_id.tbl_user, ssi_smart$company_id.smart_page
	where ssi_smart$company_id.tbl_user.user_id = ssi_smart$company_id.smart_page.user_id " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	while ( $array = mysqli_fetch_array ( $query ) ) {
		
		// Entrag in die globale Domainverwaltung
		$GLOBALS['mysqli']->query ( "
		INSERT ssi_company.domain SET
			company_id = '{$company_id}',
			user_id = '{$array[user_id]}',
			page_id = '{$array[page_id]}',
			domain = '{$array[domain]}',
			forwarding = '{$array[domain_forwarding]}',
			set_ssl = '{$array[set_ssl]}',
			locked  = '{$array[smart_page_locked]}'
			" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		
		$domain_id = mysqli_insert_id ( $GLOBALS['mysqli'] );
		$count_domain ++;
		
		// Übertragung der aliases in die Datenbank
		$array_domain_alias = preg_split ( '/\n/', $array[domain_alias] );
		foreach ( $array_domain_alias as $domain_alias ) {
			$domain_alias = trim ( $domain_alias );
			if ($domain_alias) {
				$query_count = $GLOBALS['mysqli']->query ( "SELECT * FROM ssi_company.domain WHERE domain = '$domain_alias' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
				$domain_exist = mysqli_num_rows ( $query_count );
				
				// Prüfen ob Domain bereits existiert
				if ($domain_exist == 0 ) {
					$count_alias ++;
					$GLOBALS['mysqli']->query ( "INSERT ssi_company.domain SET domain = '$domain_alias', parent_id = '$domain_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
					// $GLOBALS['mysqli']->query ( "INSERT ssi_company.domain_alias SET domain_id ='$domain_id', alias = '$domain_alias'" );
				}
			}
		}
		// echo $array[domain] . "- tranfered<br>";
	}
	
	// Domains abrufen und das Array übergeben
	$query_domain = $GLOBALS['mysqli']->query ( "SELECT domain_id, domain, locked, forwarding, page_id, user_id FROM ssi_company.domain" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	while ( $array = mysqli_fetch_array ( $query_domain ) ) {
		
		$domain_id = $array['domain_id'];
		// Aliases
		$query = $GLOBALS['mysqli']->query ( "SELECT * FROM ssi_company.domain WHERE parent_id = '$domain_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		while ( $fetch_alias = mysqli_fetch_array ( $query ) ) {
			$array_alias[] = $fetch_alias;
		}
		$array['array_alias'] = $array_alias;
		
		$array_domains[$domain_id] = $array;
	}
	$count_domain_sum += $count_domain;
	$count_alias_sum += $count_alias;
	echo "Company" . $company_id . ": (Domain: $count_domain, Alias: $count_alias)<br>";
	$count_domain = $count_alias = 0;
	
	fu_virtualhost_generator ($company_id);
	
}
echo "<hr>";
echo "Summe Domain: $count_domain_sum<br>";
echo "Summe Alias: $count_alias_sum<br>";

// Array anzeigen
// echo "<pre>";
// echo print_r ( $array_domains );
// echo "</pre>";

// echo var_dump($array_domains);


//fu_virtualhost_generator ( $array_domains );