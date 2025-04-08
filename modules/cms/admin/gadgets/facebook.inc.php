<?php
$select_plugin_array = array ( 'standard' => 'Standard' , 'share_button' => 'Share Button' , 'like_share_button' => 'Button Count' );
$arr['field']['select_plugin'] = array ( 'tab' => 'first' , 'label' => "Plugin" , "type" => "select" , 'array' => $select_plugin_array , 'value' => $select_plugin );
$arr['field']['fb_link'] = array ( 'tab' => 'first' , 'label' => "Facebook" , 'type' => 'input' , 'value' => $fb_link );