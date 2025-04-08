<?php
$arr['field']['text'] = array ( 'tab' => 'first' , 'type' => 'ckeditor_inline' , 'toolbar' =>'mini' , 'value' => $text ,  'focus' => true );
$arr['field'][''] = array ( 'tab' => 'first' , 'type' => 'button' , 'class_button' => 'mini blue' , 'value' => 'Text übernehmen' , 'onclick' => "save_value_element('$update_id','text',$('#text').html(),'marquee');" );