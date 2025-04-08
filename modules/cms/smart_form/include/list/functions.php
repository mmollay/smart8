<?

/*
 * Generate Buttons
 * TOP
 */
function buttons($id, $arr, $pos)
{
	if (!isset($arr[$pos]['button']) || !is_array($arr[$pos]['button']))
		return;

	$button = '';
	$button_separat = '';
	$list_size = $arr['list']['size'] ?? '';
	$list_class = $arr['list']['class'] ?? '';
	$list_style = $arr['list']['style'] ?? '';
	$list_id = $arr['list']['id'];

	// Default
	if (isset($arr[$pos]['buttons']['groups'])) {
		$buttons_class = $arr[$pos]['buttons']['class'];
		$button .= "<div class='buttons $buttons_class icon ui'>";
	}

	foreach ($arr[$pos]['button'] as $key => $value) {

		$icon = $value['icon'] ?? '';
		$id = $value['id'] ?? '';
		$title = $value['title'] ?? '';
		$href = $value['href'] ?? '';
		$target = $value['target'] ?? '';
		$filter = $value['filter'] ?? '';
		$single = $value['single'] ?? '';
		$popup = $value['popup'] ?? '';
		$class = $value['class'] ?? '';
		$onclick2 = $value['onclick'] ?? '';
		$onclick = '';
		if ($onclick2) {
			$onclick2 = preg_replace("/{id}/", $id, $onclick2);
		}

		$arr['modal'][$key]['focus'] = $arr['modal'][$key]['focus'] ?? '';

		if ($key) {
			if (isset($arr['flyout'][$key]['url'])) {
				$url = $_SESSION['workpath'] . "/" . $arr['flyout'][$key]['url'];
				// $url = preg_replace ( "/\/\//", "/", $url );
				$url = preg_replace("/{id}/", $id, $url);
				$onclick = "call_semantic_form_flyout('$id','$key','$url','{$list_id}','{$arr['flyout'][$key]['focus']}');";
			} elseif (isset($arr['modal'][$key]['url'])) {
				$url = $_SESSION['workpath'] . "/" . $arr['modal'][$key]['url'];
				// $url = preg_replace ( "/\/\//", "/", $url );
				$url = preg_replace("/{id}/", $id, $url);
				$onclick = "call_semantic_form('$id','$key','$url','{$list_id}','{$arr['modal'][$key]['focus']}');";
			}
		}

		if (!$key) {
			$field_id++;
			$key = 'button-' . $field_id;
		}

		if ($icon)
			$icon = "<i class='icon $icon'></i> ";

		$title = "<span class='smart_form_button_top'>" . $title . "</span>";

		if ($filter) {
			$query = $GLOBALS['mysqli']->query($filter) or die(mysqli_error($GLOBALS['mysqli']));
			$count = mysqli_num_rows($query);
		} else
			$count = 1;

		if ($popup)
			$popup = "data-content = '$popup' ";

		if (isset($onclick) or isset($onclick2))
			$set_button = "<button id = '$id' class='ui button icon $list_size $class' onclick=\"$onclick $onclick2\" $popup >$icon$title</button>";
		else {
			if ($target)
				$set_target = "target='$target'";

			if ($href)
				$set_button = "<a id = '$id' class='ui tooltip button icon $list_size $class' href='$href' $set_target $popup >$icon$title</a>";
			else
				$set_button = "<button id = '$id' class='ui button icon $list_size $class' $popup >$icon$title</button>";

			$set_target = '';
		}

		if (!$single)
			$button .= $set_button;
		else
			$button_separat = $set_button;
	}

	if (isset($arr[$pos]['buttons']['groups']))
		$button .= "</div>";

	return $button . $button_separat;
}

