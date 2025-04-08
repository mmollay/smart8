<?php
//Call Function
include_once ("../include_form.php");

//Config - Fields
$arr['form'] = array ( 'id' => 'form_newsletter' , 'action' => 'inc/handler_newsletter.php', 'class' => 'segment', 'width' => '500',  'align' =>'center' );
$arr['ajax'] = array ( 'success' => "alert(data)" , 'dataType' => 'html' );
$arr['field']['email'] = array ( 'type' => 'input' , 'placeholder' => "Email" , 'validate' => 'email' , 'label_right_class' => "button submit" , 'label_right' => 'Absenden' );
$arr['field'][] = array ( 'type' => 'text' , 'value'=>'Daten werden streng vertraulich behandelt' );
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
	<br><br><div class='center aligned header green ui'>Newsletter</div>
	<?=$output_form['html']?>
	<script src="../jquery.min.js"></script>
	<!-- 	<script src="../smart_form.js"></script> -->
	<script src='../jquery-upload/js/vendor/jquery.ui.widget.js'></script>
	<script src="../semantic/dist/semantic.min.js"></script>
	<?=$output_form['js']?>
</body>
</html>