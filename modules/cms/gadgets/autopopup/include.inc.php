<?php
include_once ('../config.php');
include_once ('../../library/functions.php');

if ($_POST['site_id'])
    $site_id = $GLOBALS['mysqli']->real_escape_string($_POST['site_id']);
else
    $site_id = $_SESSION['site_id'];

$_SESSION['set_container_basic'] = '';
$mysql_query2 = $GLOBALS['mysqli']->query("
			SELECT * FROM smart_layer
			WHERE site_id='$site_id'
			AND splitter_layer_id = '0'
			AND archive = ''
			AND position = 'auto-popup'
			order by sort,layer_id
			") or die(mysqli_error($GLOBALS['mysqli']));
while ($array2 = mysqli_fetch_array($mysql_query2)) {
    // $layer_content = $array2['text'];
    $gadget = $array2['gadget'];
    $_SESSION['load_js'][$gadget] = true;
    $layer_id = $array2['layer_id'];
    $field = $array2['field'];
    $position = $array2['position'];
    $layer_fixed = $array2['layer_fixed'];
    $content['content_auto_popup'] .= show_element($layer_id);
}
echo "<div style='min-height:200px' id='auto-popup' class='smart_content_container sortable'>{$content['content_auto_popup']}</div>";

if ($_SESSION['admin_modus']) {
    echo "<script>
	SetSortable();
	SetNewTextfield ();
	for (var instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].destroy();
	}
	//$( '.cktext' ).ckeditor();
	save_content();
    </script>";
}

$add_js2 = preg_replace("[<script>|</script>]", "", $add_js2);
echo "<script>$add_js2</script>";