// Next generation with "position"
// LIST
function buttons2($id, $arr, $pos, $array)
{
	if (!is_array($arr[$pos]['buttons']['left']) and !is_array($arr[$pos]['buttons']['right'])) {
		$arr[$pos]['buttons']['left'] = $arr[$pos]['buttons'];
		$arr[$pos]['button']['left'] = $arr[$pos]['button'];
		$set_position = 'left';
	}

	foreach ($arr[$pos]['buttons'] as $position => $value) {
		if (isset($set_position))
			$position = $set_position;
		$buttons_class = $arr[$pos]['buttons'][$position]['class'];
		$td_class = $arr[$pos]['buttons'][$position]['td_class'] ?? '';

		if (is_array($arr[$pos]['buttons'][$position])) {

			// Rechbündig wenn Buttons rechts angeordnet sind
			if ($position == 'right')
				$td_class2 = "right aligned";
			else
				$td_class2 = '';

			$array[$position] = "<td class='$td_class $td_class2' nowrap><div class='buttons $buttons_class icon ui'>";

			foreach ($arr[$pos]['button'][$position] as $key => $value) {
				if (isset($arr[$pos]['button'][$position][$key]['filter'])) {
					$show_button = fu_filter($arr[$pos]['button'][$position][$key]['filter'], $array);
				} else
					$show_button = 1; // Defaultmässig wird der Button immer gezeigt

				// Wird wenn Filter gesetzt ist nicht angezeigt
				if ($show_button) {

					$title = $value['title'] ?? '';
					$class = $value['class'] ?? '';
					$onclick2 = $value['onclick'] ?? '';
					$download = $value['download'] ?? '';
					$href = $value['href'] ?? '';
					$single = $value['single'] ?? '';
					$icons = $value['icons'] ?? '';
					$icon = $value['icon'] ?? '';
					$icon_corner = $value['icon_corner'] ?? '';

					if ($icon_corner) {
						$icon = "<i class='$icons icons'><i class='$icon icon'></i><i class='corner $icon_corner icon'></i></i>";
					} elseif ($icon)
						$icon = "<i class='icon $icon'></i>";

					$GLOBALS['array'] = $array;

					if ($onclick2) {
						// bei ID
						$onclick2 = preg_replace("/{id}/", $id, $onclick2);
						// bei restlichen Werten aus der Datenbank
						// $onclick2 = preg_replace ( '!{(.*?)}!e', '$array[ \1 ]', $onclick2 );
						$onclick2 = preg_replace_callback('!{(.*?)}!', function ($matches) {
							$array = $GLOBALS['array'];
							return $array[$matches[1]];
						}, $onclick2);
					}

					if ($download) {
						// $download = preg_replace ( '!{(.*?)}!e', '$array[ \1 ]', $download );

						$download = preg_replace_callback('!{(.*?)}!', function ($matches) {
							$array = $GLOBALS['array'];
							return $array[$matches[1]];
						}, $download);

						$download = "download = '$download' ";
					}

					if ($href) {
						// $href = preg_replace ( '!{(.*?)}!e', '$array[ \1 ]', $href );
						$href = preg_replace_callback('!{(.*?)}!', function ($matches) {
							$array = $GLOBALS['array'];
							return $array[$matches[1]];
						}, $href);
						$href = "href = '$href' target=_new ";
					}

					if ($key) {
						$url = '';

						if (isset($arr['modal'][$key]['url'])) {
							$url = $_SESSION['workpath'] . "/" . $arr['modal'][$key]['url'];
							$type = 'modal';
						} elseif (isset($arr['flyout'][$key]['url'])) {
							$url = $_SESSION['workpath'] . "/" . $arr['flyout'][$key]['url'];
							$type = 'flyout';
						}

						if ($url) {
							$url = preg_replace("/{id}/", $id, $url);

							if ($type === 'modal') {
								$onclick = "call_semantic_form('$id','$key','$url','{$arr['list']['id']}','" . ($arr['modal'][$key]['focus'] ?? '') . "'); ";
							} elseif ($type === 'flyout') {
								$onclick = "call_semantic_form_flyout('$id','$key','$url','{$arr['list']['id']}','" . ($arr['flyout'][$key]['focus'] ?? '') . "'); ";
							}
						} else {
							$onclick = '';
						}
					}

					$popup = $value['popup'];

					if ($popup)
						$popup = "data-content = '$popup' ";

					if ($onclick or $onclick2)
						$onclick = "onclick=\"$onclick $onclick2\"";
					$button_set = "<a $href class='ui button icon $class $key tooltip' id ='$id' $download $onclick $popup >$icon$title</a>";

					if (!$single)
						$array[$position] .= $button_set;
					else
						$array_single[$position] .= " $button_set";
				}
			}

			if (is_array($arr[$pos]['buttons'][$position])) {
				$array_single[$position] = $array_single[$position] ?? '';
				$array[$position] .= "</div>" . $array_single[$position] . "</div>";
			}
		}
	}
	return $array;
}

