<?
$define_type_field = '';
if (! $icon_postition and ! $clearable)
	$icon_postition = 'left';

if ($search) {
	$define_type_field .= "<div class='ui search $id'>";
	$class_search = 'prompt';
}
if ($read_only) {
	$define_type_field .= "<input $setting id='$id' class='$form_id $class_input' name ='$id' type='hidden' value='$value'>";
} else {

	if ($icon or $clearable)
		$define_type_field .= "<div class='ui $icon_postition icon input'>";

	
	$define_type_field .= "<input type='text' class='ui-input $class_search $form_id $class_input' name ='$id' $disabled value='$value' id='$id' placeholder='$placeholder'  $option>";
	
	
	if ($search)
		$define_type_field .= "<div class='results'></div></div>";

	if ($icon)
		$define_type_field .= "<i class='$icon icon'></i></div>";

	if ($clearable)
		$define_type_field .= "<i onclick=\"$('#$id').val('');\" id='icon_$id' class='link remove icon'></i></div>";
}

$type_field = $define_type_field;
