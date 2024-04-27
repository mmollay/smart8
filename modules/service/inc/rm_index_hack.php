<?php
//mm@ssi.at am 02.06.2016
//Entfernt "index.html & index.php - wenn diese im gleichen Verzeichnis sind

$hacked_folder = "/var/www/"; 
//$hacked_folder = "/http-public/acid.at";

$GLOBALS['hack_count'] = 0;

function dirToArray($dir) {

	$result = array();

	$cdir = scandir($dir);

	$check_hack1 = $check_hack2 = '';

	foreach ($cdir as $key => $value)
	{
		if ($value == 'index.html') { $check_hack1 = true;  }
		if ($value == 'index.php') { $check_hack2 = true;  }
		if ($check_hack1 and $check_hack2) {
			echo "Infizierter Folder: $dir (".date ('l jS \of F Y h:i:s A', filemtime("$dir")).")<br>";
			exec ("rm $dir/index.html") ;
			exec ("rm $dir/index.php");
			$GLOBALS['hack_count']++;
			$check_hack1 = $check_hack2 = '';
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
echo "<hr>";
//echo "<pre>";
//print_r(dirToArray("/http-public/smart_users/ssi/user"));
dirToArray($hacked_folder);
//echo "</pre>";
echo "Gel&ouml;schte Hacks:". $GLOBALS['hack_count'];