/**
 * ******************************************************
 * RowSpan für tabellen spalten
 * ******************************************************
 */
function fu_span($filter, $aRow)
{
	$ii = '';
	if (!is_array($filter))
		return;

	foreach ($filter as $array) {
		// $filter = $filter[0]
		$ii++;
		$filter_value = $array['value'];
		$filter_field = $array['field'] ?? '';
		$filter_field2 = $array['field2'] ?? '';
		$filter_operator = $array['operator'] ?? '';
		$filter_link[$ii] = $array['link'] ?? '';

		// Aktueller Tag
		if ($filter_value === 'NOW')
			$filter_value = date('Y-m-d');

		// Wenn Feld zu Feld verglichen werden soll
		if ($filter_field2)
			$filter_value = $aRow[$filter_field2];

		if ($filter_operator == '==' && $aRow[$filter_field] == $filter_value) {
			$value[$ii] = 1;
		} elseif ($filter_operator == '!=' && $aRow[$filter_field] != $filter_value)
			$value[$ii] = 1;
		elseif ($filter_operator == '>' && $aRow[$filter_field] > $filter_value)
			$value[$ii] = 1;
		elseif ($filter_operator == '>=' && $aRow[$filter_field] >= $filter_value)
			$value[$ii] = 1;
		elseif ($filter_operator == '<' && $aRow[$filter_field] < $filter_value)
			$value[$ii] = 1;
		elseif ($filter_operator == '<=' && $aRow[$filter_field] <= $filter_value)
			$value[$ii] = 1;
	}

	// Bei OR Verknüpfnug
	if ($filter_link[2] == 'or') {
		if ($value[1] or $value[2])
			return $filter[col];
		// Bei AND Verknüpfung
	} elseif ($filter_link[2] == 'and') {
		if ($value[1] and $value[2])
			return $filter[col];
	} elseif ($value[1])
		return $filter[col];
}

/**
 * ******************************************************
 * FIlTER für Buttons (Für anzeigen oder ausblenden)
 * ******************************************************
 */
function fu_filter($filter, $aRow)
{
	$value = [];

	foreach ($filter as $array) {
		$filter_value = $array['value'];
		$filter_field = $array['field'] ?? '';
		$filter_field2 = $array['field2'] ?? '';
		$filter_operator = $array['operator'] ?? '';
		$filter_link[] = $array['link'] ?? '';

		// Aktueller Tag
		if ($filter_value === 'NOW')
			$filter_value = date('Y-m-d');

		// Wenn Feld zu Feld verglichen werden soll
		if ($filter_field2)
			$filter_value = $aRow[$filter_field2];

		switch ($filter_operator) {
			case '==':
				$value[] = ($aRow[$filter_field] == $filter_value) ? 1 : 0;
				break;
			case '!=':
				$value[] = ($aRow[$filter_field] != $filter_value) ? 1 : 0;
				break;
			case '>':
				$value[] = ($aRow[$filter_field] > $filter_value) ? 1 : 0;
				break;
			case '>=':
				$value[] = ($aRow[$filter_field] >= $filter_value) ? 1 : 0;
				break;
			case '<':
				$value[] = ($aRow[$filter_field] < $filter_value) ? 1 : 0;
				break;
			case '<=':
				$value[] = ($aRow[$filter_field] <= $filter_value) ? 1 : 0;
				break;
			default:
				$value[] = 0;
		}
	}

	if (isset($filter_link[1]) && isset($filter_link[2])) {
		if ($filter_link[2] === 'or') {
			if ($value[0] || $value[1])
				return 1;
		} elseif ($filter_link[2] === 'and') {
			if ($value[0] && $value[1])
				return 1;
		}
	}

	return $value[0] ?? null;
}

