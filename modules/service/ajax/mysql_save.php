<?
include_once (__DIR__ . '/../../../config.php');
get_message("Auführung gestartet", '');

// $message = "<div class='message ui' style='max-width:1000px;'><i class='close icon'></i><div class='header'>Info</div>	<div id='content_query'></div><br></div>";
// echo "$('#output_mysql').html(\"$message\"); $('.message .close').on('click', function() { $(this).closest('.message').transition('fade');});";

// ob_get_contents();
// flush();
// ob_flush();

// include ("inc/function_fix_utf8.php");
$query_array = array();
$query_message = array();
$array_database = array();

$link = mysqli_connect($host, $username, $password, $dbname);

mysqli_query($link, "SET NAMES 'utf8'");

foreach ($_POST as $key => $value) {
	$GLOBALS[$key] = mysqli_real_escape_string($link, $value);
}

if (!$database and $database_select)
	$database = $database_select;

if (!$database) {
	get_message("Keine Datenbank definiert", '');
}

if ($database == 'ssi_smart_all') {
	$array_database = array("ssi_smart1", "ssi_smart2", "ssi_smart3", "ssi_smart4", "ssi_smart7", "ssi_smart8");
} elseif ($database == 'ssi_faktura_all') {
	$array_database = array("ssi_faktura", "ssi_faktura93", "ssi_faktura94", 'ssi_faktura1287');
} else
	$array_database = array($database);

// Erzeugen von sql von Table+Field+Value
if ($set_table && $field) {
	switch ($field) {
		case "timestamp":
		case "date":
		case "datetime":
		case "text":
			$query_alter = "ALTER TABLE `$set_table` ADD `$field_name` $field NOT NULL";
			break;
		case "int":
		case "varchar":
			if (!$set_value) {
				$set_value = 10;
			}
			$query_alter = "ALTER TABLE `$set_table` ADD `$field_name` $field($set_value) NOT NULL";

			break;
	}

	//get_message ( "$query_alter", '' );
	$query_array[] = $query_alter;
} elseif ($_POST['query']) {
	//Allgemeine geschriebene Anfragen
	$query = str_replace(array("\n", "\r"), '', $_POST['query']);

	if (preg_match("/^select/i", $query)) {
		mysqli_select_db($link, $database);
		$result = mysqli_query($link, $query) or die(mysqli_error($link));
		// Logfile
		mysqli_query($link, "INSERT INTO `ssi_company`.`log_service_sql` SET query='$query', `database`='$database' ") or die(mysqli_error($link));
		$sizes = array();
		$row = mysqli_fetch_assoc($result);

		if (is_array($row)) {
			foreach ($row as $key => $value) {
				$sizes[$key] = strlen($key); // initialize to the size of the column name
			}
			while ($row = mysqli_fetch_assoc($result)) {
				foreach ($row as $key => $value) {
					$length = strlen($value);
					if ($length > $sizes[$key])
						$sizes[$key] = $length; // get largest result size
				}
			}

			mysqli_data_seek($result, 0); // set your pointer back to the beginning.

			// top of output
			foreach ($sizes as $length) {
				$output .= "+" . str_pad("", $length + 2, "-");
			}
			$output .= "+\n";

			// column names
			$row = mysqli_fetch_assoc($result);
			foreach ($row as $key => $value) {
				$output .= "| ";
				$output .= str_pad($key, $sizes[$key] + 1);
			}
			$output .= "|\n";

			// line under column names
			foreach ($sizes as $length) {
				$output .= "+" . str_pad("", $length + 2, "-");
			}
			$output .= "+\n";

			// output data
			do {
				foreach ($row as $key => $value) {
					$output .= "| ";
					$output .= str_pad($value, $sizes[$key] + 1);
				}
				$output .= "|\n";
			} while ($row = mysqli_fetch_assoc($result));

			// bottom of output
			foreach ($sizes as $length) {
				$output .= "+" . str_pad("", $length + 2, "-");
			}
			$output .= "+\n";
		} else {
			$output .= "Keine Daten vorhanden";
		}

		get_message("<div style='max-height:400px; overflow: auto;'><pre>$output</pre></div>", '');
		exit();
	} else {
		//$query_array = explode ( ";", $_POST ['query'] );
		$query_array[] = $_POST['query'];
	}
}

/**
 * *****************************************
 * Ab-arbeitung auch von mehreren Datenbanken
 * *****************************************
 */

