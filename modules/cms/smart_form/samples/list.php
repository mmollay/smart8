<?php
//Call Function
include ("../include_list.php");
$array = call_list ('inc/config.php','inc/mysql.php' );
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
	<div align=center><?=$array['html']?></div>

	<script src="../jquery.min.js"></script>
	<script src="../semantic/dist/semantic.min.js"></script>
	<script>var smart_form_wp = '../'</script>
	<script src="../smart_list.js"></script>
	<?=$array['js']?>
</body>
</html>