/**
 * ******************************************************
 * FILTER-LEISTE (DROPDOWN, BUTTONS)
 * ******************************************************
 */

function call_filter($array, $type = 'select', $color = false)
{
	$list_id = $array['list_id'];
	$id = $array['id'];
	$var = $array['var'] ?? '';
	$placeholder = $array['placeholder'] ?? '';
	$setting = $array['setting'] ?? '';
	$filter_key = "filter_$id";
	$value = $array['value'];
	$default_value = $array['default_value'] ?? '';
	$class = $array['class'] ?? '';
	$query = $array['query'] ?? '';
	$list_para = $array['list_para'] ?? '';
	$list_size = $list_para['size'] ?? '';

	if ($array['table'] > '')
		$table = $array['table'] . ".";
	else
		$table = '';

	$filter_value = $_SESSION["filter"][$list_id][$id] ?? null;

	if ($value && !isset($filter_value)) {
		$filter_value = $value;
	}

	/**
	 * *********************************************************************
	 * BUTTON - LEISTE
	 * *********************************************************************
	 */
	if ($type == 'button') {
		foreach ($var as $key => $value) {

			// Wenn eine Defaultwert gesetzt wurde
			if (!$filter_value && $default_value) {
				$filter_value = $default_value;
			}

			if ($key == $filter_value)
				$add_class = 'active';
			else
				$add_class = '';
			$onclick = "onclick = \"call_semantic_table('$list_id','filter','$id','$key'); $('.filter_button_$list_id').removeClass('active'); $('.filter_button_$list_id#$key').addClass('active'); \" ";
			$array_output['html'] .= "<button id='$key' class='ui button $class $add_class filter_button_$list_id' $onclick>$value</button>";
		}
	} /**
	  * *********************************************************************
	  * SELECT
	  * *********************************************************************
	  */ else {
		$class = 'button basic search';
		$color_class = '';
		$group_a = '';


		if (!isset($filter_value)) {
			if ($default_value)
				$filter_value = $default_value;
			else
				$filter_value = 'all';
		} else {
			if ($filter_value != 'all')
				$color_class = $color;
		}

		// Wenn eine Defaultwert gesetzt wurde
		if (!$filter_value && $default_value) {
			$filter_value = $default_value;
		}

		// Wenn es einen Defaultwert gibt, gibt es keinen Platzhalter
		if (!isset($value)) {
			if (!$placeholder)
				$placeholder = "--Bitte wählen--";
			$group_a .= "<div class='item' data-value='all' >$placeholder</div>";
		}

		if (is_array($var)) {
			foreach ($var as $key => $value) {
				$group_a .= "<div class='item' data-value='$key' >$value</div>";
			}
		}

		$array_output['html'] = "
		<div class='$color_class ui $list_size dropdown $class $list_id' id='$filter_key'>
		<input name='$filter_key' type='hidden' value='$filter_value'>
		<div class='default text'>$placeholder</div>
		<i class='dropdown icon'></i>
		<div class='menu'>$group_a</div>
		</div>
		";
		// wird direkt geladen, damit der Refresh (und auch Autorfresh funktioniert
		$array_output['html'] .= "
		<script type=\"text/javascript\">
			$(document).ready(function() {
			$('#$filter_key').dropdown({ fullTextSearch : true, onChange(value, text) { call_semantic_table('$list_id','filter','$id',value); }, " . $setting . "});
			});
		</script>
		";
	}

	// klomplexe Abfragen bsp.: DATE_FORMAT(date_create,'%Y')
	if (isset($filter_value) and $filter_value != 'all') {

		if ($query) {
			if (preg_match("/{value}/", $query)) {
				// Ausgabe wenn gesamter Parameter übergeben wird "platzhalter {value}
				$query = preg_replace("/{value}/", $filter_value, $query);
				if ($query) {
					if (strpos($query, 'having') !== false) {
						$array_output['having'] = " $query";
					} else {
						$array_output['mysql'] = " AND $query";
					}
				}
			} else
				// klassisch
				$array_output['mysql'] = " AND $query $field_opterator '$filter_value ' ";
		} else
			// alter Varian

			$array_output['mysql'] = " AND $table$id = '$filter_value ' ";

		// echo $array_output['mysql'];
	}

	$class = '';

	return $array_output;
}

