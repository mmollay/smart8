<?php
/**
 * *************************************************************
 * update: 10.09.2020 mm@ssi.at
 * $array => $var von der Datenbank - wird für Filter verwendet
 * *************************************************************
 */

// Muss bei der SQL -Query Am Anfang angeführt sein
$id = $array[0] ?? 1;

$serial_nr = 0;
$count_th++;
$output_template = $output_template ?? '';

if (isset($arr['list']['template'])) {
	/**
	 * ******************************************************************
	 * Template {title}<br>{text}
	 * ******************************************************************
	 */
	$output_template .= temp_replace($arr['list']['template'], $array);
} else {
	/**
	 * ******************************************************************
	 * Output as a Table
	 * ******************************************************************
	 */

	$button = buttons2($id, $arr, 'tr', $array);

	// $arr ['checkbox'] ['title'] = $arr ['checkbox'] ['title'] ?? '';
	// $arr ['checkbox'] ['align'] = $arr ['checkbox'] ['align'] ?? '';
	$list_td .= "<tr class='hover_tr_$list_id' id='tr_$id'>";

	/**
	 * *****************************************************************
	 * Output - Checkbox for multiselect
	 * *****************************************************************
	 */

	if (is_array($arr['checkbox'])) {



		if (isset($arr['checkbox']['title'])) {
			$checkbox_add_class = '';
			$checkbox_title = temp_replace($arr['checkbox']['label'], $array);
		} else {
			$checkbox_add_class = 'fitted';
			$checkbox_title = '';
		}

		if (isset($arr['checkbox']['align']))
			$checkbox_add_tb_class = "class='" . $arr['checkbox']['align'] . " aligned'";
		else
			$checkbox_add_tb_class = '';

		// Checkbox
		$list_td .= "<td $checkbox_add_tb_class ><div class='ui child checkbox $checkbox_add_class'><input class='checkbox-{$arr['list']['id']}' value='$id' type='checkbox' name='type'><label>$checkbox_title</label></div></td>";
	}

	if ($button['left'])
		$list_td .= $button['left'];

	if ($serial == true) {
		$serial_nr = ++$nr_count;
		$list_td .= "<td>" . ($serial_nr + $limit_pos) . "</td>";
	}

	/**
	 * *************************************************************
	 * Ab Hier <TD> erzeugt
	 * ************************************************************
	 */
	foreach ($arr['th'] as $key => $value) {
		$class = $value['class'] ?? '';
		$align = $value['align'] ?? '';
		$format = $value['format'] ?? '';
		$dataType = $value['dataType'] ?? '';
		$colspan = $value['colspan'] ?? '';
		$gallery = $value['gallery'] ?? '';
		$nowrap = $value['nowrap'] ?? '';

		if ($align)
			$class .= " $align aligned ";



		if ($format)
			$array[$key] = formatCurrency($array[$key], $format);

		if (isset($array[$key])) {
			$array[$key] = preg_replace("/\{id\}/", $id, $array[$key]);
		}

		if ($nowrap)
			$array[$key] = "<span style='white-space: nowrap';>" . $array[$key] . "</span>";
		else
			$array[$key] = text_output($array[$key]);

		/**
		 * ************************************************************
		 * Abfrage ob Span für table_field gemacht werden soll
		 * ***********************************************************
		 */

		$col = ($colspan && !$col) ? fu_span($colspan, $array) : '';

		$count_col = (isset($count_col)) ? ++$count_col : null;

		$add_span_td = ($col && !$count_col) ? " colspan='$col' " : '';

		$td_show = !($count_col && !$add_span_td);

		if ($td_show) {
			$td_href = $arr['th'][$key]['href'] ?? '';
			$th_replace = $arr['th'][$key]['replace'] ?? '';
			$th_gallery = $arr['th'][$key]['gallery'] ?? '';
			$th_modal = $arr['th'][$key]['modal'] ?? '';

			// Umwandeln in eine Gallery
			if (is_array($th_gallery)) {
				foreach ($th_gallery as $key_gallery => $value_gallery) {
					$array_gallery[$key_gallery] = fu_call_value($array[$key], array('default' => $value_gallery));
				}
				$array_gallery['id'] = $id;
				$field_value = fu_call_gallery($array_gallery);
			} else {
				// Austauschen eines Wertes
				$field_value = fu_call_value($array[$key], $th_replace);

				if (isset($field_value)) {
					$field_value = preg_replace("/\{id\}/", $id, $field_value);
				}
			}

			$list_td .= "<td class=' $class' $add_span_td >";

			/**
			 * **********************************
			 * Clickable Content open the Modal
			 * mm@ssi.at 10.09.2020
			 * **********************************
			 */

			if (is_array($th_modal)) {
				$modal_id = $th_modal['id'];
				$modal_popup = $th_modal['popup'] ?? '';
				$modal_popup = temp_replace($modal_popup, $array);
				$modal_onclick = $th_modal['onclick'] ?? '';
				$modal_onclick = temp_replace($modal_onclick, $array);
			} else {
				$modal_id = $th_modal;
				$modal_onclick = '';
			}

			if ($modal_id) {
				$url = $_SESSION['workpath'] . "/" . $arr['modal'][$modal_id]['url'];
				$url = preg_replace("/{id}/", $id, $url);
				$onclick = "call_semantic_form('$id','$modal_id','$url','{$arr['list']['id']}','{$arr['modal'][$key]['focus']}');";
			} else {
				$onclick = '';
			}

			// Change template {title}
			$td_href = temp_replace($td_href, $array);

			if ($td_href or ($onclick or $modal_onclick)) {
				if ($onclick or $modal_onclick)
					$onclick = "onclick=\"$onclick $modal_onclick\" ";

				if ($td_href)
					$href = "href='$td_href'";

				if ($modal_popup)
					$modal_popup = "data-content = '$modal_popup' ";

				$list_td .= "<a $href $onclick style='display: block; cursor:pointer;' class='ui tooltip' $modal_popup>";
			}

			$list_td .= $field_value;

			if ($td_href)
				$list_td .= "</a>";

			$onclick = '';
			$td_href = '';
			$modal_popup = '';

			/**
			 * ************************************
			 * ** END - Clickable Content Modal ***
			 * ************************************
			 */

			$list_td .= "</td>";
		}

		$td_href = '';
		$add_span_td = '';

		if ($col && $col == $count_col) {
			$col = '';
			$count_col = '';
		}
	}
	if (isset($button['right'])) {
		$list_td .= $button['right'];
	}
	$list_td .= "</tr>";
}