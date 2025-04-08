<?php
include_once ('../../library/gallery.inc.php');
include_once ('../../config.inc.php');

// Neue Version noch in Erprobung
function listFolders($dir, $level = 0) {
	if (! $GLOBALS ['rm_relative_path'])
		$GLOBALS ['rm_relative_path'] = $dir;

	if (! is_dir ( $dir ))
		return;

	static $output_folder;

	for($space = 0; $space < $level; $space ++) {
		$set_space .= "&nbsp;&nbsp;&nbsp;&#8627;&nbsp;";
	}
	$level ++;

	$dh = scandir ( $dir );
	// $output_folder .='<ul>';
	foreach ( $dh as $folder ) {
		if ($folder != '.' && $folder != '..' && ! in_array ( $folder, $_SESSION ['IgnoreFileList'] )) {
			if (is_dir ( $dir . '/' . $folder )) {
				$folder1 = preg_replace ( "[" . $GLOBALS ['rm_relative_path'] . "]", '', $dir . '/' . $folder );
				$counter = countfiles ( $dir . '/' . $folder );
				// $output_folder .='<li>'.$set_space.$folder."($counter)</li>";
				$output_folder [$folder1] .= $set_space . $folder . "($counter)<br>";
				listFolders ( $dir . '/' . $folder, $level );
			}
		}
	}

	// $output_folder .='</ul>';
	return $output_folder;
}

/*
 * Form zum erzeugen eine Webseite
 */
function dir_tree($dir) {
	if (! $GLOBALS ['rm_relative_path'])
		$GLOBALS ['rm_relative_path'] = $dir;

	if (! is_dir ( $dir ))
		return;

	$path = array ();
	$stack [] = $dir;

	while ( $stack ) {
		$thisdir = array_pop ( $stack );
		if ($dircont = scandir ( $thisdir )) {

			$i = 0;
			while ( isset ( $dircont [$i] ) ) {
				if (! in_array ( $dircont [$i], $_SESSION ['IgnoreFileList'] )) {
					// if ($dircont[$i] !== '.' && $dircont[$i] !== '..') {
					$current_file = "{$thisdir}/{$dircont[$i]}";
					if (is_file ( $current_file )) {
						// $path[] = "{$thisdir}/{$dircont[$i]}";
					} elseif (is_dir ( $current_file )) {
						$counter = countfiles ( $current_file );
						$folder_key = "{$thisdir}/{$dircont[$i]}";
						$folder_key = preg_replace ( "[" . $GLOBALS ['rm_relative_path'] . "]", '', $folder_key );
						$folder = ltrim ( $folder_key, '/' );
						$path [$folder_key] = preg_replace ( "[/]", ' <b>&rsaquo;</b> ', $folder ) . "($counter)";

						// $path[$folder] = $folder;
						$stack [] = $current_file;
					}
				}
				$i ++;
			}
		}
	}
	return $path;
}

$array_folder = dir_tree ( $path_id_explorer_folder );
// $array_folder = listFolders( $path_id_explorer_folder );

$array_size = array ("mini" => "mini","tiny" => "klein","small" => "mittel","medium" => "groß" );
$array_size2 = array ("full" => "100%","small" => "300px","large" => "450px","big" => "600px","huge" => "800px","massive" => "960px" );
$array_col = array (1 => "1 spaltig",2 => "2 spaltig",3 => "3 spaltig",4 => "4 spaltig" );
$array_animation = array ("slide" => "<i class='caret left icon'></i><i class='caret right icon'></i>","fade" => "Übergang" );

$array_hover_effect = array ('image_opacity' => 'Deckkraft bei Mouseover','image_opacity_reverse' => 'Deckkraft bei Default' );
$array_zoom_effect = array ('img-hover-zoom' => 'Zoomen','img-hover-zoom img-hover-zoom--quick-zoom' => 'Schnelles Zoomen','img-hover-zoom img-hover-zoom--point-zoom' => 'Pointiertes Zoomen','img-hover-zoom img-hover-zoom--zoom-n-rotate' => 'Rotation',
		'img-hover-zoom img-hover-zoom--brightness' => 'Aufhellung','img_parallax' => 'Parallaxeffect' );

$array_view = array ('slide' => 'Slideshow','carousel' => 'Carousel' ); // , 'slide_carousel' => 'Slideshow + Carousel'
for($sek = 1; $sek < 10; $sek ++) {
	$array_slideshowSpeed [$sek] = "$sek Sekunden";
}