/*
 * Entfernt Zeilenumbrüche, Breaks und Kommentare
 */
function compress_html($alt)
{
	$check = true;
	do {

		$pos1 = strpos($alt, '<!--');
		$pos2 = strpos($alt, '-->', $pos1);

		if ($pos1 === false || $pos2 === false) {
			$check = false;
		} else {
			$alt = substr($alt, 0, $pos1) . substr($alt, $pos2 + 3);
		}
	} while ($check);

	$neu = $alt;
	$changed = false;

	do {

		$changed = false;
		if (($tmp = str_replace('  ', ' ', $neu)) != $neu) {
			$neu = $tmp;
			$changed = true;
		}
	} while ($changed);

	$neu = str_replace("\r\n", '', $neu);
	$neu = str_replace("\n", '', $neu);
	return $neu;
}
function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float) $usec + (float) $sec);
}

// Ersetzen eines Wertes durch einene anderen Wert Bsp. 1 wird <i class='icon red eye'></i>
function fu_call_value($value, $array)
{
	if (!$array)
		return $value;

	foreach ($array as $key => $value2) {

		if ($key == $value) {

			if (preg_match("/{value}/", $value2)) {
				// Ausgabe wenn gesamter Parameter übergeben wird "platzhalter {value}
				$value2 = preg_replace("/{value}/", $value, $value2);
			}

			return $field_value = $value2;
		} else if (preg_match("/>/", $key)) {
			// if value bigger then...
			$split_value = explode(">", $key);
			$value2 = preg_replace("/{value}/", $split_value[1], $value2);
			if ($value > $split_value[1])
				return $field_value = $value2;
		} else if (preg_match("/>/", $key)) {
			// if value smaller then...
			$split_value = explode("<", $key);
			$value2 = preg_replace("/{value}/", $split_value[1], $value2);
			if ($value < $split_value[1])
				return $field_value = $value2;
		}
	}

	if ($array['default']) {
		$value2 = $array['default'];
		if (preg_match("/{value}/", $array['default'])) {

			// Ausgabe wenn gesamter Parameter übergeben wird "platzhalter {value}
			$value2 = preg_replace("/{value}/", $value, $array['default']);
		}
		return $field_value = $value2;
	}

	return $value;
}

/**
 * ********************************************************
 * Auto RELOAD
 * ********************************************************
 */
function list_auto_reload($list_id, $value)
{
	if ($value) {
		if (is_array($value)) {
			$label = $value['label'] ?? '';
			$checked = $value['checked'] ?? '';
			$loader = $value['loader'] ?? '';
		}

		if ($checked === FALSE)
			$checked = '';
		else
			$checked = 'checked';
		if (!$label)
			$label = 'Auto Reload';

		if ($loader === FALSE) {
			$style = 'visibility:hidden; overflow:hidden; position:absolute;';
		}

		$auto_reload = "<input class='reload_loader' id='$list_id' type='hidden' value='1'>";
		$auto_reload .= "<div style='float:right; $style' >";
		$auto_reload .= "<div class='ui toggle checkbox'>";
		$auto_reload .= "<input class='reload_table' id='$list_id' $checked type='checkbox'>";
		$auto_reload .= "<label>$label</label>";
		$auto_reload .= "</div> ";
		$auto_reload .= "</div> ";

		if ($loader !== FALSE) {
			$auto_reload .= "<br>";
		}

		return $auto_reload;
	}
}

