<?
// Bei Zugriff vom NS-System zum Aufruf des Formulares werden keine Includes von Smart_kit geladen
if ($_POST ['list_id'] == 'formulardesign_list') {
	$gadget = $_POST ['list_id'];
} else {
	// Ladet für Smart-Kit mysql, functions + die Darstellung des jeweilige Layers
	include_once (__DIR__ . '/../../../login/config_main.inc.php');
	include_once (__DIR__ . '/../../config.inc.php');
	include_once (__DIR__ . '/../../smart_form/include_form.php');

	$gadget = $_POST ['name'];

	if ($_POST ['update_id']) {
		$update_id = $_POST ['update_id'];
		$sql = $GLOBALS ['mysqli']->query ( "SELECT gadget_array,format,dynamic_modus,dynamic_name, from_id, layer_fixed FROM smart_layer WHERE layer_id = '{$_POST['update_id']}'" );
		$array = mysqli_fetch_array ( $sql );

		//         $gadget_array = $array['gadget_array'];
		//         $gadget_array_n = explode("|", $gadget_array);
		//         if ($gadget_array) {
		//             foreach ($gadget_array_n as $array_split) {
		//                 $array2 = preg_split("[=]", $array_split, 2);
		//                 $GLOBALS[$array2[0]] = $array2[1];
		//             }
		//         }

		$arr ['value'] = call_smart_element_option ( $_POST ['update_id'] );

		$layer_fixed = $array ['layer_fixed'];
		// Darf nicht aktiviert sein (zumindest für Formular-da dieses sonst überschrieben wirde
		// $from_id = $array['from_id'];
		if (! $format)
			$format = $array ['format'];
		$dynamic_name = $array ['dynamic_name'];
		$dynamic_modus = $array ['dynamic_modus'];
	}
}

