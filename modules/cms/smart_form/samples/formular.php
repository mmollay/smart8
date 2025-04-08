<?
include_once ("../include_form.php");
$rnd = mt_rand ();
$upload_dir = "/var/www/ssi/smart_users/formular/$rnd/"; // Bei Upload muss der absolute Serverpfad verwendet werden
$upload_url = "../../../smart_users/formular/$rnd/";

exec("mkdir $upload_dir");

// Data for Input - field
$arr['value'] = array ( 'title' => 'Title' , 'text' => 'This is a test' );
// Data for Content
$arr['value']['content'] = 'Martin Mollay';
$arr['value']['email'] = 'martin@ssi.at';
$arr['value']['age'] = '10';

// Header
$arr['header'] = array ( 'title' => "<i class='icon newspaper'></i>Formular" , 'text' => 'here you get it' , 'class' => 'small diverding white' , 'segment_class' => 'attached message' );
$arr['footer'] = array ( 'text' => "Test" , 'segment_class' => 'warning attached message' );
// Config - Fields

$arr['form'] = array ( 'id' => 'form_newsletter' , 'action' => 'inc/handler.php' , 'class' => 'segment attached' , 'width' => '800' , 'align' => 'center' );
$arr['ajax'] = array ( 'success' => "$('#show_data').html(data);" , 'dataType' => 'html' );

// location.reload();
// 'beforeSend' => "alert('lets go')"

$arr['field']['content'] = array ( 'type' => 'content' , 'text' => "<b>This is:</b> {data}" );

$arr['field']['age'] = array ( 'type' => 'slider' , 'label' => 'Age' , 'step' => '5' , smooth => true  , 'class' => 'labeled ticked', 'unit' => 'Jahre' ); // labeled ticked range                                                                                                                                                          
$arr['field']['age2'] = array ( 'type' => 'slider' , 'label' => 'Age2' , 'step' => '2', 'max' => 100 , smooth => true , 'class' => 'green' ); // labeled ticked range

$arr['field']['drop'] = array ( 'type' => 'dropdown' , 'label' => 'Drop',  'search' => true, clearable=>true, 'array'=>array('holz'=>'Holz','wasser'=>'Wasser') ); //labeled ticked range

/**
 * **************ACCORDION INPUT*****************************************
 */
$arr['field'][] = array ( 'type' => 'accordion' , 'title' => 'Input' );

$arr['field'][] = array ( 'type' => 'div' , 'class' => 'fields equal width' );
$arr['field']['date'] = array ( 'type' => 'date' , 'label' => 'Datum' , 'value' => '2018-10-10' );
$arr['field']['firstname'] = array ( 'type' => 'input' , 'label' => 'Firstname' , 'placeholder' => 'Firstname' );
$arr['field']['secondname'] = array ( 'type' => 'input' , 'label' => 'Secondname' );
$arr['field'][] = array ( 'type' => 'div_close' );

/**
 * **************ACCORDION UPLOAD***************************************
 */
$arr['field'][] = array ( 'type' => 'accordion' , 'title' => 'Upload' , 'split' => true, 'active' =>true );

$arr['field']['bunker'] = array ( 'label' => 'Daten hochladen' , 'type' => 'uploader' , 
		'upload_dir' => $upload_dir , 'upload_url' => $upload_url , 'accept' => array ( 'png' , 'jpg' , 'jpeg' , 'gif' , 'pdf' , 'zip' , 'mp3' ) , 
		// 'webcam' => array('width'=>'800','height'=>'600'),
		'options' => 'imageMaxWidth:1000,imageMaxHeight:1000' , 
		'button_upload' => array ( 'text' => "Dateien auswählen" , 'color' => 'green' , 'icon' => 'upload' ) , 'card_class' => 'five'	// 'interactions' => array ( 'sortable' => true )
);

$arr['field'][] = array ( 'type' => 'accordion' , 'close' => 'true' );

$arr['field']['email'] = array ( 'type' => 'input' , 'placeholder' => "Email" , 'validate' => 'email' , 'label_right_class' => "button submit" , 'label_right' => 'Absenden' );

$output_form = call_form ( $arr );
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Form | Semantic UI | PHP</title>
<link rel="stylesheet" href="../semantic/dist/semantic.min.css">
<link rel='stylesheet' type='text/css' href='../jquery-upload/css/jquery.fileupload.css'>
</head>
<body>

	<div class='ui segment' id='show_data'></div>
	<br>
	<?=$output_form['html']?>
	
	<script src="../jquery-ui/jquery.min.js"></script>
	<script src="../semantic/dist/semantic.min.js"></script>
	<script src="../smart_form.js"></script>
	<script>var smart_form_wp = '../'</script>
	<script type='text/javascript' src='../jquery-upload/js/load-image.min.js'></script>
	<script type='text/javascript' src='../jquery-upload/js/canvas-to-blob.min.js'></script>
	<script type='text/javascript' src='../jquery-upload/js/jquery.iframe-transport.js'></script>
	<script type='text/javascript' src='../jquery-upload/js/vendor/jquery.ui.widget.js'></script>
	<script type='text/javascript' src='../jquery-upload/js/jquery.fileupload.js'></script>
	<script type='text/javascript' src='../jquery-upload/js/load-image.min.js'></script>
	<script type='text/javascript' src='../jquery-upload/js/jquery.fileupload-process.js'></script>
	<script type='text/javascript' src='../jquery-upload/js/jquery.fileupload-image.js'></script>
	<?=$output_form['js']?>
	
</body>
</html>