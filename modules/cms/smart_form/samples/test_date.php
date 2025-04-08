<?
//Call Function
include_once ("../include_form.php");

//Config - Fields
for ($i =1; $i<30; $i++) {
	$output_form['html'] .= "<input type='text' id='test$i'><br><br>";
}
$output_form['html'] .= "<input type='text' id='data' placeholder='datum'>";
$output_form['js'] .="<script>$('#data').pickadate()</script>";
?>

<!DOCTYPE html>
<html style="height:auto;" >
<head>
<meta charset="utf-8" />
<title>Form | Semantic UI | PHP</title>
<link rel="stylesheet" href="../semantic/dist/semantic.min.css">
<link rel='stylesheet' type='text/css' href='../pickadate/themes/default.css' />
<link rel='stylesheet' type='text/css' href='../pickadate/themes/default.date.css' />
<link rel='stylesheet' type='text/css' href='../pickadate/themes/default.time.css' />
</head>
<body>
	<br><br><div class='center aligned header green ui'>Mögliche Felder</div>
	<?=$output_form['html']?>
	<script src="../semantic/dist/semantic.min.js"></script>
	<script src="../jquery.min.js"></script>
	<script type='text/javascript' src='../pickadate/picker.js'></script>
	<script type='text/javascript' src='../pickadate/picker.date.js'></script>
	<script type='text/javascript' src='../pickadate/picker.time.js'></script>
	<?=$output_form['js']?>
</body>
</html>