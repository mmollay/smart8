<?php
$id = $array2['field_id'];
$type = $array2['type'];

// $value = json_decode ( $array2 ['value'], true );
$label = $array2['label'];
$text = $array2['text'];
$help = $array2['help'];
$min = $array2['min'];
$placeholder = $array2['placeholder'];
$validate = $array2['validate'];
$gadget_array = $array2['setting_array'] . "|segment_size=$segment_size";
$segment_formular_field = call_segment($gadget_array);
$rows = $segment_formular_field['rows'];
$default_value = $array2['default_value'];
$newsletter_field = $array2['newsletter_field'];

if ($newsletter_field == 'intro')
    $array_value = array('f' => 'Frau','m' => 'Herr');
elseif ($newsletter_field == 'country')
    // $array_value = array ('at' => 'Österreich','de' => 'Deutschland','ch' => 'Schweiz' );
    $array_value = 'country';
else
    $array_value = json_decode($array2['value'], true);

if ($newsletter_field == 'birth')
    $input_type = 'date';
else
    $input_type = $type;

if ($array2['setting_array']) {
    $gadget_array_n = explode("|", $array2['setting_array']);
    foreach ($gadget_array_n as $set_array) {
        $array3 = preg_split("[=]", $set_array, 2);
        $GLOBALS[$array3[0]] = $array3[1];
    }
}

if ($validate == '1')
    $validate = true;
else
    $validate = false;

if ($_SESSION['admin_modus'])
    $setting = "contenteditable='true' ";

if ($GLOBALS['class_ticked'] == '1')
    $add_slider_class = 'labeled ticked ';
else
    $add_slider_class = '';

if ($type == 'uploader') {
    $rnd = mt_rand();
    $arr['field'][$id] = array('class' => $segment_formular_field['segment'] . ' form-field','upload_dir' => "/var/www/ssi/smart_users/formular/$rnd/",'upload_url' => "/smart_users/formular/$rnd/",'label_class' => 'formular','label' => $label,'type' => 'uploader','accept' => array('png','jpg','jpeg','gif','pdf','zip','mp3','mp4','avi','wmv','mpeg','mov','flv'),'options' => 'imageMaxWidth:1000,imageMaxHeight:1000','button_upload' => array('text' => "Dateien auswählen",'color' => 'green','icon' => 'upload'),'card_class' => 'five','info' => $helps);
    $arr['field']["$id" . '_rnd'] = array('tab' => 'first','type' => 'hidden','value' => $rnd);
} else if ($type == 'text') {
    $arr['field'][$id] = array('class_content' => 'cktext' , 'class' => $segment_formular_field['segment'] . ' form-field','setting' => $setting,'type' => 'content','text' => $text,'value' => $default_value,'max' => $max,'min' => $min);
} else if ($type == 'slider') {
    $arr['field'][$id] = array('class' => $add_slider_class . $GLOBALS['class_color'] . $segment_formular_field['segment'] . ' form-field','label_class' => 'formular_save', // direktes bearbeiten des Labels wurde deaktivert, wegen der Zahlenanzeige
    'label' => $label,'rows' => $rows,'type' => $type,'validate' => $validate,'placeholder' => $placeholder,'info' => $help,'value' => $default_value,max => $GLOBALS['max'],min => $GLOBALS['min'],unit => $GLOBALS['unit']);

    $GLOBALS['max'] = $GLOBALS['min'] = $GLOBALS['unit'] = $GLOBALS['class_color'] = $GLOBALS['class_ticked'] = '';
} elseif ($type == 'select' or $type == 'radio') {
    if (! $placeholder)
        $placeholder = 'Bitte wählen';

    $arr['field'][$id] = array('search' => true,'class' => $segment_formular_field['segment'] . ' form-field','label_class' => 'formular','label' => $label,'type' => $type,'array' => $array_value,'validate' => $validate,'placeholder' => $placeholder,'info' => $helps,'value' => $default_value);
} else
    $arr['field'][$id] = array('class' => $add_slider_class . $segment_formular_field['segment'] . ' form-field','label_class' => 'formular','label' => $label,'rows' => $rows,'type' => $input_type,'validate' => $validate,'placeholder' => $placeholder,'info' => $help,'value' => $default_value);

$label = '';