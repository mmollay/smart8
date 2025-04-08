<?php
// Config -> https://github.com/solarissmoke/php-moon-phase
include ('MoonPhase.php');

date_default_timezone_set ( "Europe/Berlin" );
// $date = strtotime("06 May 2015");

$moon = new Solaris\MoonPhase ( $date );
$age = round ( $moon->age (), 1 );
$stage = $moon->phase () < 0.5 ? 'waxing' : 'waning';
// $distance = round ( $moon->distance (), 2 );
// $next = gmdate ( 'G:i:s, j M Y', $moon->next_new_moon () );
$illumination = $moon->illumination ();
$illumination = 0.24;
// echo $age;

$moon_info_popup2 .= "Alter des Mondes:  $age Tage<br>";


if ($age > 15.5)
	$age = 15.5 - $age + 15.5;

$illumination = (100 / 15.5) * $age * 0.01;
// echo $illumination;

// 0 = new moon
// 0.25 = crescent
// 0.50 = quarter
// 0.75 = gibbous
// 1.00 = full moon

// Ab oder zunehmender Mond
if ($stage == 'waxing') {
	$shadow = 'true';
	$stage_text = 'zunehmend';
} else {
	$shadow = 'false';
	$stage_text = 'abnehmend';
}

$moon_info = "<b>" . $moon->phase_name () . "</b><br>" . " ($stage_text) ";

// Lichtstärke des Mondes

// @config $diameter -> Size of the moon
// @config $info_position -> Popup oder Text
// @config $color -> Farbe des Mondes
// @config $diameter -> Breite des Mondes

$diamenter = $_POST['diameter'];
$info_position = $_POST['info_position'];
$color = $_POST['color'];
$diameter = $_POST['diameter'];
$align = $_POST['align'];

if ($align == 'right') $popu_align = 'left';
else $popu_align = 'right';

if (! $diameter)
	$diameter = '100px';

if (! $color)
	$color = 'yellow';

$add_js = "$('#moon_container').popup();";

$moon_info_popup .= $moon_info;

// POPUP
if ($info_position == 'popup') {
	//$moon_info_popup .= $moon_info;
} // RIGHT
elseif ($info_position == 'right') {
	$text_moon_info_right = "<div class='child' style='text-align:left; padding-left:10px;'>$moon_info</div>";
} // LEFT
elseif ($info_position == 'left') {
	$text_moon_info_left = "<div class='child' style='text-align:right; padding-right:10px;'>$moon_info</div>";
} elseif ($info_position == 'bottom') {
	$text_moon_info_right = "<br><div style = 'text-align:center; display:inline-block; width:$diameter" . "px' >$moon_info</div>";
} elseif ($info_position == 'top') {
	$text_moon_info_left = "<div style = 'text-align:center; display:inline-block; width:$diameter" . "px' >$moon_info</div><br>";
}


$popup = "data-html='$moon_info_popup<br>$moon_info_popup2' data-position='$popu_align center' ";

echo "
<div class='parent'>
$text_moon_info_left
<div class='child' style='display:inline-block;' id=moon_container $popup></div>
$text_moon_info_right
</div>
";

// echo "<script type='text/javascript' src='gadgets/moon/planet_phase.js'></script>";
echo "<script>
drawPlanetPhase(document.getElementById('moon_container'), $illumination, $shadow,{diameter:'$diameter', earthshine:0.1, lightColour: '$color'});
$add_js
</script>";