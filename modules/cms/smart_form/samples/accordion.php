<?
include_once ("../include_form.php");

// Config - Fields
$arr['form'] = array ( 'id' => 'form_newsletter' , 'action' => 'inc/handler.php' , 'width' => '800' , 'align' => 'center' );

// Akkordion
$arr['field'][] = array ( 'type' => 'accordion' , 'class' => 'styled fluid' , 'title' => 'Accordion1' , 'active' =>true );

$arr['field'][] = array ( 'type' => 'accordion' , 'class' => 'inverted' , 'title' => 'Sub 1' );
$arr['field'][] = array ( 'type' => 'content' , 'text' => 'text 1_1' );
$arr['field'][] = array ( 'type' => 'accordion' , 'split' => true, 'title' => 'Sub 2' );
$arr['field'][] = array ( 'type' => 'content' , 'text' => 'text 1_2' );
$arr['field'][] = array ( 'type' => 'accordion' , 'close' => true );

$arr['field'][] = array ( 'type' => 'accordion' , 'split' => true , 'title' => 'Accordion2' );

$arr['field'][] = array ( 'type' => 'content' , 'text' => 'test von Martin Mollay' );
$arr['field'][] = array ( 'type' => 'accordion' , 'close' => true );

$output_form = call_form ( $arr );
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Form | Semantic UI | PHP</title>
<link rel="stylesheet" href="../semantic/dist/semantic.min.css">
</head>
<body>

	<br>
	<?=$output_form['html']?>
	<script src="../jquery.min.js"></script>
	<!-- 	<script src="../smart_form.js"></script> -->
	<script src="../semantic/dist/semantic.min.js"></script>
	<?=$output_form['js']?>
</body>
</html>