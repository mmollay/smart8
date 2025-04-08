<?
/*
 * --------------------------------------------------------------------------------------------------------
 * | SMART - LIST (ssi-Product)
 * | List-Generator: Using Semantic-UI Library
 * | 03.12.2023 - mm@ssi.at
 * | Version 2.6
 * --------------------------------------------------------------------------------------------------------
 */
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
//error_reporting(E_ERROR | E_PARSE);
error_reporting(0);

session_start();
// Call filter from GET
// for this function you have to set 'list_id' before call this include!!
// Example: http://localhost/smart_form/examples/list.php?search=Martin&category=second
if (isset($list_id)) {

	if (!empty($_GET)) {
		unset($_SESSION['search']);
		unset($_SESSION['filter']);
	}

	if (isset($_GET['search']))
		$_SESSION['input_search'][$list_id] = $_GET['search'];
	foreach ($_GET as $key => $value) {
		$_SESSION["filter"][$list_id][$key] = $value;
	}
}

/*
 * Generiert eine Listendarstellung
 */
function call_list($config_path, $mysql_connect_path, $data = false)
{
	$count_th = 1;
	$list_td = '';

	$str_default_text_notfound = "<i class='icon search'></i>Keine Einträge für {data} vorhanden.";
	$str_default_text = "Kein Eintrag vorhanden";

	// setzt cookie für wortpath bei reload

	// TODO Geht leider nicht im Smart-Kit kommt Fehler : Cannot modify header information - headers already sent by
	// if ($_SESSION['workpath'])
	// setcookie ( "test", $_SESSION['workpath'], time () + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST'] );

	// Wird bei Suchanfrage intern weitergereicht, dass sich sonst der WORKPATH ändern würde
	if (!isset($_POST['table_reload'])) {

		$path_parts = pathinfo($_SERVER['PHP_SELF']);
		$_SESSION['workpath'] = $path_parts['dirname'];
		if ($_SESSION['workpath'] == '/')
			$_SESSION['workpath'] = '';
		// Es wir ein manueller Workpath gesetzt
		// if ($data['workpath'])
		// $_SESSION['workpath'] = $data['workpath'];
	} else if ($_COOKIE["workpath"] && !$_SESSION["workpath"]) {
		// Wenn COOKIE gesetzt worden ist wird diese für den Workpath verwendet
		$_SESSION['workpath'] = $_COOKIE["workpath"];
	}

	if ($mysql_connect_path) {
		$mysql_connect_path = realpath($mysql_connect_path);
		include ($mysql_connect_path);
	}

	$config_path = realpath($config_path);
	include ("$config_path");

	echo $arr['list']['test'];

	// $GLOBALS['mysqli']->query("SET NAMES 'utf8'");

	// $GLOBALS['mysqli']->query("SET NAMES 'utf8'");
	// include ($config_path );
	// include ($mysql_connect_path );
	// Suchbegriff
	// $input_search = $_SESSION['input_search'][$list_id];
	// $input_search = trim ( $input_search );

	if (isset($list_width)) {
		$list_style .= 'max-width:' . $list_width . ';';
	}

	// OPTIONS
	$list_align = $arr['list']['align'] ?? '';
	$list_header = $arr['list']['header'] ?? true;
	$list_width = $arr['list']['width'] ?? '';
	$list_size = $arr['list']['size'] ?? '';
	$list_class = $arr['list']['class'] ?? '';
	$list_style = $arr['list']['style'] ?? '';
	$list_id = $arr['list']['id'];
	$serial = $arr['list']['serial'] ?? ''; // Zeigt fortlaufende Nummer an
	$show_empty = $arr['search']['show_empty'] ?? '';

	$input_search_array = $_SESSION['input_search'] ?? null;
	$input_search = isset($input_search_array[$list_id]) ? $input_search_array[$list_id] : '';

	// Überprüfen, ob $input_search null ist
	if ($input_search !== null) {
		// Ersetze '\+' durch '' (leerer String)
		$input_search = str_replace('\+', '', $input_search);

		// Entferne führende und nachfolgende Leerzeichen
		$input_search = trim($input_search);

		// Ersetze mehrere aufeinanderfolgende Leerzeichen durch ein einzelnes Leerzeichen
		$input_search = preg_replace('/(\\\s){2,}/', '$1', $input_search);

		// Entferne alle verbleibenden '+'
		$input_search = str_replace('+', '', $input_search);

		// Setze das bereinigte $input_search in die globale Variable
		$GLOBALS['input_search'] = $input_search;
	} else {
		// Handle null case, z.B. setzen Sie $input_search auf einen leeren String oder einen Standardwert
		$GLOBALS['input_search'] = "";
	}

	// Wird übergeben wenn die Table neu geladen werde soll in call_table.php
	$_SESSION['smart_list_config'][$list_id] = array('config_path' => $config_path, 'mysql_connect_path' => $mysql_connect_path, 'data' => $data);

	// print_r($_SESSION['smart_list_config']);

	// Daten werden in Cookie gespeichert und in call_table verwendet
	if ($_SESSION['smart_list_config'])
		setcookie("smart_list_config", serialize($_SESSION['smart_list_config']), time() + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST']);

	/*
	 * Button-List for Checkbox - multiediting
	 */
	$button_checkbox = buttons('', $arr, 'checkbox');
	$list_td .= "<tr style='display:none' class='tr-checkbox-button-$list_id' ><td colspan=$count_th>$button_checkbox</td></tr>";

	/**
	 * ************************************************************************
	 * CALL - DATA (mysql or array)
	 * ************************************************************************
	 */

	if (!isset($arr['search']['show_empty']) or $GLOBALS['input_search']) {

		$timeStart = microtime_float();

		if (isset($arr['mysql']) && is_array($arr['mysql'])) {
			// DATA - MYSQL ******************************************************************************************
			include (__DIR__ . '/include/list/data_mysql.php');
		} else {

			// DATA - ARRAY **************************************************************************************
			include (__DIR__ . '/include/list/data_array.php');

		}

		$timeEnd = microtime_float();

		include (__DIR__ . '/include/list/header.php');

		if (is_array($arr['checkbox']))
			$count_th++;

		if ($serial == true)
			$count_th++;
	}

	/**
	 * ************************************************************************
	 * END - CALL - DATA (mysql or array)
	 * ************************************************************************
	 */

	if ($count_th > 10)
		include (__DIR__ . '/include/list/checkbox.php');

	$default_text_notfound = isset($arr['search']['default_text_notfound']) ? $arr['search']['default_text_notfound'] : $str_default_text_notfound;
	$default_text = isset($arr['search']['default_text']) ? $arr['search']['default_text'] : $str_default_text;

	if (!$count_line or !$no_body) {

		if ($input_search) {
			$empty_text = str_replace('{data}', $GLOBALS['input_search'], $default_text_notfound);
		} else
			$empty_text = "$default_text";

		$list_td .= "<tr><td colspan='$count_th' ><br><br><div align=center>$empty_text</div><br><br></td></tr>";
	} else {
		include (__DIR__ . '/include/list/total.php');
	}

	/*
	 * BODY
	 */
	$output_body = "<tbody>";
	$output_body .= $total_tr;
	$output_body .= $list_td;
	// $output_body .= $total_tr;
	$output_body .= "</tbody>";
	/*
	 * FOOTER
	 */



	$run_time = round($timeEnd - $timeStart, 3);

	if ($run_time && $arr['list']['loading_time'] === true)
		$loading_time = "<br>(" . $run_time . "sek)";

	/**
	 * *****************************************************************
	 * Flypout - GENERATOR
	 * MODAL- GENERATOR
	 * mm@ssi.at Update 18.10.20202
	 * Erzeugt ein oder mehrere Flyout und Modalelemten
	 * *****************************************************************
	 */

	if (is_array($arr['flyout'])) {
		$modul_flyout = generate_element($arr['flyout'], 'flyout');
	}
	if (is_array($arr['modal'])) {
		$modul_modal = generate_element($arr['modal'], 'modal');
	}

	/**
	 * *********************************************
	 * Ab hier wird Liste neu geladen bei Reload
	 * ********************************************
	 */
	$output_table .= "<div style='margin-top:12px;' id='$list_id' class='smart_list' >";
	$output_table .= $arr['content']['top'];

	// $dropdown_order = 1;
	if ($dropdown_order) {
		$output_table_order = "<div style='float:right'>" . $dropdown_order . "</div>";
	}

	if ($dropdown_group)
		$output_table_group = "<div style='float:right'>" . $dropdown_group . "</div>";


	if ($arr['list']['auto_reload']) {
		$output_auto_reload = list_auto_reload($list_id, $arr['list']['auto_reload']);
	}

	if ($fields_filter or $dropdown_order or $dropdown_group) {
		$output_table .= "
			<div id='$list_id' class='ui message smart_list_filter'>
				<div class='fields'>
					<div style='float:left'>$fields_filter</div>
					<div style='float:right'>$output_table_order $output_table_group</div>
					<div style='clear:both'></div>
				</div>
			</div>$output_auto_reload";
	}

	/**
	 * *******************************************************
	 * Ausgabe der Table oder Liste
	 * *******************************************************
	 */

	if ($arr['list']['template']) {

		// Ausgabe bei Templateausgabe
		$output_table .= "<div align='left'>$output_template</div>";
		$output_table .= $empty_text;
		$output_table .= "<div align='left'>$txt_count_all $txt_count_filter $txt_limitbar $loading_time</div>";
	} else if (!$arr['list']['hide']) {
		// Table - Main
		$output_table .= "<table border=0 class='ui table $list_size $list_class' style='$list_style'>";

		// Sortierfunktion
		$output_table .= $output_order;

		// if ($show_th)
		$output_table .= $output_head;
		$output_table .= $output_body;

		if ($arr['list']['footer'] !== false) {
			$arr['list']['footer'] = true;
			// FOOTER 2
			$output_table .= "<tfoot class='full-width'><tr><th colspan=$count_th>$txt_count_all $txt_count_filter $txt_limitbar $loading_time</th></tr></tfoot>";
		}

		$output_table .= "</table>";
	}
	$output_table .= $arr['content']['bottom'] . "</div>";

	$_SESSION['export']['db'] = $arr['mysql']['db'];
	$_SESSION['export']['table'] = $arr['mysql']['table'];
	$_SESSION['export']['filter'] = $sql_export;
	$_SESSION['export']['field'] = $arr['mysql']['export'];

	// Outpup - just Table
	if ($_POST['table_reload']) {
		return $output_table;
	}

	// Like ODER Like&Boolean verwendet wird
	if ($arr['mysql']['like']) {
		// Ladet Liste nach jeder Veränderung im INPUT-Search-Field NEU
		$jquery .= "
		$( '#input_search$list_id' ).on('input', function( event ) {
			call_semantic_table('$list_id','input_search','',$('#input_search$list_id').val());
		});";
	} elseif ($arr['mysql']['match']) {
		// Wenn Boolean verwendet wird
		// Ladet Liste nach jeder Veränderung im INPUT-Search-Field NEU
		$jquery .= "
		$( '#input_search$list_id' ).on('change', function( event ) {
		call_semantic_table('$list_id','input_search','',$('#input_search$list_id').val());
		});
		$( '#input_search$list_id' ).on('input', function( event ) {
		if ($('#input_search$list_id').val() == '') call_semantic_table('$list_id','input_search','',$('#input_search$list_id').val());
		})
		";
	}

	if (is_array($arr['js'])) {
		foreach ($arr['js'] as $array_js) {
			if ($array_js['text'])
				$add_js_text .= $array_js['text'];
			if ($array_js['src'])
				$add_js_src .= "<script type=\"text/javascript\" src=\"{$array_js['src']}\"></script>";
		}
	}

	if (is_array($arr['checkbox'])) {
		$add_checkbox_js = "check_change_checkbox('$list_id');";
	}

	$js .= "
	<script type=\"text/javascript\">
			$(document).ready(function() {
				call_share_link('$list_id');
				$add_checkbox_js
				$jquery
                $('.ui.flyout').flyout({});
				$('.tooltip').popup();
						
				
			});
			$add_js_text
	</script>";
	$js .= $add_js_src;
	if ($arr['list']['hover']) {
		if ($arr['list']['hover'] === true)
			$arr['list']['hover'] = '#eee';
		$output_array['html'] = '<style>.hover_tr_' . $list_id . ':hover td { background-color: ' . $arr['list']['hover'] . '; }</style>';
	}

	$output_array['html'] .= "<div align='$list_align'><div style='max-width:$list_width;'>";

	/**
	 * ********************************************************
	 * Suchfeld + Button (TOP)
	 * ********************************************************
	 */
	if ($arr['mysql']['like'] or $arr['mysql']['match']) {
		// if ($arr['mysql']['like'])
		// $onchange = "onkeydown=\"call_semantic_table('$list_id','input_search','',$('#input_search$list_id').val());\"";
		$head_search = "<input $onchange id = 'input_search$list_id' value='{$GLOBALS['input_search']}' placeholder='Suchbegriff' type='text'><i class='search icon'></i>";
		// $output_array['html'] .= "<div class='ui icon input'><i class='inverted circular search link icon'></i><input id = 'input_search$list_id' class='' type='text' value='$input_search' placeholder='Suchbegriff'></div>";
		$jquery .= "$('#input_search$list_id').focus(); ";
	}
	if ($arr['mysql']['match'])
		$head_search .= "&nbsp;<button class='ui icon button' onclick=$('#input_search$list_id').click >Suchen</button>";

	if ($arr['top'])

		$head_button .= buttons('', $arr, 'top');

	if ($arr['top']['buttons']['align'])
		$buttons_float = $arr['top']['buttons']['align'];
	else
		$buttons_float = 'right';

	if ($head_search and !$head_button) {
		$output_array['html'] .= "<div class='ui form'><div class='ui {$arr['search']['class']} $list_size icon input focus'>$head_search</div></div><br><hr>";
	} elseif ($head_search or $head_button) {
		$output_array['html'] .= "<div style='float:left'><div class='ui form'><div class='ui $list_size icon input focus'>$head_search</div></div></div>";
		$output_array['html'] .= "<div style='float:$buttons_float'>$head_button</div>";
		$output_array['html'] .= "<div style='clear:both'></div>";
	}

	/**
	 * *******************************************************
	 */
	$output_array['html'] .= $output_table;

	if ($_SESSION['export']['field']) {

		if ($arr['smartFormRootPath'])
			$smartFormRootPath = $arr['smartFormRootPath'];
		else
			$smartFormRootPath = '/smart_form';

		// Vollständige URL für den Export-Button erstellen
		$export_url = $smartFormRootPath . "/plugin/export.php?list_id=" . $list_id;

		// Button für den Export erzeugen
		$button_export = "<a href='" . $export_url . "'>[ Export ]</a><br><br>";
	}

	$output_array['html'] .= $button_export;
	$output_array['html'] .= "</div></div>";
	$output_array['html'] .= "<div class='loader'></div>";
	$output_array['html'] .= $modul_modal;

	$output_array['flyout'] .= $modul_flyout;

	$output_array['js'] = $js_list;
	$output_array['js'] = $js;

	return $output_array;
}

include (__DIR__ . '/include/list/functions.php');
include (__DIR__ . '/include/smart_functions.php');
