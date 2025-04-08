<?php
$label_new = "<div class='label ui red mini'>Neu</div>";

$str_element_sidebar .= '<div style="z-index:1001;  width:200px; display: none;" class="hideAll ui accordion vertical menu right sidebar sidebar-elements">';
$str_element_sidebar .= "<div class='header item' style='background-color:#EEE;'><i class='icon move'></i> Elemente hineinziehen</div>";
$str_element_sidebar .= "<a style='right: 198px; top: 186px'  class='button-element-close tooltip-left ' title='Elemente-Bar schließen' onclick=\"$('.sidebar-elements').sidebar('toggle')\"><i class='icon orange large puzzle'></i></a>";

$str_element_sidebar .= '<div class="item"><a class="active title" style="color:grey;"><i class="dropdown icon"></i>Basis-Elemente</a><div class="active content">';

$str_element_sidebar .= module_element_sidebar ( 'splitter', "columns" );
$str_element_sidebar .= module_element_sidebar ( 'textfield', "align left" );
// $str_element_sidebar .= module_element_sidebar ( 'hallo', "content","Hallo" );
$str_element_sidebar .= module_element_sidebar ( 'photo', "photo layout" );
$str_element_sidebar .= module_element_sidebar ( 'gallery', "file image outline" );
$str_element_sidebar .= module_element_sidebar ( 'menu', "content", "Menü" );
$str_element_sidebar .= module_element_sidebar ( 'embed', "youtube play", "Video & Iframe" );

$str_element_sidebar .= module_element_sidebar ( 'formular', "file text outline" );
$str_element_sidebar .= module_element_sidebar ( 'feedback', "file text outline" );

$str_element_sidebar .= module_element_sidebar ( 'line', "minus", 'Linie' );
$str_element_sidebar .= module_element_sidebar ( 'search', "search", "Suchen" ); // , 'validate' => true
$str_element_sidebar .= module_element_sidebar ( 'awesome', "lemon", "Icons" );
// $str_element_sidebar .= module_element_sidebar ( 'button', "ellipsis horizontal" );

$str_element_sidebar .= module_element_sidebar ( 'marquee', "long arrow left" );
$str_element_sidebar .= module_element_sidebar ( 'pdf', "file pdf outline" );
$str_element_sidebar .= module_element_sidebar ( 'site_map', "sitemap", "Sitemap" );

$str_element_sidebar .= module_element_sidebar ( 'breadcrumb', "ellipsis horizontal", "Breadcrumb" );

if ($set_modul ['newsletter'])
	$str_element_sidebar .= module_element_sidebar ( 'newsletter', "mail", "Newsletter" );

$str_element_sidebar .= "</div></div>";

$str_element_sidebar .= '<div class="item"><a class="title" style="color:grey;"><i class="dropdown icon"></i>Weitere Elemente</a><div class="content">';
$str_element_sidebar .= module_element_sidebar ( 'guestbook', "file" );
$str_element_sidebar .= module_element_sidebar ( 'clock', "clock layout" );
$str_element_sidebar .= module_element_sidebar ( 'counter', "history layout" );
$str_element_sidebar .= module_element_sidebar ( 'miniplayer', "music layout" );
// $str_element_sidebar .= module_element_sidebar ( 'links', "linkify layout" );
$str_element_sidebar .= module_element_sidebar ( 'moon', "moon layout" );
$str_element_sidebar .= module_element_sidebar ( 'ticker', "comment" );
$str_element_sidebar .= module_element_sidebar ( 'sharing', "facebook" );
$str_element_sidebar .= module_element_sidebar ( 'dynamic', "external", 'Dynamisch' );
$str_element_sidebar .= module_element_sidebar ( 'script', "code layout" );
$str_element_sidebar .= module_element_sidebar ( 'other', "ticket", 'Platzhalter' );
$str_element_sidebar .= module_element_sidebar ( 'learning', "student", "Lerntool" );
$str_element_sidebar .= module_element_sidebar ( 'facebook', "facebook", "Facebook-Box" );
// $str_element_sidebar .= module_element_sidebar ( 'placeholder', "ticket", 'Platzhalter' );

// $str_element_sidebar .= "<div id='button_add_layer' class='item tooltip' title='Verschiebaren Layer erzeugen - Hier klicken' style='cursor:click;'><i class='object ungroup icon'></i>>Neuen Layer</div>";

$str_element_sidebar .= "</div></div>";

$str_element_sidebar .= '<div class="item"><a class="title" style="color:grey;"><i class="dropdown icon"></i>Spezielle Elemente</a><div class="content">';
$str_element_sidebar .= module_element_sidebar ( 'meditation', "heart" );
$str_element_sidebar .= module_element_sidebar ( 'login_bar', "sign in" );
if ($set_modul ['days21']) {
	$str_element_sidebar .= module_element_sidebar ( 'days21', "", '21 Days', 'Präsentiere deine Ergebnisse auf deiner Webseite' );
	$str_element_sidebar .= module_element_sidebar ( 'days21v2', "", '21 Days(v2)', 'Präsentiere deine Ergebnisse auf deiner Webseite' );
}
$str_element_sidebar .= module_element_sidebar ( 'map', "map" );
$str_element_sidebar .= module_element_sidebar ( 'hideme', "hide" );
$str_element_sidebar .= module_element_sidebar ( 'amazon', "amazon layout", "Amazon-Produkt" );

$str_element_sidebar .= "</div></div>";

$str_element_sidebar .= "</div>";

$content_sidebar_admin .= $str_element_sidebar;

// if ($right_set_public_page or ! $right_id) {
// $str_element_sidebar .= "<hr><div title='Klicken' id='button_add_layer' class='item tooltip' style='cursor:click;'><i class='object ungroup icon'></i>Neuen Layer</div>";
// }

// Element zum hineinziehen auf die gewünschte Position
function module_element_sidebar($id, $image, $title = false, $text = false) {
	global $str_text;

	// echo '$str_text[\'element\'][\''.$id.'\'][\'title\'] = \''.$title.'\';'."<br>";
	// echo '$str_text[\'element\'][\''.$id.'\'][\'text\'] = \''.$text.'\';'."<br>";
	if (! $title && $str_text ['element'] [$id] ['title'])
		$title = $str_text ['element'] [$id] ['title'];

	if (! $text && $str_text ['element'] [$id] ['text']) {
		$text = $str_text ['element'] [$id] ['text'];
		// $data_tooltip = "data-tooltip='$text' ";
	}
	return "<div id='$id' class='new_module item tooltip' data-position='left center' title='$text' style='cursor:move;'><i class='$image icon'></i>$title</div>";
}
