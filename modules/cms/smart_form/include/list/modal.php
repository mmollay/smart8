<?php
foreach ($arr['modal'] as $modal_key => $modal_value) {

    $modal_title = $modal_value['title'];
    $modal_close_hide = $modal_value['close_hide'];
    $modal_class = $modal_value['class'];
    $modal_url = $modal_value['url'];
    $modal_button = $modal_value['button'];
    $modal_id = $modal_value['id'];

    if (preg_match("/scrolling/", $modal_class))
        $add_class_content = 'scrolling';
    else
        $add_class_content = '';

    // $close_button = $modal_value ['close_button'];
    // $close_button = "<div style='float:right'><a href=# onclick=\"$('#$modal_key').modal('hide'); $('#$modal_key>.content').empty(); \"><i class='close icon'></i></a></div><div style='clear:both'></div>";

    $modal .= "<div id='$modal_key' class='ui modal $modal_class'>";
    if (! $modal_close_hide)
        $modal .= "<i class='close icon'></i>";
    $modal .= "<div class='header'>$modal_title </div>";
    $modal .= "<div class='content $add_class_content'></div>";

    if (is_array($modal_button)) {
        $modal .= "<div class='actions'>";
        foreach ($modal_button as $button_key => $button_array) {
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
            $modal .= "<div class='ui $button_key $button_class $class_icon button {$button_array['color']}' $set_onclick >$button_array_icon {$button_array['title']}</div>";
        }
        $modal .= "</div>";
    }

    $modal .= "</div>";
    $modal_header = '';
}