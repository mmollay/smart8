<?php
if (! $column_relation) {
    $column_relation = 11;
}
$numbermappings = array(
    "zero",
    "one",
    "two",
    "three",
    "four",
    "five",
    "six",
    "seven",
    "eight",
    "nine",
    "ten",
    "eleven",
    "twelve",
    "thirteen",
    "fourteen",
    "fifteen",
    "sixteen"
);
$array_columns = array(
    "13",
    "12",
    "11",
    "10",
    "9",
    "8",
    "7",
    "6",
    "5",
    "4",
    "3"
);
$array_compact = array();
$array_relaxed = array(
    'no_padding' => 'Randlos',
    'relaxed' => 'Normal',
    'compact' => 'Kompakt',
    'very compact' => 'Sehr kompakt',
    'very relaxed' => 'mehr Abstand'
);

$array_relation['1'] = "<div class='ui celled grid' style='margin:0px; width:180px; height:18px;'>
		<div class='column row'>
		<div class='column' style='padding:4px; background-color:#EEE'></div>
		</div>
		</div>";

foreach ($array_columns as $set_column_relation) {
    // for($set_column_relation=8; $set_column_relation>1; $set_column_relation--) {
    // $get_percent = round(10/1.6*$iii);
    // $get_rest_percent = 100-$get_percent;
    // $array_relation[$iii] = "$get_percent% zu $get_rest_percent%";
    $size_field1 = $numbermappings[$set_column_relation];
    $size_field2 = $numbermappings[16 - $set_column_relation];

    if ($set_column_relation > '8') {
        // $ggT = round ( 16 / (16 - $set_column_relation) );
        // $bruch2 = round ( (16 / $ggT) / (16 - $set_column_relation) );
        // $bruch1 = round ( $ggT - $bruch2 );
    } else {
        // $ggT = round ( 16 / $set_column_relation );
        // $bruch1 = round ( (16 / $ggT) / $set_column_relation );
        // $bruch2 = round ( $ggT - $bruch1 );
    }
    // $ggT = "/".$ggT;

    $array_relation[$set_column_relation] = "<div class='ui celled grid' style='margin:0px; width:180px; height:18px;'>
  			<div class='row'>
		    		<div class='$size_field1 wide column' style='padding:0px; background-color:white'>$bruch1$ggT</div>
		    		<div class='$size_field2 wide column' style='padding:0px; background-color:#EEE'>$bruch2$ggT</div>
		    		</div>
			</div>";
}

$array_relation['525'] = "<div class='ui celled grid' style='margin:0px; width:180px; height:18px;'>
		<div class='ui equal width column row'>
		<div class='column' style='padding:4px; background-color:white'></div>
		<div class='ten wide column' style='padding:4px; background-color:#EEE'></div>
		<div class='column' style='padding:4px; background-color:white'></div>
		</div>
		</div>";

$array_relation['333'] = "<div class='ui celled grid' style='margin:0px; width:180px; height:18px;'>
		<div class='three column row'>
		<div class='column' style='padding:2px; background-color:white'></div>
		<div class='column' style='padding:2px; background-color:#EEE'></div>
		<div class='column' style='padding:2px; background-color:#DDD'></div>
		</div>
		</div>";

$array_relation['444'] = "<div class='ui celled grid' style='margin:0px; width:180px; height:18px;'>
		<div class='four column row'>
		<div class='column' style='padding:4px; background-color:white'></div>
		<div class='column' style='padding:4px; background-color:#EEE'></div>
		<div class='column' style='padding:4px; background-color:#DDD'></div>
		<div class='column' style='padding:4px; background-color:white'></div>
		</div>
		</div>";

$array_relation['424'] = "<div class='ui celled grid' style='margin:0px; width:180px; height:18px;'>
		<div class='row'>
		<div class='four wide column' style='padding:4px; background-color:white'></div>
		<div class='eight wide column' style='padding:4px; background-color:#EEE'></div>
		<div class='four wide column' style='padding:4px; background-color:white'></div>
		</div>
		</div>";

$array_relation['844'] = "<div class='ui celled grid' style='margin:0px; width:180px; height:18px;'>
		<div class='row'>
		<div class='eight wide column' style='padding:4px; background-color:white'></div>
		<div class='four wide column' style='padding:4px; background-color:#EEE'></div>
		<div class='four wide column' style='padding:4px; background-color:white'></div>
		</div>
		</div>";

