<?php
//mm@ssi.at am 25.06.2016
//Entfernt "x.htm 
date_default_timezone_set ( 'Europe/Belgrade' );
$hacked_folder = "/var/www/ssi/smart_users"; 
//$hacked_folder = "/Applications/XAMPP/xamppfiles/htdocs/smart_users/inbs";
$GLOBALS['rm_file'] = "x.htm";

$GLOBALS['hack_count'] = 0;

function dirToArray($dir) {
	$result = array();
	$cdir = scandir($dir);

	foreach ($cdir as $key => $value)
	{
		if ($value == $GLOBALS['rm_file']) { 
			echo "Infizierter Folder: $dir (".date ('l jS \of F Y h:i:s A', filemtime("$dir/{$GLOBALS['rm_file']}")).")<br>";
			exec ("rm $dir/{$GLOBALS['rm_file']}") ;
			$GLOBALS['hack_count']++;
		}

		if (!in_array($value,array(".","..")))
		{
			if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
			{
				$result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
			}
			else
			{
				$result[] = $value;
			}
		}
	}
	return $result;
}

echo "Infiziertes File: ". $GLOBALS['rm_file']."<br>";
echo "Zu untersuchende Folderstruktur: ".$hacked_folder."<br>";
echo "Abrufdatum: ". date('l jS \of F Y h:i:s A');
//echo "<pre>";
//print_r(dirToArray("/http-public/smart_users/ssi/user"));
echo "<hr>";
dirToArray($hacked_folder);
//echo "</pre>";
echo "<br>Gel&ouml;schte Hacks: ". $GLOBALS['hack_count'];
