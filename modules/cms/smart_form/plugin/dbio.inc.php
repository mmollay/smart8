<?php

/**
 * dbio.inc.php - Klasse fuer MySQL-Datenbank-Import und Export
 *
 * @author Bert Klauninger
 * @last-changed 2009-03-31 (BK)
 * @history
 *     2009-03-31 - Konvertierung von Sonderzeichen eingebaut (BK)
 *     2009-04-15 - Export von Excel-konformen Daten eingebaut (BK)
 *                  Ausgabe auf stdout bei Export unterstuetzt (BK)
 *     2010-05-08 - "\n" und "\r" werden bei Export aus den Feldern entfernt
 *     2019-01-14   db = false (optional)
 *
 */
// require_once("config.inc.php");
// require_once("util.inc.php");
class DBIO {
	//private $dbconn;
	function __construct($host, $user, $pass, $db = false) {
		//$this->dbconn = mysqli_connect ( $host, $user, $pass ) or die ( "cannot connect to mySQL host $host" );
		//mysql_select_db ( $db, $this->dbconn ) or die ( "cannot select database $db" );
		
		if ($db) mysqli_select_db ( $GLOBALS['mysqli'], $db ) or die ( 'Could not select database ' . $db );
		
	}
	function import($filename, $tablename, $column_list, $separator = ';', $iconv_source = 'utf8', $clear_table = false, $set_passwords = false, $pwdfield = null, $notifyuser = false, $emailfield = null, $email_subj = null, $email_tplt = null, $test_mode = true) {
		/**
		 * import() - Textdatei in Datenbank importieren
		 *
		 * @param $filename Name
		 *        	der Textdatei
		 * @param $tablename Name
		 *        	der DB-Tabelle
		 * @param $column_list Reihenfolge
		 *        	der DB-Felder in der Textdatei (durch $separator getrennt)
		 * @param $separator Trennzeichen
		 *        	in der Textdatei
		 * @param $clear_table Soll
		 *        	vor dem Import die Tabelle geleert werden (optional) ?
		 * @param $set_passwords Zufallspasswoerter
		 *        	vergeben (optional) ?
		 * @param $pwdfield Name
		 *        	des Passwortfeldes (optional)
		 * @param $notifyuser Passwoerter
		 *        	per Mail versenden (optional) ?
		 * @param $emailfield Name
		 *        	des DB-Feldes, das die E-Mail Adresse enthaelt (optional)
		 * @param $email_subj Betreff-Zeile
		 *        	des Mails (optional)
		 * @param $email_tplt Inhalt
		 *        	des Mails, das an die User versendet werden soll (optional)
		 * @param $test_mode Wenn
		 *        	true, werden Mails nicht wirklich versandt
		 *        	
		 * @return Anzahl der importierten Datensaetze
		 */
		$columns = explode ( $separator, $column_list );
		$fp = fopen ( $filename, "r" ) or die ( "cannot open file $filename for reading" );
		
		if ($clear_table) {
			$sql = "DELETE FROM $tablename";
			$GLOBALS['mysqli']->query ( $sql) or die ( "cannot clear table $tablename" );
		}
		
		$lc = 0;
		// $GLOBALS['mysqli']->query("SET NAMES 'utf8'");
		while ( ! feof ( $fp ) ) {
			$line = iconv ( $iconv_source, 'iso-8859-1', trim ( fgets ( $fp ) ) );
			if ($line != "") {
				++ $lc;
				$fields = explode ( $separator, $line );
				if (count ( $fields ) != count ( $columns )) {
					die ( "error at line $lc of input file $filename" );
				}
				$sql = "INSERT INTO $tablename (";
				foreach ( $columns as $col ) {
					$sql .= "$col,";
				}
				
				if ($set_passwords === true) {
					// Zufallspasswort setzen
					$sql .= "$pwdfield,";
				}
				
				$sql = substr ( $sql, 0, - 1 ) . ") VALUES (";
				
				foreach ( $fields as $field ) {
					
					$sql .= "'" . mysql_escape_string ( $field ) . "',";
				}
				
				if ($set_passwords === true) {
					$pass = getpass ();
					$sql .= "'" . $pass . "',";
				}
				
				$sql = substr ( $sql, 0, - 1 ) . ")";
				
				$GLOBALS['mysqli']->query ( $sql) or die ( "cannot execute query: $sql" );
				
				if ($notifyuser == true) {
					// Mail an Benutzer ausschicken
					$body = $email_tplt;
					$subj = $email_subj;
					for($i = 0; $i < count ( $columns ); ++ $i) {
						// Ersetzen aller Platzhalter der Form {feldname}
						$body = str_replace ( '{' . $columns [$i] . '}', $fields [$i], $body );
						$subj = str_replace ( '{' . $columns [$i] . '}', $fields [$i], $subj );
						if ($columns [$i] == $emailfield) {
							$rcpt = $fields [$i];
						}
					}
					
					// auch das Passwort-Feld ersetzen
					$body = str_replace ( '{' . $pwdfield . '}', $pass, $body );
					
					if ($test_mode) {
						echo ("MAIL\n");
						echo ("  TO: $rcpt\n");
						echo ("  Subject: $subj\n\n$body\n\n");
					} else {
						// mail($rcpt, $subj, $body);
					}
				}
			}
		}
		
		return $lc;
	}
	function export($filename, $tablename, $column_array, $separator = ';', $filter = null) {
		/**
		 * export() - Datenbank-Tabelle in importieren
		 *
		 * @param $filename Name
		 *        	der Textdatei; "-" = stdout
		 * @param $tablename Name
		 *        	der DB-Tabelle
		 * @param $column_array Reihenfolge
		 *        	der DB-Felder in der Textdatei (Array)
		 * @param $separator Trennzeichen
		 *        	in der Textdatei
		 * @param $filter SQL-Filter-String        	
		 *
		 * @return Anzahl der exportierten Datensaetze
		 */
		$stdout = ($filename == "-");
		
		if (! $stdout) {
			$fp = fopen ( $filename, "w" ) or die ( "cannot open file $filename for writing" );
		}
		
		$sql = "SELECT ";
		
		foreach ( $column_array as $col ) {
			$sql .= "$col,";
		}
		$sql = substr ( $sql, 0, - 1 ) . " FROM $tablename";
		if ($filter !== null) {
			$sql .= " WHERE 1 $filter";
		}
		
		$result = $GLOBALS['mysqli']->query ( $sql ) or die( mysqli_error($GLOBALS['mysqli']));
		
		$lc = 0;
		
		// Spaltenbezeichnungen
		if ($stdout) {
			echo (implode ( $separator, $column_array ) . "\n");
		} else {
			fputs ( $fp, implode ( $separator, $column_array ) . "\n" );
		}
		
		// Inhalte
		while ( $row = mysqli_fetch_row ( $result ) ) {
			++ $lc;
			
			foreach ( $row as &$field ) {
				$field = preg_replace ( "/\n|\r/", "", $field );
				
				// change format numeric "in german format for export mm@ssi.at 11.6.2012
				
				if (is_numeric ( $field ) and preg_match ( "/\./", $field )) {
					$field = number_format ( $field, 2, ',', '.' );
				}
				
				if (strpos ( $field, $separator ) !== false) {
					$field = '"' . $field . '"';
				}
			}
			$line = implode ( $separator, $row );
			
			if ($stdout) {
				echo ($line . "\n");
			} else {
				fputs ( $fp, $line . "\n" );
			}
		}
		
		if (! $stdout) {
			fclose ( $fp );
		}
		
		return $lc;
	}
	function set_passwords($tablename, $pwdfield, $overwrite = false) {
		/**
		 * set_passwords() - Zufallspasswoerter vergeben
		 * Die Passwoerter bestehen aus Hex-Ziffern [0-9a-f]
		 * Diese Methode ist wesentlich schneller, als die Passwoerter ueber import() zu
		 * setzen, allerdings weniger flexibel (keine Mail-Verstaendigung etc.)
		 *
		 * @param $tablename Name
		 *        	der DB-Tabelle
		 * @param $pwdfield Feld,
		 *        	welches das Passwort enthaelt
		 * @param $overwrite Feld
		 *        	ueberschreiben, auch wenn bereits ein Passwort gesetzt ist? (optional)
		 * @return true, wenn erfolgreich
		 */
		$sql = "UPDATE $tablename" . "  SET $pwdfield = SUBSTRING(MD5(ROUND(RAND(9999)*1000)),-10)";
		if (! $overwrite) {
			$sql .= "  WHERE $pwdfield IS NULL OR $pwdfield = ''";
		}
		$GLOBALS['mysqli']->query ( $sql ) or die ( "cannot execute query: $sql" );
		return true;
	}
	function __destruct() {
		$this->dbconn = null;
	}
}

?>