/**
 * ********************************************************
 * strip_tags, hightlight
 * ********************************************************
 */
function text_output($value)
{
	$searchSettings = $GLOBALS['arr']['search'] ?? '';
	$inputSearch = $GLOBALS['input_search'] ?? '';

	if (isset($searchSettings['strip_tags'])) {
		$allowableTags = ($searchSettings['strip_tags'] === true) ? '' : $searchSettings['strip_tags'];

		$value = nl2br($value);
		$value = preg_replace('#<br />(\s*<br />)+#', '<br />', $value);
		$value = preg_replace("/<br \/>/", " ", $value);
		$value = strip_tags($value, $allowableTags);
	}

	if ($inputSearch && isset($searchSettings['hightlight'])) {
		$value = preg_replace("/{$inputSearch}\w*/i", "<b>$0</b>", $value);
		// $value = preg_replace("/\w*?{$inputSearch}\w*/i", "<b>$0</b>", $value);
	}

	return $value;
}

/**
 * **********************************************************
 * gallery
 * **********************************************************
 */
function fu_call_gallery($array)
{
	if (!$array['document_root'])
		$array['document_root'] = $_SERVER["DOCUMENT_ROOT"];

	// $images = glob ( $_SERVER["DOCUMENT_ROOT"].$array['img_path'] . "/*.{jpg,png,bmp}" ,GLOB_BRACE );
	$images = glob($array['document_root'] . $array['img_path_thumb'] . "/*.{jpg,png,bmp}", GLOB_BRACE);
	foreach ($images as $image) {
		$image_thumb = $image = preg_replace("[{$array['document_root']}]", '/', $image);
		$image = preg_replace("[thumbnail/]", '', $image);
		$output .= "<a href='$image' data-fancybox='gallery{$array['id']}' >$image<img class='ui image tooltip' src='$image_thumb' title='Bild vergrößern'></a>";
	}
	if (!$output)
		$output = "<img class='ui image' src='../ssi_smart/smart_form/img/image.png'>";
	return "<div class='ui small rounded images'>" . $output . "</div>";
}

/**
 * ****************************************************************
 * CHANGE {value} form Tables
 * ****************************************************************
 */
function temp_replace($value, $array)
{
	if (!is_string($value)) {
		// Falls $value kein gültiger String ist, gib ihn unverändert zurück
		return $value;
	}

	preg_match_all('/{(.*?)}/', $value, $matches);

	foreach ($matches[1] as $key) {
		if (isset($array[$key])) {
			$value = str_replace('{' . $key . '}', text_output($array[$key]), $value);
		}
	}

	return $value;
}

