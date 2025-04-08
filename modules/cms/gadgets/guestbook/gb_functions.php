<?
session_start ();
include ("gb_config.php");
?>
<html>
<head>
<title>Admin Panel</title>
<link href="gb_style.css" rel="stylesheet" type="text/css"
	media="screen" />
</head>
<body>
<?
if (isset ( $_SESSION ['admin'] )) {
	
	$bantext = $_REQUEST ['bantext'];
	$banip = $_REQUEST ['banip'];
	
	if ($banip) {
		
		if (! (file_exists ( $ip_file ))) {
			fopen ( $ip_file, "w" ) or die ( "Can't open the file $ip_file" );
			;
			$data = file ( $ip_file );
		} else {
			fopen ( $ip_file, "r" );
			$data = file ( $ip_file );
		}
		
		?>
<form method="POST" action="gb_functions.php?banip=none">
		<b><? echo $la35; ?>
</b><br />
		<? echo $la36; ?>.<br />
		<p>
			<textarea rows="14" name="iptext" cols="20"><?
		for($i = 0; $i < sizeof ( $data ); $i ++) {
			$iplist = $iplist . trim ( $data [$i] ) . "\n";
		}
		if ($banip == "none") {
			$iplist = $iptext;
		} else if ($banip == "banip") {
			$iplist = $iplist;
		} else {
			$iplist = $iplist . $banip;
		}
		
		echo $iplist;
		?></textarea>
		</p>
		<p>
			<input type="submit" value="<? echo $la37; ?>" name="SaveB"> <?
		if ($SaveB) {
			$data = fopen ( $ip_file, "w" );
			flock ( $data, LOCK_EX );
			fwrite ( $data, $iplist );
			flock ( $data, LOCK_UN );
			fclose ( $data );
			
			echo ("<font color = red> <b>$la38 </b> </font><br>");
		}
		?></p>
	</form>
<?
	}
} //end session
?>
</body>
</html>