if (! $representation)
	$representation = 'fleximages';
if (! $thumb_height)
	$thumb_height = '400';
if (! $thumb_width)
	$thumb_width = '400';
if (! $slide_view)
	$slide_view = 'slide';
if (! $image_resize)
	$image_resize = 1;

if (! $animation)
	$animation = 'slide_hor';

$array_representation = array (
		// 'flexslider2' => '<i class="popup icon repeat large layout " title="Slideshow"></i>Slideshow' ,
		'carousel' => '<i class="popup icon ellipsis horizontal large layout " title="Carusell"></i>Karusell','fleximages' => '<i class="popup icon grid large layout " title="Thumbnails"></i>Thumbnails','list' => '<i class="popup icon list large layout " title="Liste"></i>Liste',
		'logos' => '<i class="popup icon ellipsis large vertical" title="Liste untereinander (speziell für Logos)"></i>Logo-leiste' );

$onLoad .= "
		show_hide_count_item();
		represention($('#representation').attr('value'));
	
		$('#representation').bind('keyup change',function() { 
			represention($('#representation').attr('value')) 
		});
	
		function represention(id){
			$('.show-carousel,.show-list,.show-thumbnail').hide(); 
			if (id =='carousel' ){ $('.show-carousel').show();  }
			else if (id =='list' )  { $('.show-list').show(); }
			else if (id =='fleximages' ) { $('.show-thumbnail').show(); }
			else if (id =='logos' ) { }
		}
								
		$('#owl_autocount').bind('keyup change',function() { show_hide_count_item() });
								
		function show_hide_count_item() {   
			if ($('#owl_autocount').attr('checked')){ $('#row_owl_item').hide(); } else { $('#row_owl_item').show(); } 
		}
		";

$arr ['field'] ['folder'] = array ('tab' => 'first','type' => 'dropdown','array' => $array_folder,'validate' => true,'value' => $folder,'class' => 'search' );
$arr ['field'] ['representation'] = array ('tab' => 'first','settings' => 'fullTextSearch:true','type' => 'dropdown','array' => $array_representation,'validate' => true,'value' => $representation );
// $arr['field']['slide_view'] = array ( 'tab' => 'first' , 'type' => 'radio', 'value' => $slide_view , 'array' => $array_view );

$arr ['field'] ['zoom_effect'] = array ('tab' => 'first','type' => 'dropdown','label' => 'Zoom-Effekte','value' => $zoom_effect,'array' => $array_zoom_effect,'clearable' => true );
$arr ['field'] ['hover_effect'] = array ('tab' => 'first','type' => 'dropdown','label' => 'Tranparents-Effekte','value' => $hover_effect,'array' => $array_hover_effect,'clearable' => true );

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'title' => "Allgemein" , 'active' =>true );

$arr ['field'] [] = array ('tab' => 'first','type' => 'div','class' => 'ui message' );
if (! $sort)
	$sort = 'name';
if (! $sort)
	$direction = 'direction';

$arr ['field'] ['sort'] = array ('tab' => 'first','label' => 'Sortieren nach','type' => 'radio',"array" => array ('name' => 'Dateiame','title' => 'Überschrift' ),'value' => $sort,'placeholder' => '' );
// $arr['field']['direction'] = array ( 'tab' => 'first' , 'label' => 'Richtung' , 'type' => 'dropdown' , "array" => array ( 'asc' => 'aufsteigend' , 'desc' => 'absteigend' ) , 'value' => $direction , 'placeholder' => '' );
// $arr['field']['size2'] = array ( 'tab' => 'first' , 'label' => 'Größe' , 'type' => 'dropdown' , "array" => $array_size98762 , 'value' => $size2 );

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'two fields' );

// $arr['field']['animation'] = array ( 'tab' => 'first' , 'type' => 'radio' , 'label' => 'Animation' , 'value' => $animation , 'array' => $array_animation );
$array_after_click = array ('nothing' => 'Keine Aktion setzen','resize' => 'vergrößern','link' => 'wenn verlinkt, weiterleiten' );
$arr ['field'] ['after_click'] = array ('tab' => 'first','type' => 'dropdown','label' => 'Bei Klick auf Bild','value' => $after_click,'array' => $array_after_click );

$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );

