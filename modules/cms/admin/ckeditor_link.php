<?php
session_start ();
$page_id = $_SESSION['smart_page_id'];
$user_id = $_SESSION['user_id'];

// Gibt Instruktion an den Explorer, dass er den Link des Images weitergeben soll an den CKEditor
$_SESSION['CKEditorKey'] = 'content_edit';

// Datenbankverbindung herstellen
include_once ('../../login/config_main.inc.php');
include_once ('../smart_form/include_form.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Explorer</title>
<link rel="stylesheet" href="../smart_form/semantic/dist/semantic.min.css">
</head>
<body>
<div style='padding:5px'>
<?

$query = $GLOBALS['mysqli']->query ( "SELECT *
		,(CASE
		WHEN DATE(max(timestamp)) = CURDATE() then CONCAT('<span style=\'color:green\'>Heute</span> ')
		WHEN DATE(max(timestamp)) = CURDATE()-interval 1 day then CONCAT('<span style=\'color:orange\'>Gestern</span>')
		ELSE CONCAT('vor ',datediff(NOW(), DATE(max(timestamp))),' Tagen ')
		END) timestamp2
 FROM smart_langSite LEFT JOIN smart_id_site2id_page ON site_id = fk_id 
	where page_id = '$page_id' group by site_id 
	order by timestamp, title
 " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
while ( $array = mysqli_fetch_array ( $query ) ) {
	$id = $array['site_id'];
	$timpestamp = $array['timestamp2'];
	$arr_option[$id] = $array['title']." <label class='ui label mini'>$timpestamp</label>";
}


// $query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_langSite INNER JOIN smart_id_site2id_page ON site_id = fk_id where page_id = '$page_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
// while ( $array = mysqli_fetch_array ( $query ) ) {
// 	$id = $array['site_id'];
// 	$arr_option[$id] = $array['title'];
// }

$style_div = 'position: relative; padding-bottom: 55%; height: 0;';
$style = 'position: absolute;top: 0;left: 0; width: 100%; height: 100%;';

$arr['form'] = array ('id'=>'get_link', 'class' => 'green message' , 'width' => '100%');
$arr['field']['site'] = array ('label'=>'Seite zum verlinken wählen', 'type' => 'dropdown' , 'array' => $arr_option,  'class' => 'search', 'placeholder'=>'Verlinken zu...', 'onchange' => "OpenFile('?site_select='+value)",  'focus' => true );
$output = call_form ( $arr );
echo $output['html'];

?>
</div>
	<div style='<?=$style_div?>'>
		<iframe src="../../ssi_finder/index.php?CKEditor=<?=$_GET['CKEditor']?>&type=Images&CKEditorFuncNum=<?=$_GET['CKEditorFuncNum']?>&langCode=de"  style='<?=$style?>' name="Xplorer" frameborder=0></iframe>
	</div>
	
	<script src="../smart_form/jquery-ui/jquery.min.js"></script>
	<script src="../smart_form/semantic/dist/semantic.min.js"></script>
	<script>
		function getUrlParam(paramName)
		{
		  var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i') ;
		  var match = window.location.search.match(reParam) ;
		 
		  return (match && match.length > 1) ? match[1] : '' ;
		}
		function OpenFile( fileUrl )
		{
			var funcNum = getUrlParam('CKEditorFuncNum');
			window.top.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl);
			window.top.close();
		}
	</script>
	<?=$output['js'];?>
</body>
</html>