// Prüft über Recaptcha eingetragene Werte hat
function check_recaptcha_setting($recaptcha) {
	$query = $GLOBALS ['mysqli']->query ( "SELECT * from smart_page WHERE page_id='{$_SESSION['smart_page_id']}'" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	$array = mysqli_fetch_array ( $query );
	if ($array ['site_key'] && $array ['secret_key']) {} else {
		$add_link = "<br><a href=# onclick=\"$('.button_option_page').click()\"><u>Hier einrichten</u></a>\"";
	}

	return array ('tab' => 'first','type' => 'toggle','label' => "ReCaptcha aktivieren \"<i>I'm am not a robot</i>$add_link\"",'value' => $recaptcha );
}

$array_size = array ("mini" => "mini","tiny" => "klein","small" => "mittel","medium" => "groß","large" => "sehr groß","huge" => "enorm","massive" => "massiv",'fluid' => 'max Breite' );

// Wenn kein Gadget zur Verfügung steht wird Default "other" gewaehlt
if (! $gadget)
	$gadget = 'textfield';

$array_label_class = array ('left ribbon' => 'Ribbon (Links)','right ribbon' => 'Ribbon (Rechts)','top attached' => 'Angeheftet (oben)','bottom attached' => 'Angeheftet (unten)','tag' => 'Tag','left corner' => 'Ecke (links)','right corner' => 'Ecke (rechts)' );

$array_semgent_or_message = array ('segment' => 'Segment','message' => 'Infofeld' );
$array_segment_grade = array ('primary' => 'Primär','secondary' => 'Sekundär','tertiary' => 'Tertiär' );
$array_segment_size = array ('' => 'Standard','mini' => 'Mini','tiny' => 'Tiny','small' => 'Small','large' => 'Large','big' => 'Big','huge' => 'Huge','massive' => 'Massive' );

$array_background_repeat = array ('no-repeat' => '1mal anzeigen','repeat-y' => 'Vertikal wiederholen','repeat-x' => 'Horizontal wiederholen','repeat' => 'kacheln','space' => 'Gekachelt mit Abstand' );
$array_background_size = array ('auto' => 'Original','cover' => 'Angepasst','contain' => 'Contain','100% auto' => 'Breite 100%','auto 100%' => 'Höhe 100%' );
$array_background_position = array ('center top' => 'mittig oben','center center' => 'mittig mittig','center bottom' => 'mittig unten','left top' => 'links oben','left center' => 'links mittig','left bottom' => 'links unten','right top' => 'rechts oben','right center' => 'rechts mittig',
		'right bottom' => 'rechts unten' );

// Diese Funktion läuft leider nicht Version 2.2.13 semantic-ui
// , 'piled' => 'individuell gestapelt'
$array_segment_type = array ('' => 'Standard','raised' => 'angehoben','stacked' => 'gestapelt','tall stacked' => 'groß gestapelt','circular' => 'kreisrund','piled' => 'individuell gestapelt' );

/* anzeigen der Eingabefelder je nach Auswahl der Segment oder Messageeinstellung */
// $onLoad_save = "
// fu_segment_or_message($('#segment:checked').attr('id'));

// $('#segment_or_message').bind('keyup change focus',function() { fu_segment($('#segment_or_message').val()) });

// $('#segment').bind('change', function() {
// fu_segment_or_message($('#segment:checked').attr('id'));
// });

// function fu_segment_or_message(id) {

// if (id != 'segment') $('.show_segment').hide();
// else fu_segment($('#segment_or_message').val());
// }

// function fu_segment(id){
// $('.show_segment').show();
// if (id =='segment') { $('#row_segment_color,#row_segment_or_message,#row_segment_type,#row_segment_grade,#row_segment_inverted,#row_segment_disabled').show(); }
// else if (id =='message') { $('#row_segment_grade,#row_segment_inverted,#row_segment_type').hide(); $('#row_segment_or_message,#row_segment_color').show(); }
// }

// fu_show_label($('#show_label:checked').attr('id'));

// $('#show_label').bind('change', function() {
// fu_show_label($('#show_label:checked').attr('id'));
// });

// function fu_show_label(id) {
// if (id != 'show_label') $('.div_show_label').hide();
// else $('.div_show_label').show();
// }
// ";

if (! $segment_or_message)
	$segment_or_message = 'segment';

if ($gadget == 'textfield')
	$choose_tab = 'tab_border';

if (! $parallax_repeat)
	$parallax_repeat = 'no-repeat';

$arr ['tab'] = array (
		// 'class' => "pointing " ,
		// 'content_class' => "secondary" ,

		// ausgehängt
		'tabs' => [ "first" => "<i class='icon  setting tooltip' title='Einstellungen'></i>","tab_border" => "<i class='icon vector square tooltip' title='Rahmen & Darstellung'></i>","ruler" => "<i class='icon ruler combined tooltip' title='Abstände & Breite'></i>",
				"label" => "<i class='icon tag tooltip' title='Label'></i>" // "thi" => "<i class='icon terminal tooltip' title='ID & Parameter'></i>"
		],'active' => $choose_tab );

// $arr['header'] = array ( 'text' => "<i class='icon orange puzzle'></i><div class='content'>Element - $gadget</div>",'class' => 'grey small', segment_class=> '' );
$arr ['field'] ['segment_size'] = array ('label' => 'Größe','tab' => 'tab_border','type' => 'dropdown','array' => $array_segment_size,'value' => $segment_size );
$arr ['field'] ['align'] = array ('label' => "Ausrichtung",'tab' => 'first',"type" => "select",'array' => array ('left' => 'links','center' => 'mittig','right' => 'rechts' ),'value' => $align );
$arr ['field'] ['element_fullsize'] = array ('tab' => 'first','type' => 'checkbox','label' => '100% Ausdehnung','value' => $element_fullsize );

$arr ['field'] [] = array ('tab' => 'tab_border','type' => 'div','class' => 'content active  ui message' );
$arr ['field'] ['segment'] = array ('tab' => 'tab_border','type' => 'toggle','label' => 'Rahmen anzeigen','value' => $segment,'info' => 'Inhalt wird in eine weiße Box mit Rahmen gepackt' );
$arr ['field'] [] = array ('tab' => 'tab_border','type' => 'div','class' => 'show_segment' );
// $arr['field'][] = array ( 'tab' => 'tab_border' , 'type' => 'div' , 'class' => 'inline fields' );
$arr ['field'] ['segment_or_message'] = array ('tab' => 'tab_border','type' => 'dropdown','array' => $array_semgent_or_message,'value' => $segment_or_message );
$arr ['field'] ['segment_color'] = array ('tab' => 'tab_border','type' => 'dropdown','array' => 'color','value' => $segment_color,'placeholder' => 'Farben' );
$arr ['field'] ['segment_grade'] = array ('tab' => 'tab_border','type' => 'dropdown','array' => $array_segment_grade,'value' => $segment_grade,'placeholder' => 'Grad' );
$arr ['field'] ['segment_type'] = array ('tab' => 'tab_border','type' => 'dropdown','array' => $array_segment_type,'value' => $segment_type );
// $arr['field'][] = array ( 'tab' => 'tab_border' , 'type' => 'div_close' );
// $arr['field'][] = array ( 'tab' => 'tab_border' , 'type' => 'div' , 'class' => 'inline fields' );
$arr ['field'] ['segment_inverted'] = array ('tab' => 'tab_border','type' => 'checkbox','label' => 'Farbe im Hintergrund','value' => $segment_inverted,'info' => 'Farbe wird für den Hintergrund verwendet' );
$arr ['field'] ['segment_disabled'] = array ('tab' => 'tab_border','type' => 'checkbox','label' => 'Disabled','value' => $segment_disabled,'info' => 'Gesamte Inhalt wird "entkräftet"' );
$arr ['field'] ['segment_compact'] = array ('tab' => 'tab_border','type' => 'checkbox','label' => 'Kompakt','value' => $segment_compact,'info' => 'Gibt nur die notwendige Größe aus' );
// $arr['field'][] = array ( 'tab' => 'tab_border' , 'type' => 'div_close' );
// $arr['field'][] = array ( 'tab' => 'tab_border' , 'type' => 'div' , 'class' => 'two fields' );

// $arr['field'][] = array ( 'tab' => 'tab_border' , 'type' => 'div_close' );
$arr ['field'] [] = array ('tab' => 'tab_border','type' => 'div_close' );
$arr ['field'] [] = array ('tab' => 'tab_border','type' => 'div_close' );
// $arr['field']['margin_lr'] = array ( 'label' => 'Abstand(seitlich)' , 'tab' => 'tab_border' , 'type' => 'input' , 'class' => 'wide four' , 'value' => $margin_lr , 'label_right' => 'px' );
// $arr['field']['align'] = array ( 'label' => "Ausrichtung" , 'tab' => 'tab_border' , "type" => "select" , 'array' => array ( 'left' => 'links' , 'center' => 'mittig' , 'right' => 'rechts' ) , 'value' => $align );

$arr ['field'] [] = array ('tab' => 'tab_border','type' => 'div','class' => 'content active  ui message' );

// $arr['field']['parallax_height'] = array ('tab' => 'tab_border' , 'label' => 'Höhe' , 'type' => 'slider' , 'min' => 100 , 'max' => 600 , 'step' => 1 , 'unit' => 'px' , 'value' => $parallax_height );

$arr ['field'] ['parallax_show'] = array ('tab' => 'tab_border','type' => 'toggle','label' => 'Hintergrund','value' => $parallax_show,"info" => "Hintergrund anzeigen" );

$arr ['field'] [] = array ('tab' => 'tab_border','type' => 'div','class' => 'show_parallax' );
// $arr['field']['parallax_color'] = array ( 'tab' => 'tab_border' , 'label' => Hintergrundfarbe , 'type' => 'dropdown' , 'array' => 'color' , 'value' => $parallax_color , 'placeholder' => 'Farben' );
$arr ['field'] ['parallax_image'] = array ('tab' => 'tab_border',"label" => "Pfad","type" => "finder",'value' => $parallax_image );
$arr ['field'] ['parallax_color'] = array ('class_input' => 'no_reload_element','tab' => 'tab_border','label' => "Hintergrundfarbe",'type' => 'color','value' => $parallax_color );
$arr ['field'] ['parallax_color2'] = array ('class_input' => 'no_reload_element','tab' => 'tab_border','label' => "Hintergrundfarbe 2 (Verlauf)",'type' => 'color','value' => $parallax_color2 );
$arr ['field'] ['background_size'] = array ('tab' => 'tab_border','type' => 'dropdown','label' => 'Größe','value' => $background_size,"array" => $array_background_size,'value_default' => 'auto' );
$arr ['field'] ['background_position'] = array ('tab' => 'tab_border','type' => 'dropdown','label' => 'Ausrichtung','value' => $background_position,"array" => $array_background_position,'value_default' => 'center top' );
$arr ['field'] ['background_repeat'] = array ('tab' => 'tab_border','type' => 'dropdown','label' => 'Wiederholung','value' => $background_repeat,"array" => $array_background_repeat,'value_default' => 'no-repeat' );
$arr ['field'] ['parallax_mode'] = array ('tab' => 'tab_border','type' => 'checkbox','label' => 'Parallax - Mode','value' => $parallax_mode,"info" => "Bewegter Hintergrund bei Scroll" );
// $arr['field']['parallax_stretch'] = array ( 'class_input' => 'no_reload_content' , 'tab' => 'tab_border' , 'type' => 'checkbox' , 'label' => 'Bild 100% Breite' , 'value' => $parallax_stretch );
// $arr['field']['parallax_filter_color'] = array ( 'tab' => 'tab_border' , 'label' => Filterhintergrund, 'type' => 'dropdown' , 'array' => 'color' , 'value' => $parallax_filter_color , 'placeholder' => 'Farben' );
// $arr['field']['parallax_filter_color'] = array ( 'tab' => 'tab_border' , 'label' => Filterhintergrund, 'type' => 'color' , 'value' => $parallax_filter_color , 'placeholder' => 'Farben' );

$arr ['field'] ['parallax_filter'] = array ('tab' => 'tab_border','type' => 'checkbox','label' => 'Filter aktivieren','value' => $parallax_filter,"info" => "Legt einen Schleier über das Bild - verbessert die Lesbarkeit des Layerinhalte" );

$arr ['field'] [] = array ('tab' => 'tab_border','type' => 'div_close' );

// if (!$parallax_filter_color) $parallax_filter_color = '#eee';
// $arr['field']['parallax_filter_color2'] = array ( 'tab' => 'tab_border' , 'label' => Filterfarbe , 'type' => 'input' , 'value' => $parallax_filter_color );

$arr ['field'] [] = array ('tab' => 'tab_border','type' => 'div_close' );

/**
 * ********************************************************************************
 * RULER
 * *********************************************************************************
 */
$arr ['field'] ['parallax_padding'] = array ('class' => 'no_reload_element','tab' => 'ruler','label' => "<i class='icon arrows alternate vertical'></i> Abstand(Vertikal)",'type' => 'slider','max' => 300,'step' => 1,'unit' => 'px','value' => $parallax_padding );
$arr ['field'] ['parallax_padding_lr'] = array ('class' => 'no_reload_element','tab' => 'ruler','label' => "<i class='icon arrows alternate horizontal'></i> Abstand (Horizontal)",'type' => 'slider','max' => 200,'step' => 1,'unit' => 'px','value' => $parallax_padding_lr );
$arr ['field'] ['element_margin'] = array ('class' => 'no_reload_element','tab' => 'ruler','label' => "<i class='icon arrows alternate vertical'></i>Abstand Aussenrahmen(Vertikal)",'type' => 'slider','max' => 300,'step' => 1,'unit' => 'px','value' => $element_margin );
$arr ['field'] ['element_margin_lr'] = array ('class' => 'no_reload_element','tab' => 'ruler','label' => "<i class='icon arrows alternate horizontal'></i>Abstand Aussenrahmen(Horzintal)",'type' => 'slider','max' => 200,'step' => 1,'unit' => 'px','value' => $element_margin_lr );

$arr ['field'] ['element_width'] = array ('class' => 'no_reload_element','tab' => 'ruler','label' => 'Maximale Breite','type' => 'slider','min' => 20,'max' => 100,'step' => 1,'unit' => '%','value' => $element_width,'value_default' => '100' );

if ($gadget != 'splitter')
	$arr ['field'] ['parallax_height'] = array ('tab' => 'ruler','label' => ' Maximale Höhe (0 = automatische Höhe)','type' => 'slider','max' => 800,'step' => 50,'unit' => 'px','value' => $parallax_height );

// $arr['field']['button_change_background'] = array ( 'tab' => 'tab_border' , 'type' => 'button', 'class_button'=>'mini blue', value=>'Übernehmen' )
// $arr['field']['button_change_background'] = array ( 'tab' => 'tab_border' , 'type' => 'button', 'class_button'=>'mini blue', value=>'Bild tauschen' , 'onclick' =>"" );

$arr ['field'] ['style_div'] = array ('label' => 'Style (Bsp.: border:1px solid red)','tab' => 'tab_border','type' => 'input','value' => $style_div );

/**
 * ***************************************************************************
 * LABEL
 * ***************************************************************************
 */
$arr ['field'] ['show_label'] = array ('tab' => 'label','type' => 'toggle','label' => 'Label anzeigen','value' => "$show_label",'info' => 'Es wird ein Label in dem Feld angezeigt' );
$arr ['field'] [] = array ('tab' => 'label','type' => 'div','class' => 'div_show_label' );
$arr ['field'] ['labelIcon'] = array ('tab' => 'label','class' => 'two','label' => "Icon",'type' => 'icon','value' => $labelIcon );
$arr ['field'] ['label_text'] = array ('tab' => 'label','type' => 'input','label' => 'Label-Text','value' => $label_text );
// $arr['field']['label_span'] = array ( 'tab' => 'label' , 'type' => 'input' , 'label' => 'Nebentext' , 'value' => $label_span );
// $arr['field'][] = array ( 'tab' => 'label' , 'type' => 'div_close' );
// $arr['field'][] = array ( 'tab' => 'label' , 'type' => 'div' , 'class' => 'two fields' );
$arr ['field'] ['label_color'] = array ('tab' => 'label','label' => 'Farbe','type' => 'dropdown','array' => 'color','value' => $label_color,'placeholder' => 'Farben' );
$arr ['field'] ['label_class'] = array ('tab' => 'label','label' => "Darstellung","type" => "select",'array' => $array_label_class,'value' => $label_class );
$arr ['field'] ['label_size'] = array ('tab' => 'label','label' => 'Größe','type' => 'dropdown',"array" => $array_segment_size,'value' => $label_size );
// $arr['field']['label_align'] = array ( 'tab' => 'label' , 'label' => "Ausrichtung" , "type" => "radio" , 'array' => array ( 'left' => 'links' , 'center' => 'mittig' , 'right' => 'rechts' ) , 'value' => $label_align );
// $arr['field']['label_align'] = array ( 'label' => "Ausrichtung" , 'tab' => 'label' , "type" => "select" , 'array' => array ( 'left' => 'links' , 'center' => 'mittig' , 'right' => 'rechts' ) , 'value' => $label_align );
// $arr['field'][] = array ( 'tab' => 'label' , 'type' => 'div_close' );

// wird für das Newsletter-System nicht angezeigt
if ($gadget != 'formulardesign_list' and $_SESSION ['smart_page_id']) {
	$array_sites = GenerateArraySql ( "SELECT * FROM smart_langSite INNER JOIN smart_id_site2id_page ON smart_langSite.fk_id = smart_id_site2id_page.site_id and lang='{$_SESSION['page_lang']}' AND page_id='{$_SESSION['smart_page_id']}' ORDER BY title", 'title' ); // %var% die ausgegeben werden soll
	$arr ['field'] ['label_link'] = array ('tab' => 'label','label' => 'Seite','type' => 'dropdown','array' => $array_sites,'value' => $label_link );
}
$arr ['field'] [] = array ('tab' => 'label','type' => 'div_close' );

$arr ['field'] [] = array ('tab' => 'first','type' => 'div','class' => 'ui message' );
$arr ['field'] ['hide_in_smartphone'] = array ('tab' => 'first','type' => 'checkbox','label' => 'im Phone ausblenden','value' => $hide_in_smartphone,"info" => "Element wird im Smartphone ausgeblendet" );
$arr ['field'] ['anker_name'] = array ('tab' => 'first','type' => 'input','label' => 'Anker-Name','value' => $anker_name,"info" => "Anker-Name für Verknüpfung Bsp.: #formular_anmelden ",'placeholder' => 'ankername' );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );

