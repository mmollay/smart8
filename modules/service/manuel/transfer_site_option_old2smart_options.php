<?php
// Transferiert options old zu smart_options
include_once ('../../login/config_main.inc.php');

foreach ($array as $key) { 
    if ($get_string) $get_string .= ','; $get_string .=  $key;
   
}

$sql = "SELECT * FROM smart_id_site2id_page";

$query = $GLOBALS ['mysqli']->query ( $sql ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
while ($fetch_array = mysqli_fetch_array($query)) {
     // OPTIONS
    
    $array = array(
        'menu_disable'=> $fetch_array['menu_disable'],
        'menu_newpage'=> $fetch_array['menu_newpage'],
        'menubar_disable'=> $fetch_array['menu_disable'],
        'breadcrumb_disable'=> $fetch_array['breadcumb_disable'],
        'dynamic_site'=> $fetch_array['dynamic_site'],
        'dynamic_name'=> $fetch_array['dynamic_name'],
        'site_dynamic_id'=> $fetch_array['site_dynamic_id'],
        'layout_id'=> $fetch_array['layout_id'],
        'parent_id'=> $fetch_array['parent_id'],        
    );
    
     $page_id = $fetch_array['page_id'];
     $site_id = $fetch_array['site_id'];
    
     save_smart_option ( $array, $page_id, $site_id );
     echo "Site_ID $site_id fertig übertragen<br>";
 }
 
