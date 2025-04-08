<?php
session_start();
$_SESSION['test']=1;
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Session_test mit Ajax für IE 11</title>
</head>
<body>
<br><br>
<div align=center><div id = 'test'></div></div>

	<script src="jquery.min.js"></script>
	<script>

	$(document).ready( function () {
		$.ajax( {
				url      : "index2.php",
				global   : false,
				type     : 'POST',
				dataType : 'html',
				success  : function(data) {
					$('#test').html(data);
				}
			});
	});

</script>
	
</body>
</html>