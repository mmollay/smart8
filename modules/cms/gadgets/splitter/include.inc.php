<?
$splitter_layer_id = $layer_id;

if (! $column_relation)
    $column_relation = '11';

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

$size_field1 = $numbermappings[$column_relation];
$size_field2 = $numbermappings[16 - $column_relation];

// Element im publicmodus nicht anzeigen
if (! $_SESSION['admin_modus']) {
    $add_query = "AND hidden = '' ";
}

$_SESSION['load_js'] = array();

// Content auslesen
$mysql_query2 = $GLOBALS['mysqli']->query("
		SELECT * FROM smart_layer LEFT JOIN smart_langLayer ON smart_layer.layer_id=smart_langLayer.fk_id
		WHERE (lang='{$_SESSION['page_lang']}' OR splitter_layer_id)
		AND archive = '' 
		$add_query
		AND splitter_layer_id = '$splitter_layer_id'
		AND layer_id  != '$splitter_layer_id'
		order by sort,layer_id
		") or die(mysqli_error($GLOBALS['mysqli']));
while ($array2 = mysqli_fetch_array($mysql_query2)) {
    $gadget = $array2['gadget'];

    if ($gadget)
        $_SESSION['load_js'][$array2['gadget']] = true;

    $layer_id = $array2['layer_id'];
    $field = $array2['field'];
    $position = $array2['position'];
    $layer_fixed = $array2['layer_fixed'];
    if (! $position)
        $position = 'left';
    // Verkleinern von Bildern im System + Sicherung der Bilder anlegen
    // $layer_content = image_resizer($layer_content);
    // $layer_content = UmwandelnTemplates($layer_content,$layer_id);

    // siehe libray/function
    // $layer_content = change_resize ( $layer_content );
    // $layer_content[$position] .= show_textfield ( $layer_id, $gadget, '', $layer_fixed );
    $layer_content[$position] .= show_element($layer_id);
    // $layer_content[$position] .= "$layer_id test";
}

$add_class_grid = 'element_splitter ';

// Cell variations
// if (! $cell_design)
// $add_class_grid = 'internally celled';
// elseif ($cell_design == 'empty')
// $add_class_grid = 'padded';
// else {
// $add_class_grid = $cell_design;
// }

if (! $cell_design)
    $cell_design = 'internally celled';

$add_class_grid .= " $cell_design";

if (! $cell_variation)
    $cell_variation = 'streched';

$add_class_row = " $cell_variation";

// Abstand rund um den Inhalt
// if (! $relaxed_off) {
// $add_class_grid .= ' relaxed';
// }

$add_class_grid .= " $cell_relaxed";

if ($cell_relaxed == 'no_padding' and is_array($layer_content)) {
    $grid_style = "padding:0px; ";
}

$set_container_class = $_SESSION['set_container_basic'] . " $set_container_class sortable grid_field";

$grid_style_left = "style='$grid_style'";
$grid_style_middle = "style='$grid_style' ";
$grid_style_right = "style='$grid_style' ";
$grid_style_four = "style='$grid_style' ";
$grid_style_five = "style='$grid_style' ";

if ($doubling)
    $doubling = 'doubling ';
else
    $stackable = 'stackable ';

if ($column_relation == '1') {
    // $output .= $layer_content['left'];
    $output .= "<div class='ui $add_class_grid $stackable grid' style='$grid_style' >
	<div id='left_$splitter_layer_id' class = '$set_container_class column' $grid_style_left>{$layer_content['left']}</div>
	</div>";
} else if ($column_relation == '333') {
    $output .= "<div class='ui $add_class_grid $stackable grid' >
	<div class='$doubling three column row $add_class_row' style='$grid_style'>
	<div id='left_$splitter_layer_id' class='$set_container_class column ' $grid_style_left >{$layer_content['left']}</div>
	<div id='middle_$splitter_layer_id' class='$set_container_class column ' $grid_style_middle >{$layer_content['middle']}</div>
	<div id='right_$splitter_layer_id' class='$set_container_class column ' $grid_style_right >{$layer_content['right']}</div>
	</div>
	</div>";
} else if ($column_relation == '444') {
    $output .= "<div class='ui $add_class_grid $stackable grid' >
	<div class='$doubling four column row $add_class_row' style='$grid_style'>
	<div id='left_$splitter_layer_id' class='$set_container_class column ' $grid_style_left>{$layer_content['left']}</div>
	<div id='middle_$splitter_layer_id' class='$set_container_class column ' $grid_style_middle>{$layer_content['middle']}</div>
	<div id='right_$splitter_layer_id' class='$set_container_class column ' $grid_style_right>{$layer_content['right']}</div>
	<div id='four_$splitter_layer_id' class='$set_container_class column ' $grid_style_four>{$layer_content['four']}</div>
	</div>
	</div>";
} else if ($column_relation == '424') {
    $output .= "<div class='ui $add_class_grid $stackable grid' >
	<div class='$doubling row $add_class_row' style='$grid_style'>
	<div id='left_$splitter_layer_id' class='$set_container_class four wide column ' $grid_style_left>{$layer_content['left']}</div>
	<div id='middle_$splitter_layer_id' class='$set_container_class eight wide column ' $grid_style_middle>{$layer_content['middle']}</div>
	<div id='right_$splitter_layer_id' class='$set_container_class four wide column ' $grid_style_right>{$layer_content['right']}</div>
	</div>
	</div>";
} else if ($column_relation == '525') {
    $output .= "<div class='ui $add_class_grid $stackable grid' >
	<div class='$doubling column row $add_class_row' style='$grid_style'>
	<div id='left_$splitter_layer_id' class='$set_container_class three wide column ' $grid_style_left>{$layer_content['left']}</div>
	<div id='middle_$splitter_layer_id' class='$set_container_class ten wide column ' $grid_style_middle>{$layer_content['middle']}</div>
	<div id='right_$splitter_layer_id' class='$set_container_class three wide column ' $grid_style_right>{$layer_content['right']}</div>
	</div>
	</div>";
} else if ($column_relation == '555') {
    $output .= "<div class='ui $add_class_grid $stackable grid' >
	<div class='$doubling five column row $add_class_row' style='$grid_style'>
	<div id='left_$splitter_layer_id' class='$set_container_class column ' $grid_style_left>{$layer_content['left']}</div>
	<div id='middle_$splitter_layer_id' class='$set_container_class column ' $grid_style_middle>{$layer_content['middle']}</div>
	<div id='right_$splitter_layer_id' class='$set_container_class column ' $grid_style_right>{$layer_content['right']}</div>
	<div id='four_$splitter_layer_id' class='$set_container_class column ' $grid_style_four>{$layer_content['four']}</div>
	<div id='five_$splitter_layer_id' class='$set_container_class column ' $grid_style_five>{$layer_content['five']}</div>
	</div>
	</div>";
} else if ($column_relation == '448') {
    if ($doubling)
        $stackable = 'stackable';
    $output .= "<div class='ui $add_class_grid $stackable grid' >
	<div class='$doubling row $add_class_row' style='$grid_style'>
	<div id='left_$splitter_layer_id' class='$set_container_class four wide column ' $grid_style_left>{$layer_content['left']}</div>
	<div id='middle_$splitter_layer_id' class='$set_container_class four wide column ' $grid_style_middle>{$layer_content['middle']}</div>
	<div id='right_$splitter_layer_id' class='$set_container_class eight wide column ' $grid_style_right>{$layer_content['right']}</div>
	</div>
	</div>";
} else if ($column_relation == '844') {
    if ($doubling)
        $stackable = 'stackable';
    $output .= "<div class='ui $add_class_grid $stackable grid' >
	<div class='$doubling row $add_class_row' style='$grid_style'>
	<div id='left_$splitter_layer_id' class='$set_container_class eight wide column ' $grid_style_left>{$layer_content['left']}</div>
	<div id='middle_$splitter_layer_id' class='$set_container_class four wide column ' $grid_style_middle>{$layer_content['middle']}</div>
	<div id='right_$splitter_layer_id' class='$set_container_class four wide column ' $grid_style_right>{$layer_content['right']}</div>
	</div>
	</div>";
} else {
    $output .= "
	<div class='ui $add_class_grid $stackable grid'>
	<div class='$doubling $add_class_row row' style='$grid_style'>
	<div id='left_$splitter_layer_id' class='$size_field1 wide column $set_container_class' $grid_style_left>{$layer_content['left']}</div>
	<div id='right_$splitter_layer_id' class='$size_field2 wide column $set_container_class ' $grid_style_right>{$layer_content['right']}</div>
	</div>
	</div>";
}

if ($GLOBALS['set_ajax']) {
    $output .= "\n 
	<script type='text/javascript'>
			SetSortable();
			SetNewTextfield();
			for (var instance in CKEDITOR.instances) {
				   CKEDITOR.instances[instance].destroy();
			}
			save_content();
	</script>";
}