// Aufrufen zusätzlicher Felder wenn Erweiterung vorhanden ist
// Eweiterung seit 12.2017
if (is_file ( "../gadgets/$gadget.inc.php" ))
	include_once ("../gadgets/$gadget.inc.php");

switch ($gadget) {
	case 'newsletter' :
	case 'formulardesign_list' :
		// parameter vom NS-System zum abrufen der Formelemete für IFRAME Darstellung
		include_once ("../gadgets/newsletter.inc.php");
		break;
	// Youtube, PDF
	case 'pdf' :
		include_once ("../gadgets/embed.inc.php");
		break;
	// FEEDBACK - FORM
	case 'feedback' :
		// case 'formular' :
		include_once ("../gadgets/formular.inc.php");
		break;
}

$arr ['field'] ['layer_id'] = array ('tab' => 'thi','type' => 'text','label' => 'Feld-ID','text' => 'layer_text' . $_POST ['update_id'],'info' => 'ID des Feldes zur manuellen Berabeitung für css und js' );
$arr ['field'] ['gadget_array'] = array ('tab' => 'thi','type' => 'textarea','readonly' => true,'label' => 'Gadget Array','value' => $gadget_array,'info' => 'für interne Weiterverarbeitung' );

$success = "
			if ( data =='close') { 
				$('#show_option').modal('hide');  
			}
			else {
				$('#show_option').modal('hide');
				//Wenn seite nach speichern wieder geladen soll (Bsp.: Button->fixed)
				if (data == 'reload') {
					$('#ProzessBarBox').message({ type:'success','title':'Inhalt gespeichert', text: '<div class=\"ui active centered inline loader\"></div><div align=center>Seite wird neu geladen...</div>' });
					location.reload();				
				}
				else {
					$('#ProzessBarBox').message({ type:'success', title: 'Inhalt gespeichert' });
					$('#sort_{$_POST['update_id']}').replaceWith(data);
					SetNewTextfield ();
				}
			}";

