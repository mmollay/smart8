<?php

// <?php

// mm 10.10.2023
// Erzeugt für List und Form ein oder mehrere Modal-Elemente oder Flyout-Elemente
function generate_element($arr, $element_type) {
	$output = '';
	// echo "<pre>";
	// print_r ( $arr );
	// echo "</pre>";
	foreach ( $arr as $element_key => $element_value ) {
		$element_title = $element_value ['title'] ?? '';
		$element_close_hide = $element_value ['close_hide'] ?? '';
		$element_class = $element_value ['class'] ?? '';
		$element_content = $element_value ['content'] ?? '';
		$element_zindex = $element_value ['zindex'] ?? '';
		$element_button = $element_value ['button'] ?? '';

		if ($element_zindex)
			$zindex = "style='z-index:$element_zindex'";
		else
			$zindex = '';

		if (preg_match ( "/scrolling/", $element_class ))
			$add_class_content = 'scrolling';
		else
			$add_class_content = '';

		$output .= "<div $zindex id='$element_key' class='ui $element_type $element_class'>";
		if (! $element_close_hide)
			$output .= "<i class='close icon'></i>";
		$output .= "<div class='ui header'>$element_title </div>";
		$output .= "<div class='content $add_class_content'>";
		$output .= $element_content;
		$output .= "<input type='hidden' id='hiddenVariable' value=''>";
		$output .= "</div>";
		if ($element_button) {
			$output .= "<div class='actions'>";
			foreach ( $element_button as $button_key => $button_array ) {
				$button_class = $button_array ['class'] ?? '';
				$form_id = $button_array ['form_id'] ?? '';
				$button_onclick = $button_array ['onclick'] ?? '';

				if ($form_id) {
					$onclick = "$('#$form_id') . submit ();";
				}

				if ($onclick or $button_array ['onclick'])
					$set_onclick = "onclick=\"$onclick $button_onclick\"";
				else
					$set_onclick = '';

				$button_array_icon = '';
				$class_icon = '';
				$button_array ['onclick'] = '';
				$onclick = '';

				if ($button_array ['icon']) {
					$button_array_icon = "<i class='icon {$button_array['icon']}'></i>";
					$class_icon = 'icon';
				}
				$output .= "<div class='ui $button_key $button_class $class_icon button {$button_array['color']}' $set_onclick >$button_array_icon {$button_array['title']}</div>";
			}
			$output .= "</div>";
		}

		$output .= "</div>";
	}

	return $output;
}

// Beispielaufruf:
// $arr ist das Array mit den Elementen, $element_type kann 'modal' oder 'flyout' sein
// $result = generate_element($arr, 'modal'); // Für Modals
// $result = generate_element($arr, 'flyout'); // Für Flyouts

?>
