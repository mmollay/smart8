<?
$version['map'] = "3.1";
$_SESSION['ssi_map_admin_modus'] = ''; 
// Wird für die public-Seite geladen
if (! $_SESSION['admin_modus']) {
	$add_path_js .= "\n<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?key=AIzaSyAgoO9CQxiF6tddu1WIKqB5vrONEHsoLTM&region=AT'></script>";
}

$add_path_js .= "\n<script>appendScript('gadgets/map/js/gmap3.min.js');</script>";
// $add_path_js .= "\n<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?key=AIzaSyAgoO9CQxiF6tddu1WIKqB5vrONEHsoLTM&region=AT'></script>";
$add_path_js .= "\n<script>appendScript('gadgets/map/js/jquery.fullscreen.min.js');</script>";

$add_path_js .= "\n<script>appendScript('gadgets/map/js/functions.js');</script>";
$add_path_js .= "\n<script>var destination = '$destination'; </script>";
$add_path_js .= "\n<script src='gadgets/map/js/include.js'></script>";

$add_css2 .= "<style type='text/css'>@import 'gadgets/map/font.css';</style>\n";

// $button_output .= "<a class='item active' data-tab='first'><i class='map icon'></i><div class='tablet'>Fruitmap </div><div class='computer'>&nbsp;<span class='style_treenumber' id='count_trees'></span></div></a>";

// if ($show_clients or $_SESSION['admin_modus']) {
// if (! $show_clients and $_SESSION['admin_modus'])
// $add_icon_eye_client = "<i class='icon eye slash outline grey tooltip' title = 'wir öffentlich nicht angezeigt' ></i>";

// $button_output .= "<a class='item' data-tab='second' onclick=loadList('client')>$add_icon_eye_client<i class='users icon'></i><div class='tablet'>Baumpaten</div></a>";
// $div_output .= "<div class='ui basic segment tab load_segment' data-tab='second' id=load_client></div>";
// }

// if ($show_sorts or $_SESSION['admin_modus']) {
// if (! $show_sorts and $_SESSION['admin_modus'])
// $add_icon_eye_sorts = "<i class='icon eye slash outline grey tooltip' title = 'wir öffentlich nicht angezeigt' ></i>";

// $button_output .= "<a class='item' data-tab='third' onclick=loadList('sort')>$add_icon_eye_sorts<i class='lemon icon'></i><div class='tablet'>Sorten</div></a>";
// $button_output .= "<a class='item' data-tab='four' onclick=loadList('sortgroup')>$add_icon_eye_sorts<i class='tree icon'></i><div class='tablet'>Gattung/Art</div></a>";

// $div_output .= "<div class='ui basic segment tab load_segment' data-tab='third' id=load_sort></div>";
// $div_output .= "<div class='ui basic segment tab load_segment' data-tab='four' id=load_sortgroup></div>";
// }

$output .= "<div id='map_container' class='container'>";

$output .= "
	<div id=content_menu>
		<div class='ui top attached menu pointing'>
			<a class='item icon toggle_sidemap'><i id='map_filter_icon' class='icon grey ui content'></i></a>
			<div class='ui item' style='width:180px'><div class='ui transparent icon input'><input class='prompt search_input' placeholder='Suchen' type='text'><i class='search link icon'></i></div></div>
			<div class='right menu'><a class='item icon' id='fullsrceen_toggle'><i class='icon expand arrows alternate'></i></a></div>
		</div>
		<div class='ui bottom attached segment pushable' id='map_sidebar_segment'>
			<div id='map_sidebar' class='ui labeled left sidebar segment'><div id='menu_filter'></div></div>
			<div class='pusher' >
				<div id='map-no-results' style='display:none; position:absolute; right:0px; z-index:10000;' align=center><div class='ui label red'>Keine Suchergenisse vorhanden</div></div>
				<div class='ui bottom attached tab active load_segment' style='height:600px;' data-tab='first' id='load_map'></div>
				$div_output
			</div>
		</div>
	</div>";

$output .= "</div>";

$output .= "<div class='ui modal' id ='modal_ordertree' ><i class='close icon'></i><div class='header'>Baumpatenschaft beantragen</div><div class='content'></div></div>";