// $arr['form'] = array ( 'id' => 'form_element' , 'action' => 'admin/ajax/form_gadget2.php' , 'inline' => 'list' , 'size' => 'mini' );
$arr ['form'] = array ('id' => 'form_element',
		// 'action' => 'admin/ajax/form_gadget2.php',
		'inline' => 'list','size' => 'mini' );

$arr ['ajax'] = array ('success' => $success,'beforeSend' => "",'datatype' => 'html','onLoad' => "$onLoad load_autosave('{$_POST['update_id']}','$gadget');" );

// Autosave nach Veränderung des Parameters
// $arr['finder']['onchange'] = "save_value_element('$update_id',this.id,$('#'+this.id).val());";

$arr ['hidden'] ['update_id'] = $_POST ['update_id'];
$arr ['hidden'] ['edit_layer_id'] = $_POST ['update_id'];
$arr ['hidden'] ['gadget'] = $gadget;

// $arr['button']['submit'] = array ( 'value' => 'Speichern' , 'color' => 'blue' );
// $arr['button']['close'] = array ( 'value' => 'Schließen' , 'color' => 'gray' , 'js' => "$('.sidebar-element-setting').sidebar('toggle'); " );

$output = call_form ( $arr );
echo "<i style='display:none; right:30px; top:20px;  position:absolute;' id='save_icon_gadget' class='icon circular inverted small green save'></i>";
echo $output ['html'];

//TODO: Flyout weißt Fehler auf weil sie ausserhalb von "<div class=pusher>content</div>" gesetzt werden soll
// Flyout - wird daher im Smartkit noch nicht so richtig eingesetzt
//echo $output ['flyout']; 


// wird im Index geladen

echo $output ['js'];
echo $add_gadgets_js;
echo $add_other_form;