$arr ['field'] [] = array ('tab' => 'first','type' => 'div','class' => 'show-thumbnail ui message' );
$arr ['field'] ['rowHeight'] = array ('class' => 'no_reload_element','tab' => 'first','label' => 'Höhe','type' => 'slider','min' => 50,'max' => 300,'step' => 1,'unit' => 'px','value' => $rowHeight,'value_default' => 150 );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );

// $arr['field']['fullsize'] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'auf 100% ausdehnen' , 'value' => $fullsize , "info" => "Bild wird über die ganze Seite ausgedehnt." );

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'title' => "Erweitert" , 'split' => true );

$arr ['field'] [] = array ('tab' => 'first','type' => 'div','class' => 'show-list ui message' ); // fields inline
$arr ['field'] ['size'] = array ('tab' => 'first','label' => 'Größe','type' => 'dropdown',"array" => $array_size,'value' => $size );

$arr ['field'] ['col'] = array ('tab' => 'first','label' => 'Spalten','type' => 'dropdown',"array" => $array_col,'value' => $col );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );

$arr ['field'] [] = array ('tab' => 'first','type' => 'div','class' => 'show-carousel ui message' ); // fields inline
$arr ['field'] ['owl_autocount'] = array ('tab' => 'first','type' => 'checkbox','label' => 'Automatische Anzahl','value' => $owl_autocount );
if (! $owl_item)
	$owl_item = 1;
$arr ['field'] ['owl_item'] = array ('tab' => 'first','label' => 'Anzahl der Bilder pro Sicht','type' => 'dropdown','min' => 1,'max' => 10,'step' => 1,'value' => $owl_item );
$arr ['field'] ['owl_height'] = array ('tab' => 'first','label' => 'Höhe (0 = automatisch)','type' => 'slider','min' => 0,'max' => 500,'step' => 10,'unit' => 'px','value' => $owl_height );
// $arr['field']['owl_width'] = array ( 'tab' => 'first' , 'label' => 'Maximale Breite (0 = 100%)' , 'type' => 'slider' , 'min' => 0 , 'max' => 700 , 'step' => 10 , 'unit' => 'px' , 'value' => $owl_width );
$arr ['field'] ['autoload_sec'] = array ('tab' => 'first','label' => 'Automatischer Wechsel (0 = Stop) ','type' => 'slider','min' => 0,'max' => 10,'step' => 1,'unit' => 'sek','value' => $autoload_sec );
$arr ['field'] ['owl_loop'] = array ('tab' => 'first','type' => 'checkbox','label' => 'Wechsel loopen','value' => $owl_loop );
$arr ['field'] ['owl_nav'] = array ('tab' => 'first','type' => 'checkbox','label' => 'Navigation anzeigen','value' => $owl_nav );
$arr ['field'] ['owl_dots'] = array ('tab' => 'first','type' => 'checkbox','label' => 'Punkte anzeigen','value' => $owl_dots );
$arr ['field'] ['owl_dots_color'] = array ('tab' => 'first','label' => 'Punkefarbe','type' => 'color','value' => $owl_dots_color );
// $arr['field']['owl_lazy'] = array(
//     'tab' => 'first',
//     'type' => 'checkbox',
//     'label' => 'Lazy Load',
//     'info' => 'Nützlich beim laden von vielen Bildern',
//     'value' => $owl_lazy
// );
// $arr['field']['owl_autoheight'] = array(
//     'tab' => 'first',
//     'type' => 'checkbox',
//     'label' => 'Automatische Höhe',
//     'value' => $owl_autoheight
// );
// $arr['field']['owl_autowidth'] = array(
//     'tab' => 'first',
//     'type' => 'checkbox',
//     'label' => 'Automatische Breite',
//     'value' => $owl_autowidth
// );
// $arr['field']['owl_imgheight'] = array(
//     'tab' => 'first',
//     'label' => 'Höhe',
//     'type' => 'slider',
//     'min' => 100,
//     'max' => 300,
//     'step' => 10,
//     'unit' => 'px',
//     'value' => $owl_imgheight,
//     'value_default' => 150
// );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );


// $arr['field']['smoothHeight'] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'Bild der Höhe anpassen' , 'value' => $smoothHeight );

// $arr['field']['autoload_sec'] = array ( 'tab' => 'first' , 'type' => 'dropdown' , 'value' => $slideshowSpeed , 'array' => $array_slideshowSpeed );

//$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'close' => true );


