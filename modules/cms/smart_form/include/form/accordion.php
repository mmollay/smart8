<?php
// Accordion as well Nested Accordions possible
if (! $close && $title && ! $split) {
	$count_accordion ++;
	$arr_accordion_class [$count_accordion] = $class;
} elseif ($close) {
	$count_accordion --;
	$arr_accordion_close [$count_accordion] = $close;
}

// accordion
if ($active)
	$add_active = 'active';
else
	$add_active = '';

if ($close) {
	$type_field .= "</div>";
	if ($arr_accordion_close [$count_accordion] == $close) {
		$type_field .= "</div></div>";
	}
} else {
	if ($split)
		$type_field .= "</div>";
	if ($title) {
		if (! $split) {
			if ($count_accordion == 1) {
				$add_accordion_class = 'ui';
			} else {
				$add_accordion_class = '';
			}
			$type_field .= "<div class='field'><div id='$id' class='$add_accordion_class {$arr_accordion_class[$count_accordion]} accordion'>";
		}
	}
	$type_field .= "<div  class='$add_active title'><i class='icon dropdown'></i>$title</div><div class='$add_active content'>";
}

$class = $active = $title = $close = $split = '';