foreach ($array_database as $key => $database) {

	$count = '';

	mysqli_select_db($link, $database);

	if (!$utf8_column)
		$utf8_column = 'all';

	// Aufruf Function für Umwandlung auf tables und columns utf8
	if ($checkbox_table_utf8) {

		if (!$utf8_table) {
			// Wenn alle Tables der DB konvertieren
			$qres = mysqli_query($link, 'show tables') or die(mysqli_error($GLOBALS['mysqli']));
			while (list($tabelle) = mysqli_fetch_row($qres)) {
				if ($tabelle) {
					$query_array[] = "ALTER TABLE `$tabelle` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
					$query_array[] = "ALTER TABLE `$tabelle` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
					$count1++;
				}
			}
		} else {
			// Einzelnes Table konvertieren
			$query_array[] = "ALTER TABLE `$utf8_table` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
		}
	}

	// Startet Replace von Sonder Zeichen
	if ($checkbox_replace_specialcaracter) {
		//alle Table
		if (!$utf8_table) {
			$qres = mysqli_query($link, 'show tables') or die(mysqli_error($GLOBALS['mysqli']));
			while (list($tabelle) = mysqli_fetch_row($qres)) {
				if ($tabelle) {
					$query_array = array_merge(replace_specialcaracter($tabelle, $utf8_column), $query_array);
				}
			}
		} //gewählte Table
		else
			$query_array = array_merge(replace_specialcaracter($utf8_table, $utf8_column), $query_array);
	}

	// HTML - Sonderzeichen
	if ($checkbox_replace_entities) {
		//alle Table
		if (!$utf8_table) {
			$qres = mysqli_query($link, 'show tables') or die(mysqli_error($GLOBALS['mysqli']));
			while (list($tabelle) = mysqli_fetch_row($qres)) {
				if ($tabelle) {
					$query_array = array_merge(replace_entities_column($tabelle, $utf8_column), $query_array);
				}
			}
		} //gewählte Table
		else
			$query_array = array_merge(replace_entities_column($utf8_table, $utf8_column), $query_array);
	}

	// Aufruf zur Optimierung der Tables
	if ($checkbox_optimize_tables) {
		$alltables = mysqli_query($link, "SHOW TABLES");
		while ($table = mysqli_fetch_assoc($alltables)) {
			foreach ($table as $db => $tablename) {
				$query_array[] = "OPTIMIZE TABLE `$tablename`";
				$count4++;
			}
		}
	}

	// 	echo "<pre>";
	// 	print_r ( $query_array );
	// 	echo "</pre>";
	// 	exit ();

	if (is_array($query_array)) {
		foreach ($query_array as $key => $query) {
			get_message("$query", 'grey');
			if (mysqli_multi_query($link, $query)) {

				do {
					/* store first result set */
					if ($result = mysqli_store_result($link)) {
						while ($row = mysqli_fetch_row($result)) {
							//printf("%s\n", $row[0]);
						}
						mysqli_free_result($result);
					}
				} while (mysqli_more_results($link)); // mysqli_next_result
			}

			$error_query = mysqli_error($link);
			if ($error_query) {
				get_message("<br>$database -> $error_query<br>($query)", 'red');
			} else {
				$count++;
			}
		}
	}

	if (!$count)
		get_message("Keine Sql-Befehle für die '$database' abzuarbeiten", '');
	else
		get_message("<hr>Ausführung von <b>$count</b> Querys für '$database' waren erfolgreich!<hr>", 'green');
}

exit();

