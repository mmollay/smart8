<?php
foreach ($arr['flyout'] as $flyout_key => $flyout_value) {

    $flyout_title = $flyout_value['title'];
    $flyout_close_hide = $flyout_value['close_hide'];
    $flyout_class = $flyout_value['class'];
    $flyout_url = $flyout_value['url'];
    $flyout_button = $flyout_value['button'];
    $flyout_id = $flyout_value['id'];

    if (preg_match("/scrolling/", $flyout_class))
        $add_class_content = 'scrolling';
    else
        $add_class_content = '';

    // $close_button = $flyout_value ['close_button'];
    // $close_button = "<div style='float:right'><a href=# onclick=\"$('#$flyout_key').flyout('hide'); $('#$flyout_key>.content').empty(); \"><i class='close icon'></i></a></div><div style='clear:both'></div>";

    $flyout .= "<div id='$flyout_key' class='ui flyout $flyout_class'>";
    if (! $flyout_close_hide)
        $flyout .= "<i class='close icon'></i>";
    $flyout .= "<div class='ui header'>$flyout_title </div>";
    $flyout .= "<div class='content $add_class_content'></div>";

    if (is_array($flyout_button)) {
        $flyout .= "<div class='actions'>";
        foreach ($flyout_button as $button_key => $button_array) {
            $button_class = $button_array['class'];
            $form_id = $button_array['form_id']; // verknüpfung zum Formular

            if ($form_id) {
                $onclick = "$('#$form_id').submit();";
            }

            if ($onclick or $button_array['onclick'])
                $set_onclick = "onclick =\"$onclick {$button_array['onclick']}\"";
            else
                $set_onclick = '';

            $button_array_icon = '';
            $class_icon = '';
            $button_array['onclick'] = '';
            $onclick = '';

            if ($button_array['icon']) {
                $button_array_icon = "<i class='icon {$button_array['icon']}'></i>";
                $class_icon = 'icon';
            }
            $flyout .= "<div class='ui $button_key $button_class $class_icon button {$button_array['color']}' $set_onclick >$button_array_icon {$button_array['title']}</div>";
        }
        $flyout .= "</div>";
    }

    $flyout .= "</div>";
    $flyout_header = '';
}
?>