<?php
if ($_SESSION['admin_modus']) {
	// Prüfen ob Buttons bereits vorhanden sind
	$query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_gadget_button WHERE layer_id = '$layer_id' order by sequence" );
	$array = mysqli_fetch_array ( $query );
	if (! is_array ( $array )) {
		
		$GLOBALS['mysqli']->query ( "INSERT INTO smart_gadget_button SET
				layer_id = '$layer_id',
				sequence = '1',
				title = 'Button 1',
				icon = 'star',
				color = 'green',
                url = '',
                link = '',
                tooltip = '',
                target  = 0
				" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	}
}

$page_id = $_SESSION['smart_page_id'];
$query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_langSite INNER JOIN smart_id_site2id_page ON site_id = fk_id where page_id = '$page_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
while ( $array = mysqli_fetch_array ( $query ) ) {
	$id = $array['site_id'];
	$option[$id] = $array['title'];
}

if (! $layer_fixed) {
	// $add_class_bar = 'stackable';
	$add_class_bar = 'doubling';
} else {
	$add_class_bar = 'button_bar';
}

if (! $no_fluid)
	$class_fluid = 'fluid';

if (! $align)
	$align = 'center';

if ($button_cirular)
	$class_circular = 'circular';

$set_buttons = '';
$query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_gadget_button WHERE layer_id = '$layer_id' order by sequence" );
while ( $button_array = mysqli_fetch_array ( $query ) ) {
	$i ++;
	$text = $button_array['title'];
	$icon = $button_array['icon'];
	$color = $button_array['color'];
	$tooltip = $button_array['tooltip'];
	if ($tooltip)
		$add_tooltip = "data-tooltip='$tooltip' ";
	$url = $button_array['url'];
	$link = $button_array['link'];
	$target = $button_array['target'];
	
	$href = '';
	
	if ($target)
		$target = 'target=new';
	else
		$target = '';
	
	if ($link) {
		// Wenn Link ist, wird dieser bevorzugt
		$href = "$link";
	} else if ($url) {
		// Wenn interner Link verwendet wird
		$href = "?site_select=$url";
		$href = "#";
		$onclick = "onclick=\"CallContentSite('$url')\" ";
	} else
		$onclick = $href = '';
	
	if ($icon) {
		$icon = "<i class='ui icon $icon'></i> ";
		$class_icon = 'icon';
	} else
		$class_icon = '';
	
	if ($href) {
		$href = "href=\"$href\"";
	}
	
	if ($text or $icon) {
		if ($button_line) {
			$set_buttons .= "<button style='color:white' $add_tooltip $href $onclick $target class='button icon  ui $class_circular $color $class_icon $button_size' >$icon$text</button>";
		} else {
			$set_buttons .= "<div class='column'><div align=$align><a $add_tooltip $href $onclick $target class='button $class_circular icon $class_fluid ui $color $class_icon $button_size' >$icon$text</a></div></div>";
		}
	}
}

if ($set_buttons) {
	if ($button_line) {
		$output .= "<div style='line-height: 3.2em' align='$align'>$set_buttons</div>";
	} else {
		$output .= "<div style='padding:8px 0px;'>";
		$output .= "<div class='ui equal width centered compact grid'>";
		$output .= "<div class='$add_class_bar column row'>$set_buttons</div>";
		$output .= "</div>";
		$output .= "</div>"; 
	}
}