// Wandelt Sonderzeichen id Column richtig um
// function fix_utf8_column($fix_table, $fix_column) {
// 	echo "test";
// 	$query_array[] = "
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`,'ÃŸ', 'ß');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ã¤', 'ä');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ã¼', 'ü');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ã¶', 'ö');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ã„', 'Ä');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ãœ', 'Ü');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ã–', 'Ö');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'â‚¬', '€');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã‰','É');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã‡','Ç');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ãƒ','Ã');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã ','À');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ãº','ú');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'â€¢','-');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã˜','Ø');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ãµ','õ');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã­','í');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã¢','â');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã£','ã');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ãª','ê');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã¡','á');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã©','é');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã³','ó');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'â€“','–');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã§','ç');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Âª','ª');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Âº','º');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã ','à');
// 	update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'â€œ','\"');
// 	update `$fix_table` set `$fix_column` = replace(`$fix_column` ,'â€','\"');
// 	update `$fix_table` set `$fix_column` = replace(`$fix_column` ,'Â','');
// 	update `$fix_table` set `$fix_column` = replace(`$fix_column` ,'Å¾','');
// 	update `$fix_table` set `$fix_column` = replace(`$fix_column` ,'ž','');
// 	";
// 	return $query_array;
// }

// Wandelt Sonderzeichen id Column richtig um
function fix_utf8_column($fix_table, $fix_column)
{
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`,'ÃŸ', 'ß')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ã¤', 'ä')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ã¼', 'ü')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ã?', 'Ü')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'â?', 'ü')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'uÌˆ', 'ü')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ã¶', 'ö')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ã„', 'Ä')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ãœ', 'Ü')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'Ã–', 'Ö')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column`, 'â‚¬', '€')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã‰','É')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã‡','Ç')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ãƒ','Ã')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã ','À')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ãº','ú')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'â€¢','-')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã˜','Ø')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ãµ','õ')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã­','í')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã¢','â')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã£','ã')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ãª','ê')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã¡','á')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã©','é')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã³','ó')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'â€“','–')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã§','ç')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Âª','ª')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Âº','º')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Ã ','à')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'â€œ','\"')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'â€','\"')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Â','')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'Å¾','')";
	$query_array[] = "update `$fix_table` set `$fix_column` = REPLACE(`$fix_column` ,'ž','')";
	return $query_array;
}
function fix_utf8_entities_column($fix_table, $fix_column)
{
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&ccedil;','ç')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&atilde;','ã')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&aacute;','á')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&acirc;','â')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&eacute;','é')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&iacute;','í')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&otilde;','õ')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&uacute;','ú')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&ccedil;','ç')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Aacute;','Á')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Acirc;','Â')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Eacute;','É')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Iacute;','Í')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Otilde;','Õ')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Uacute;','Ú')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Ccedil;','Ç')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Atilde;','Ã')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Agrave;','À')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Ecirc;','Ê')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Oacute;','Ó')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Ocirc;','Ô')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Uuml;','Ü')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&uuml;','ü')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Auml;','Ä')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&auml;','ä')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Ouml;','Ö')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&ouml;','ö')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&atilde;','ã')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&agrave;','à')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&szlig;','ß')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&ecirc;','ê')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&oacute;','ó')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&ocirc;','ô')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&amp;','&')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&gt;','>')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&lt;','<')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&circ;','ˆ')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&tilde;','˜')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&uml;','¨')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&cute;','´')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&cedil;','¸')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&quot;','\"')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&ldquo;','“')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&rdquo;','”')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&lsquo;','‘')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&rsquo;','’')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&lsaquo;','‹')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&rsaquo;','›')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&laquo;','«')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&raquo;','»')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&ordm;','º')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&ordf;','ª')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&ndash;','–')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&mdash;','—')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&macr;','¯')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&hellip;','…')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&brvbar;','¦')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&bull;','•')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&para;','¶')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&sect;','§')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&sup1;','¹')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&sup2;','²')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&sup3;','³')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&frac12;','½')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&frac14;','¼')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&frac34;','¾')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&#8539;','⅛')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&#8540;','⅜')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&#8541;','⅝')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&#8542;','⅞')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&gt;','>')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&lt;','<')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&plusmn;','±')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&minus;','−')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&times;','×')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&divide;','÷')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&lowast;','∗')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&frasl;','⁄')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&permil;','‰')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&int;','∫')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&sum;','∑')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&prod;','∏')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&radic;','√')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&infin;','∞')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&asymp;','≈')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&cong;','≅')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&prop;','∝')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&equiv;','≡')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&ne;','≠')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&le;','≤')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&ge;','≥')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&there4;','∴')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&sdot;','⋅')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&middot;','·')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&part;','∂')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&image;','ℑ')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&real;','ℜ')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&prime;','′')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Prime;','″')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&deg;','°')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&ang;','∠')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&perp;','⊥')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&nabla;','∇')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&oplus;','⊕')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&otimes;','⊗')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&alefsym;','ℵ')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&oslash;','ø')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&Oslash;','Ø')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&isin;','∈')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&notin;','∉')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&cap;','∩')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&cup;','∪')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&sub;','⊂')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&sup;','⊃')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&sube;','⊆')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&supe;','⊇')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&exist;','∃')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&forall;','∀')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&empty;','∅')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&not;','¬')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&and;','∧')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&or;','∨')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'&crarr;','↵')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'o&Igrave;&circ;','ö')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'a&Igrave;&circ;','ä')";
	$query_array[] = "update `$fix_table` set `$fix_column` = replace($fix_column ,'u&Igrave;&circ;','ä')";
	return $query_array;
}
/*
 * update `smart_langLayer` set `text` = replace(text ,'o&Igrave;&circ;','&ouml;');
 * update `smart_langLayer` set `text` = replace(text ,'a&Igrave;&circ;','&auml;');
 * update `smart_langLayer` set `text` = replace(text ,'u&Igrave;&circ;','&uuml;');
 */

//Replace Specialcaracter 
function replace_specialcaracter($utf8_table, $utf8_column)
{
	global $link;
	$query_array = array();
	if ($utf8_column == 'all') {
		$result = mysqli_query($link, "SELECT * FROM $utf8_table");
		while ($property = mysqli_fetch_field($result)) {
			$query_array = array_merge(fix_utf8_column($utf8_table, $property->name), $query_array);
		}
	} else {
		$query_array = array_merge(fix_utf8_column($utf8_table, $utf8_column), $query_array);
	}
	return $query_array;
}

//Replace Entities
function replace_entities_column($utf8_table, $utf8_column)
{
	global $link;
	$query_array = array();
	if ($utf8_column == 'all') {
		$result = mysqli_query($link, "SELECT * FROM $utf8_table");
		while ($property = mysqli_fetch_field($result)) {
			$query_array = array_merge(fix_utf8_entities_column($utf8_table, $property->name), $query_array);
		}
	} else {
		$query_array = array_merge(fix_utf8_entities_column($utf8_table, $utf8_column), $query_array);
	}
	return $query_array;
}
function get_message($message, $status)
{
	$message = "<span class='ui text $status'>$message</span><br>";
	echo $message;
	flush();
	ob_flush();
}