// Get header
// tr_top th_top
function get_th($arr, $position = false)
{
	$output = '';
	$set_position = $position ? "_" . $position : '';

	$tr_style = $arr["tr$set_position"]['style'] ?? '';
	$tr_align = $arr["tr$set_position"]['align'] ?? '';

	$outupt_head = "<tr>"; // class='$tr_class' //wird nicht unterstützt

	if (is_array($arr['checkbox']) && !$position) {

		$arr['checkbox']['title'] = $arr['checkbox']['title'] ?? '';

		if (isset($arr['checkbox']['align']))
			$checkbox_add_tb_class = "class='" . $arr['checkbox']['align'] . " aligned'";
		else
			$checkbox_add_tb_class = '';

		// $output_head .= "<th><div class='item'><div class='ui master checkbox'><input type='checkbox' name='fruits'><label></label></div></th>";
		$output .= "<th $checkbox_add_tb_class><div class='ui master checkbox'><input class='checkbox-main-{$arr['list']['id']}' type='checkbox' name='type'><label>" . $arr['checkbox']['title'] . "</label></div></th>";

		/**
		 * **** END - Checkbox *********************************************
		 */
	}

	if (is_array($arr['tr']['button']['left']) && !$position)
		$output .= "<th style='$tr_style'></th>";

	if (isset($arr['list']['serial']) && !$position) // Ausgabe eines Nummerkreislaufes - Title
		$output .= "<th style='$tr_style' >Nr.</th>";

	foreach ($arr['th' . $set_position] as $key => $value) {

		extract($value);
		$title = $value['title'];
		// if ($title)
		// $show_th = true;
		$colspan = $value['colspan'] ?? '';
		$width = $value['width'] ?? '';
		$class = $value['class'] ?? '';
		$align = $value['align'] ?? '';
		$tooltip = $value['tooltip'] ?? '';
		$info = $value['info'] ?? '';
		$style = $value['style'] ?? '';

		$th_style = $width ? "width:$width;" : '';

		if ($colspan)
			$colspan = "colspan='$colspan' ";

		if ($align or $tr_align) {
			if ($tr_align)
				$align = $tr_align;
			$class .= " $align aligned ";
		}

		if ($tooltip or $info) {
			if ($info)
				$tooltip = $info;
			$str_tooltip = "title='$tooltip'";
		} else
			$str_tooltip = '';

		$output .= "<th style='$th_style $style $tr_style' class='$class tooltip' $str_tooltip $colspan >$title</th>";
	}

	if ($arr['tr']['button']['right'] && !$position)
		$output .= "<th></th>";

	$output .= "</tr>";
	return $output;
}


function formatCurrency($value, $format)
{
	// Bestimme, ob der Wert negativ ist
	$isNegative = $value < 0;

	// Absolutwert für die Formatierung verwenden
	$absoluteValue = abs($value);

	// Grundformatierung mit Punkt als Dezimaltrennzeichen und Komma für Tausendertrennung
	$formattedValue = number_format($absoluteValue, 2, ".", ",");

	// Grundlegende Farbzuweisungen
	$colorClass = $isNegative ? 'ui red text' : ''; // Standardmäßig rot für negative Werte
	$prefix = ''; // Standardmäßig kein Prefix
	$suffix = ''; // Standardmäßig kein Suffix

	switch ($format) {
		case 'dollar':
		case 'euro':
			$prefix = $format === 'dollar' ? "$" : "€";
			break;

		case 'dollar_redblue':
		case 'euro_redblue':
		case 'number_redblue':
			$colorClass = $isNegative ? 'ui red text' : 'ui blue text';
			$prefix = $format === 'dollar_redblue' ? "$" : ($format === 'euro_redblue' ? "€" : "");
			break;

		case 'dollar_redgreen':
		case 'euro_redgreen':
		case 'number_redgreen':
			$colorClass = $isNegative ? 'ui red text' : 'ui green text';
			$prefix = $format === 'dollar_redgreen' ? "$" : ($format === 'euro_redgreen' ? "€" : "");
			break;

		case 'dollar_redblack':
		case 'euro_redblack':
		case 'number_redblack':
			$colorClass = $isNegative ? 'ui red text' : 'ui black text';
			$prefix = $format === 'dollar_redblack' ? "$" : ($format === 'euro_redblack' ? "€" : "");
			break;

		case 'dollar_color':
		case 'euro_color':
		case 'number_color':
			// Speziell für *_color Formate: nur das Minus ist rot
			$colorClass = $isNegative ? 'ui red text' : '';
			$prefix = $format === 'dollar_color' ? "$" : ($format === 'euro_color' ? "€" : "");
			break;

		case 'number':
			// Keine Farbe, kein Währungssymbol
			$colorClass = '';
			break;

		default:
			$formattedValue .= " $format";
			break;
	}

	// Zusammensetzen des formatierten Strings
	$formattedValue = $prefix . $formattedValue . $suffix;

	// Farbliche Darstellung und Anzeigen des Minuszeichens, falls nötig
	if ($colorClass) {
		return "<span class='$colorClass'>" . ($isNegative ? "-" : "") . $formattedValue . "</span>";
	} else {
		return ($isNegative ? "-" : "") . $formattedValue;
	}
}
