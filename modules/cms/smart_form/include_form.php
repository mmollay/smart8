<?php
/*
 * --------------------------------------------------------------------------------------------------------
 * | SMART - FORM (ssi-Product)
 * | Form-Generator: Using Semantic-UI Library
 * | 04.02.2024 - mm@ssi.at
 * | Version 2.8
 * --------------------------------------------------------------------------------------------------------
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE);
error_reporting(1);

session_start();
// Function call Formular
function call_form($arr)
{
	date_default_timezone_set('Europe/Vienna');

	if (!is_array($arr)) {
		return "Form is not defined";
		exit;
	}

	// Basic values
	$set_field = '';
	$jquery = '';
	$valitation = '';
	$output_content = '';
	$output_form_open = '';
	$output_form_close = '';
	$output_form_open_header = '';
	$output_form_open_footer = '';
	$buttons = '';
	$add_data = '';
	$form_style = '';
	$tab_field = array();
	$GLOBALS['ii'] = $GLOBALS['ii'] ?? 1;

	$field_id = 100; // Default - Field ID

	// Form - Values
	$form_size = $arr['form']['size'] ?? '';
	$form_class = $arr['form']['class'] ?? '';
	$form_inline = $arr['form']['inline'] ?? '';
	$form_align = $arr['form']['align'] ?? '';
	$form_id = $arr['form']['id'] ?? ('smartForm' . ++$GLOBALS['ii']);
	$form_action = $arr['form']['action'] ?? $_SERVER['PHP_SELF'];

	// Ajax
	$ajax_dataType = $arr['ajax']['dataType'] ?? 'script'; // or json
	$ajax_success = $arr['ajax']['success'] ?? '';
	$ajax_beforeSend = $arr['ajax']['beforeSend'] ?? '';
	$ajax_onLoad = $arr['ajax']['onLoad'] ?? '';
	$ajax_xhr = $arr['ajax']['xhr'] ?? '';

	// Buttons
	$buttons_class = $arr['buttons']['class'] ?? '';
	$buttons_align = $arr['buttons']['align'] ?? '';
	$buttons_id = $arr['buttons']['id'] ?? '';

	$data = "\n\t\t form_id : '$form_id',"; // Wird auch übergeben und jquery-requests anpassen zu können (Bsp.: Messages)
	$hidden = "\n\t<input type=hidden name='form_id' id='form_id' value='$form_id' >";

	if (isset($arr['sql']['query'])) {
		$mysql_query = $GLOBALS['mysqli']->query($arr['sql']['query']) or die(mysqli_error($GLOBALS['mysqli']));
		$mysql_array = mysqli_fetch_array($mysql_query);

		if (isset($arr['sql']['key'])) {
			$sql_key['value'] = $mysql_array[$arr['sql']['key']];
		} else
			// Wird automatisch genommen (erstes Feld)
			$sql_key['value'] = $mysql_array[0] ?? '';
	} else
		$sql_key['value'] = '';

	if ($arr['field']) {

		foreach ($arr['field'] as $key1 => $value1) {

			$read_only = $disabled = $focus = $type = $value = $value_default = '';
			$info = $onchange = $add_label_content = $data_html = $icon = $min = '';
			$max = $step = $toolbar = $message = $segment = $text = $size = '';
			$add_class = $id = $placeholder = $label = $type_field = $type = $required = '';
			$class = $rules = $validate = $valitation_rules = $date = $prompt = '';
			$val_type = $class_input = $label_right = $label_left = '';
			$label_left_class = $label_left_click = $footer = $info_tooltip = $onclick = '';
			$format = $options = $rows = $style = $onchange = $column = $grid = '';
			$label_class = $icon_postition = $clearable = $search = $class_search = $option = '';
			$tooltip = $color = $segment_attached = '';
			$data_value = $add_function_submit = $add_xhr = '';
			$field = $contenteditable = $class_content = $class_content = $align = '';
			$setting = $inverted = $tab = $array_mysql = $filter_value = $class_button = '';
			$label_right_click = $label_right_tooltip = $label_left_tooltip = $label_right_id = $add_label_click = $add_label_right_tooltip = '';
			$close = $split = $count_accordion = $active = $grid = '';
			$onChange = $onMove = $start = $labelType = $unit = '';
			$settings = $url = $array_name = $first_value = $clear = $long = '';
			$class_select = $class_optgroup = $dropdown_icon_remove = $option = $onchange = $value = '';
			$clearable = $readonly = $set_disabled = '';
			$array_icon = '';
			$field_div = '';

			// Sollte keine ID vergeben worden sind wird eine erzeugt
			if (!$key1) {
				$field_id++;
				$id = 'field-' . $field_id;
			} else if (!is_int($key1))
				$id = $key1;
			// Id darf keine Zahl sein, sonst wird diese erweitert durch field-
			else if (is_int($key1)) {
				$id = 'field-' . $key1;
			}

			// ID - kann auch manuell gesetzt werden Bsp.: $arr['field']['domain']

			// if ($key1)

			foreach ($value1 as $key2 => $value2) {
				${$key2} = $value2;
			}

			if ($read_only)
				$read_only = 'read-only';

			if ($disabled) {
				$disabled = "disabled='disabled' ";
			}

			// Jquery - Focus
			if ($focus) {
				if ($type == 'select' or $type == 'multiselect') {
					$jquery_focus = "$('#dropdown_$id').delay(800).focus(); ";
				} else {
					$jquery_focus = "$('#$id').focus();";
				}
			}

			if (isset($arr['value'][$id]))
				$value = $arr['value'][$id];

			if ($value === '0000-00-00')
				$value = '';
			if ($value_default && !$value)
				$value = $value_default;

			$value_default = '';

			if (isset($mysql_array[$id]) && !$value) {
				// Auslesen der Werte aus der Datenbank
				// Wenn value nicht manuel gesetzt ist wird dieser aus der Datenbank genommen
				$value = $mysql_array[$id];
			}

			if (!is_array($value)) {
				// Macht Übergabe für Sonderzeichen in Inputfelder und Co möglich
				// $value = htmlspecialchars ( $value );
				$value = htmlspecialchars_decode($value);
			}

			// Infotext in einen popup
			if ($info) {
				// $info_tooltip = "<span data-tooltip='$info' ><i class='icon help circle tooltip grey'></i></span>";
				$info_tooltip = "<span title='$info' class='tooltip' ><i class='icon help circle grey'></i></span>";
			}

			if ($label === ' ')
				$label = '&nbsp;'; // Zeigt ein unsichbares Label an

			if (isset($form_size)) {
				$size = $form_size;

				// Definieren der Schriftgrößen entsprechend der Größe in $form_size
				$fontSizes = ['massive' => '22px', 'huge' => '20px', 'big' => '18px', 'large' => '16px', 'small' => '14px', 'tiny' => '14px', 'mini' => '12px'];

				// Prüfen, ob die Größe in $fontSizes vorhanden ist, andernfalls leere Zeichenfolge verwenden
				$option_style = isset($fontSizes[$size]) ? "font-size:{$fontSizes[$size]};" : '';
			} else {
				$option_style = ''; // Standardwert, wenn 'size' nicht definiert ist
			}

			$onchange = preg_replace("/{id}/", $id, $onchange);

			if ($type == 'date')
				$type = 'calendar';

			if (file_exists(__DIR__ . "/include/form/$type.php"))
				include (__DIR__ . "/include/form/$type.php");
			elseif (in_array($type, array('toggle', 'checkbox')))
				// TOGGLE , CHECKBOX
				include (__DIR__ . "/include/form/checkbox.php");
			elseif ($type == 'explorer' or $type == 'finder')
				include_once (__DIR__ . "/include/form/finder.php");
			// SELECT, MULTISELECT
			elseif ($type == 'dropdown' or $type == 'select' or $type == 'multiselect')
				include (__DIR__ . "/include/form/dropdown.php");

			/**
			 * **********************************************************************************************************
			 * END INPUTS and more
			 * **********************************************************************************************************
			 */

			if (is_array($rules) or $validate) {
				$required = 'required';
			}

			// segment
			if ($segment) {
				if ($segment === true)
					$segment = '';
				$segment = "segment ui $segment";
				// message
			} elseif ($message) {
				if ($message === true)
					$message = '';
				$message = "message ui $message";
			}

			if (in_array($type, array('hidden', 'tab', 'accordion', 'div', 'div_close', 'header'))) {
				$field .= $type_field;
			} else {

				/**
				 * *******************
				 * Field - Begin
				 * *******************
				 */

				//if $wide is set, then the field will be displayed add wide	
				$wide = $value1['wide'] ?? '';
				$wide = $wide ? "$wide wide" : '';

				// style='border:1px solid red;'
				$field_div = "\n<div  id='row_$id' class='$wide field row_field $required $class $segment $message'>";
				if ($label) {
					if ($label === true)
						$label = "&nbsp;";
					$field_div .= "<label class='$form_id label {$label_class}' id='label_$id'>$label $info_tooltip <span class='check_message'></span> $add_label_content</label>";
				}
				if (($type == 'input') and $label_right) { // Label im Input Right
					if ($label_right_click)
						$add_label_click = "onclick=\"$label_right_click\"";

					if ($label_right_tooltip)
						$add_label_right_tooltip = "data-tooltip='$label_right_tooltip'";

					if ($label_left_tooltip)
						$add_label_left_tooltip = " data-tooltip='$label_left_tooltip'";

					if (!$label_right_id)
						$label_right_id = "label_right_$id";

					$field_div .= "<div class='ui right labeled input'>$type_field<div id='$label_right_id' class='ui label $label_right_class' $add_label_click $add_label_right_tooltip>$label_right</div></div>";
				} else if (($type == 'input') and $label_left) { // Label im Input Left
					if ($label_left_click)
						$add_label_click = "onclick=\"$label_left_click\"";
					$field_div .= "<div class='ui left labeled input'><div id='label_left_$id' class='ui label $label_left_class' $add_label_click $add_label_left_tooltip>$label_left</div>$type_field</div>";
				} else if (($type == 'select' or $type == 'multiselect') and $label_right) { // Label im Input Right
					$field_div .= "<div class='ui right labeled input'>$type_field<div id='label_left_$id' class='ui label $label_right_class' $add_label_right_tooltip>$label_right</div></div>";
				} else if (($type == 'select' or $type == 'multiselect') and $label_left) { // Label im Input Left
					$field_div .= "<div class='ui labeled input'><div class='ui label'>$label_left</div>$type_field</div>";
				} else
					$field_div .= $type_field;

				$field_div .= $footer;
				$field_div .= "</div>";
				/**
				 * *******************
				 * Field - End
				 * *******************
				 */
			}

			if (!$grid) {
				$field .= $field_div;
			} else {

				// Übergabe an grid
				$grid_content[$grid] = $field_div;
			}

			// Short - Version for valtiation
			if ($type == 'smart_password') {
				$valitation .= "'$id': { identifier: '$id', rules: [{ type: 'empty', prompt: 'Bitte Passwort eingeben'},{ type: 'length[8]', prompt: 'Das Passwort muss min. 8 Zeichen haben'},{ type:'containsNumbers', prompt: 'Passwort muss eine Zahl beinhalten' }] },";
				$valitation .= "'{$id}_repeat': { identifier: '{$id}_repeat', rules: [{ type: 'empty', prompt: 'Bitte Passwort eingeben'},{ type: 'match[$id]', prompt: 'Passwort stimmt nicht überein'}] },";
				$jquery .= "$.fn.form.settings.rules.containsNumbers = function(value){ var regex = new RegExp('[0-9]'); return regex.test(value); }";
			} else if ($validate == true) {
				if (in_array($type, array('toggle', 'checkbox', 'slider'))) {
					$val_type = 'checked';
				} else {
					$val_type = 'empty';
				}

				if ($validate === true)
					$validate = 'empty';

				if (!$prompt)
					$prompt = 'Eingabe überprüfen';

				// Wenn mehr als 1 type gewählt wird, dann wird "empty" verwendet und
				if (str_word_count($validate) > 1) {
					$prompt = "$validate";
					$validate = 'empty';
				}

				if ($id) {
					if ($validate == 'empty') {
						if ($type == 'radio')
							$val_type = 'checked';
						$valitation .= "'$id': { identifier: '$id', rules: [{ type: '$val_type', prompt: '$prompt'}] },";
					} elseif ($validate) {
						$valitation .= "'$id': { identifier: '$id', rules: [{ type: '$validate', prompt: '$prompt'},{ type: 'empty', prompt: '$prompt'}] },";
					}
				}
			}

			// Complex version with a lot of possibilties
			if (is_array($rules)) {
				$valitation_rules = json_encode($rules);
				$valitation .= "'$id': { rules: $valitation_rules },";
			} elseif ($rules) {
				$valitation .= "'$id': '$rules',";
			}

			if ($type == 'date' or $type == 'calendar') { // $('#$id').calendar('get date')
				$add_data .= "data.push({ name: '$id', value: change_calendar_data( $('#$id').calendar('get date') )  });";
			} else if ($type == 'radio') {
				// $data .= "\n\t\t '$id' : $('.$id:checked.$form_id').val(),";
				$add_data .= "data.push({ name: '$id', value: $('.$id:checked.$form_id').val() });";
			} else if ($type == 'recaptcha') {
				// Google captcha
				// echo 'g-recaptcha-response';
				// $data .= "\n\t\t 'recaptcha' : $('#g-recaptcha-response').val(),";
				$add_data .= "data.push({ name: 'recaptcha', value: $('#g-recaptcha-response').val() });";
			} else if ($type == 'ckeditor_inline') {
				// INLINE - CKEDITOR with DIV
				// $add .= "\n\t\t '$id' : $('#$id.$form_id').html(),";
			} elseif ($type == 'ckeditor5') {
				$add_data .= "data.push({ name: '$id', value: myEditor_$id.getData() });";
			} elseif ($id) {
				// Input, Textarea
				// $data .= "\n\t\t '$id' : $('#$id.$form_id').val(),";
			}

			// Tab
			if (isset($arr['tab']) && is_array($arr['tab']) && $tab) {
				if (!isset($tab_field[$tab]))
					$tab_field[$tab] = '';
				$tab_field[$tab] .= "$field";
			} else { // Regulär
				$set_field .= $field;
			}
		}
	} else {
		$field = "<h4 class='header ui'>Keine Felder definiert</h4>";
	}

	/*
	 * Buttons für submit,Cancle,break or custorm
	 */

	if (isset($arr['button']) && is_array($arr['button'])) {
		// if (!is_array($arr['tab'])) $buttons = "<br>";
		$buttons .= "<div class='actions' align='$buttons_align'><div class='ui $buttons_class buttons' id='$buttons_id'>";

		foreach ($arr['button'] as $key => $value1) {
			$id = '';
			$onclick = '';
			$type = '';
			$js = '';
			$tooltip = '';
			$icon = '';
			$type = '';
			$data_text = '';
			$value = '';
			$color = '';
			$class = '';
			$set_onclick = '';
			$set_data_text = '';
			$add_class = '';
			$info = '';

			foreach ($value1 as $key2 => $value2) {
				$$key2 = $value2;
			}

			if (isset($js))
				$set_onclick = "onclick=\"$js\" ";
			elseif ($onclick)
				$set_onclick = "onclick=\"$onclick\" ";

			if ($info)
				$add_class = 'tooltip';

			if ($type == 'submit' or $id == 'submit')
				$type = 'submit';

			if ($icon) {
				$icon = "<i class='icon $icon'></i> ";
				$class = "icon $class";
			}
			if ($tooltip)
				$tooltip = "data-tooltip='$tooltip'";

			if ($type == 'or') {
				if ($value)
					$set_data_text = " data-text='$value' ";

				$buttons .= "<div class='or' $set_data_text></div>";
			} else
				$buttons .= "<div $tooltip class='ui $color $class button $form_id $key $add_class $form_size' type='$type' id='$id' $set_onclick >$icon$value</div>";
		}

		$buttons .= '</div></div>';
	}

	// print_r($arr_accordion_close);


	// Hidden Field for db
	if ($sql_key['value'] != '') {
		$hidden .= "<input type=hidden name='update_id' id='update_id' value='{$sql_key['value']}' >";
		$data .= "\n\t\t update_id : '{$sql_key['value']}',";
	}

	// HIDDEN
	if (isset($arr['hidden'])) {
		foreach ($arr['hidden'] as $key => $value) {
			$hidden .= "\n\t<input type=hidden name='$key' id='$key' value='$value' >";
			$data .= "\n\t\t $key : '$value' ,";
		}
	}

	// BREITE der Formulares
	if (isset($arr['form']['width'])) {
		$form_style = "max-width:{$arr['form']['width']}px;";
	}

	if (isset($arr['header'])) {
		if ($arr['header']['icon']) {
			$add_header_icon = "<i class='{$arr['header']['icon']} icon'></i>";
			$add_header_icon_class = 'icon';
		}
		$output_form_open_header .= "
		<div class='ui top $add_header_icon_class {$arr['header']['segment_class']}'>
			$add_header_icon
			<div class='content'>
				<div class='ui header {$arr['header']['class']}'>
					{$arr['header']['title']}
				</div>
			{$arr['header']['text']}
			</div>
		</div>";
	}

	if (isset($arr['footer'])) {
		$output_form_open_footer .= "<div class='ui bottom {$arr['footer']['segment_class']}'>{$arr['footer']['text']}</div>";
	}

	// Form wird nur angezeigt wenn arr['form'] definiert worden ist
	if (is_array($arr['form']) || is_array($arr['ajax'])) {
		if (isset($arr['form']['align']))
			$output_form_open .= "<div align='{$form_align}'>";

		$output_form_open .= "<div style='text-align:left; $form_style'>";
		$output_form_open .= $output_form_open_header;
		$output_form_open .= "<form id='$form_id' name ='$form_id' class='ui $segment_attached {$form_class} " . (isset($form_size) ? $form_size : '') . " form'>";

		// wird benötigt wenn 'dataType' => html ist, wird bsp.: "ok" übergeben
		if (!isset($GLOBALS['data_value'])) {
			$output_form_close .= "<input id='data' name='data' type='hidden'>";
			$GLOBALS['data_value'] = true;
		}

		$output_form_close .= "</form>";
		$output_form_close .= $output_form_open_footer;
		$output_form_close .= "</div>";

		if (isset($arr['form']['align']))
			$output_form_close .= "</div>";
	}

	// if ($arr['header']) {
	// $output_form_close .= "</div></div>";
	// }

	/**
	 * ******* Auflistung Der Felder, wenn eingestellt mit TAB *****************
	 */

	$output_content .= $output_form_open;

	// Tab
	if (isset($arr['tab']) && is_array($arr['tab'])) {

		// Darstellungsart TAB
		$tab_class = $arr['tab']['class'] ?? '';

		$content_class = $arr['tab']['content_class'] ?? '';

		if (!$tab_class) {
			$tab_class = "top attached";
			$content_class = "bottom attached $content_class";
		}

		$tab_title = $tab_content = '';
		foreach ($arr['tab']['tabs'] as $tab_key => $tab_value) {
			if (!$arr['tab']['active'])
				$arr['tab']['active'] = 'first';
			if ($arr['tab']['active'] == "$tab_key")
				$tab_active = 'active';
			else
				$tab_active = '';

			if ($content_class != 'basic') {
				$set_segment = 'segment ';
			}

			// Anzeigen wenn content vorhanden ist
			if ($tab_field[$tab_key]) {
				$tab_title .= "\n<a class='$tab_active item' id='$tab_key' data-tab='$tab_key'>$tab_value</a>";
				$tab_content .= "\n<div class='ui $set_segment tab $form_id $form_size $content_class $tab_active' data-tab='$tab_key'>{$tab_field[$tab_key]}</div>";
			}
		}

		$output_content .= "<div id='tabgroup_$form_id'><div class='ui $tab_class tabular menu {$form_size}'>$tab_title</div>$tab_content<div></div></div>";
		if ($content_class == 'basic') {
			$output_content .= "<br>";
		}

		if (!is_array($arr['tab']))
			$output_content .= "<br>"; // Zeilenumbruch wenn TAB mit Einfassung verwendet wird (also keine spzifische Darstellung)

		$jquery .= "$('#tabgroup_$form_id .menu .item').tab();";
	}

	$output_content .= $set_field;

	if ($form_inline == 'list') {
		$output_content .= "<div style='text-align:left;' class='ui error message'></div>";
		$form_inline = '';
	}

	$output_content .= $buttons;
	$output_content .= $hidden;
	$output_content .= $output_form_close;

	if ($output_flyout_modal)
		$output['flyout'] = $output_flyout_modal; // speziell für finder

	/**
	 * **************************************************************************
	 */

	$keyboardShowtcuts = !empty($arr['form']['keyboardShortcuts']) ? '' : 'keyboardShortcuts : false,';

	$output['html'] = $output_content;
	$output['html'] .= generateConfirmationModal($form_id, $arr);

	// Standardmässig wird Form aufgerufen
	if ($form_action !== false) {

		if ($ajax_xhr) {
			$add_xhr = "
 			xhr: function(){
				var xhr = $.ajaxSettings.xhr();
				xhr.onprogress = function(e){
					data = e.currentTarget.responseText.substr(responseLen);
					responseLen = e.currentTarget.responseText.length;
					$ajax_xhr
				};
				return xhr;
			},";
		}

		$add_function_submit .= "

		function submitForm_$form_id() {
		 var responseLen = 0;
		 var data = $('#$form_id').serializeArray();
		 $add_data
		
		 // Bestätigungslogik, wenn erforderlich
		 " . ($arr['ajax']['confirmation'] ? "
		 $('#confirmationModal_$form_id')
		 .modal({
		   closable: false,
		   onDeny: function(){
			 // Nutzer hat den Vorgang abgebrochen, keine Aktion notwendig
		   },
		   onApprove: function() {
			 ajaxSubmit();
		   }
		 })
		 .modal('show');
		
		 function ajaxSubmit() {
		 " : "") . "
		  $.ajax({
			type: 'POST',
			global   : false,
			url: '$form_action',
			data: data,
			dataType: '$ajax_dataType',
			$add_xhr
			beforeSend: function(){
			  {$ajax_beforeSend}
			  $('#$form_id').attr('class','ui loading form $form_id {$form_size} {$form_class}');
			},
			success: function(data){
			  $('#$form_id').attr('class','ui form $form_id {$form_size} {$form_class}');
			  $('.message .close').on('click', function() {
				$(this).closest('.message').fadeOut();
			  });
			  {$ajax_success}
			}
		  });
		 " . ($arr['ajax']['confirmation'] ? "}" : "") . "
		 return false;
		}
		";
	} // Wenn "'action' => false" wird nur success auf aufgerufen
	else {
		$add_function_submit .= "function submitForm_$form_id() {  {$ajax_success} return false; }";
	}

	$output['js'] = "<script>";
	$output['js'] .= "
	
		function change_calendar_data(date){
			if (date != null) {
				date1 = new Date ( date );
				date = date1.getDate();
				year = date1.getFullYear();
				month = date1.getMonth()+1;
				return year+'-'+month+'-'+date;
			}
		}
		
		function get_icon(id,icon) {
			var icon = icon.replace('+', ' ');
			$('#button_icon_'+id).attr('class',icon+' large icon');
			$('#'+id).val(icon).change();
			$('.tooltip-click').popup('hide');
		}
		
	  	$(document).ready(function() {
	  	
			//$('.link.remove.icon').hide();

			//versteckt 'x' bei den Inputs wenn kein Eintrag vorhanden ist //$clearable
			$('.ui-input').each(function () {
				if ($(this).val()) $('.link.remove.icon#icon_'+$(this).attr('id')).show();
				else  $('.link.remove.icon#icon_'+$(this).attr('id')).hide();
			});
			
			$('.ui-input').change( function(){
				if (!$(this).val())
					$('.link.remove.icon#icon_'+$(this).attr('id')).hide();
				else
					$('.link.remove.icon#icon_'+$(this).attr('id')).show();
			})
			
			$('.ui.accordion').accordion();
			$('.ui.accordion').accordion({ onOpen: function (item) {  $('.modal').modal('refresh'); } });
			$('.tooltip').popup();
			$('.tooltip-right').popup({ position:'right center' });
			$('.tooltip-top').popup({ position:'top center' });
			$('.tooltip-left').popup({ position:'left center' });
			$('.tooltip-click').popup({ on: 'click' });
						
	  		$jquery
	  		
		  	$('#$form_id.ui.form').form({
		  		fields: { $valitation },
		  		inline: '$form_inline',
		  		transition: 'fade down',
		  		$keyboardShowtcuts
		  		onSuccess: submitForm_$form_id
			});
			$jquery_focus
			$ajax_onLoad
		});
		$add_function_submit
		$add_js_finder
		";

	$output['js'] .= "</script>";

	//löschen des Array für die nächste Form
	unset($GLOBALS['arr']);

	return $output;
}

include_once (__DIR__ . '/functions/filelist.php');
include_once (__DIR__ . '/include/form/functions.php');
include_once (__DIR__ . '/include/smart_functions.php');