$array_relation['448'] = "<div class='ui celled grid' style='margin:0px; width:180px; height:18px;'>
		<div class='row'>
		<div class='four wide column' style='padding:4px; background-color:white'></div>
		<div class='four wide column' style='padding:4px; background-color:#EEE'></div>
		<div class='eight wide column' style='padding:4px; background-color:white'></div>
		</div>
		</div>";

$array_relation['555'] = "<div class='ui celled grid' style='margin:0px; width:180px; height:18px;'>
		<div class='five column row'>
		<div class='column' style='padding:4px; background-color:white'></div>
		<div class='column' style='padding:4px; background-color:#EEE'></div>
		<div class='column' style='padding:4px; background-color:#DDD'></div>
		<div class='column' style='padding:4px; background-color:white'></div>
		<div class='column' style='padding:4px; background-color:#EEE'></div>
		</div>
		</div>";

$array_design = array(
    'padded' => 'Ohne Linien ',
    'internally celled' => 'Mit Trennlinie dazwischen',
    'celled' => 'Mit Umrahmung'
);

$array_variation = array(
    '' => 'Obere Ausrichtung',
    'stretched' => 'Gestreckte Felder',
    'middle aligned' => 'Mittige Zentrierung',
    'bottom aligned' => 'Untere Ausrichtung'
);
// , 'centered' => 'Vertikale Zentrierung' deactivated - kein weiterer Nutzen

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'fields two' );
$arr['field']['cell_design'] = array(
    'tab' => 'first',
    'type' => 'dropdown',
    'array' => $array_design,
    'label' => '',
    'value' => $cell_design,
    'value_default' => 'internally celled'
);
$arr['field']['cell_variation'] = array(
    'tab' => 'first',
    'type' => 'dropdown',
    'array' => $array_variation,
    'label' => '',
    'value' => $cell_variation,
    'value_default' => 'stretched'
);
// $arr['field']['cell_compact'] = array ( 'tab' => 'first' , 'type' => 'dropdown' , 'array' => $array_compact , 'label' => '' , 'value' => $cell_compact , 'value_default' => 'no_padding' );
$arr['field']['cell_relaxed'] = array(
    'tab' => 'first',
    'type' => 'dropdown',
    'array' => $array_relaxed,
    'label' => '',
    'value' => $cell_relaxed,
    'value_default' => ''
);
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

// $arr['field']['celled_off'] = array ( 'tab' => 'first' , 'type' => 'toggle' , 'label' => 'Trennlinie dazwischen ausblenden' , 'info' => 'Blendet die Trennlinie zwischen den Feldern aus' , 'value' => $celled_off );
// $arr['field']['stretched'] = array ( 'tab' => 'first' , 'type' => 'toggle' , 'label' => 'Felder gleichermaßen gestreckt' , 'info' => 'Diese Funktion ist es möglich ein gleichbleiben Block der Inhalte zu erzeugen' , 'value' => $stretched );
// $arr['field']['relaxed_off'] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'mit verringerten Abstand' , 'info' => 'Wenn zu wenig Platz erscheint, kann man die Distanz rund um das Element verringern.' , 'value' => $relaxed_off );

$arr['field']['column_relation'] = array(
    'label' => 'Darstellung',
    //'long' => true,
    'tab' => 'first',
    'type' => 'dropdown',
    'array' => $array_relation,
    'value' => $column_relation
);

// $arr['field']['column_color_left'] = array ( 'tab' => 'first' , 'label' => 'Hintergrundfarbe (1)' , 'type' => 'color' , 'value' => $column_color_left );
// $arr['field']['column_color_middle'] = array( 'tab' => 'first' , 'label' => 'Hintergrundfarbe (2)' , 'type' => 'color' , 'value' => $column_color_middle );
// $arr['field']['column_color_right'] = array ( 'tab' => 'first' , 'label' => 'Hintergrundfarbe (3)' , 'type' => 'color' , 'value' => $column_color_right );
$arr['field']['doubling'] = array(
    'tab' => 'first',
    'type' => 'checkbox',
    'label' => 'in Mobildarstellung 2spaltig anzeigen',
    'value' => $doubling
);
