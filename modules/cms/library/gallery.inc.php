<?php
/*
 * FUNCTION - CALL Dir -structur from an Folder
 * mm@ssi.at 17.11.2011
 *
 * UPDATE mm@ssi.at 24.08.2014 - Relativer Pfad wird in einem ARRAY übergeben
 */
$_SESSION ['IgnoreFileList'] = array (
		'.',
		'..',
		'thumbnail',
		'thumbcollage',
		'thumb',
		'thumb_gallery',
		"autoresize",
		'pikachoose_gallery' 
);
function directoryToArray($directory, $recursive, $level, $relative = false) {
	if (! $GLOBALS ['rm_relative_path'])
		$GLOBALS ['rm_relative_path'] = $directory;
	$array_items = array ();
	if ($handle = opendir ( $directory )) {
		$level ++;
		while ( false !== ($file = readdir ( $handle )) ) {
			if (! in_array ( $file, $_SESSION ['IgnoreFileList'] )) {
				if (! in_array ( $file, $_SESSION ['IgnoreFileList'] ) && is_dir ( $directory . "/" . $file )) {
					$array_items = array_merge ( $array_items, directoryToArray ( $directory . "/" . $file, $recursive, $level, $relative ) );
					for($aa = 1; $aa < $level; $aa ++) {
						$space .= "&nbsp;&nbsp;&nbsp;";
					}
					$counter = countfiles ( $directory . "/" . $file );
					//if ($counter) {
						$file_name = $file . "(" . countfiles ( $directory . "/" . $file ) . ")";
						$file = $directory . "/" . $file;
						$file = preg_replace ( "/\.\.\/\.\.\/\.\.\/\.\.\//", "../../", $file );
						if ($relative)
							$file = preg_replace ( "[" . $GLOBALS ['rm_relative_path'] . "]", '', $file );
						
						if ($level == 1)
							$array_items [$file] = $file_name;
						else
							$array_items [$file] = "$space'&rarr; " . $file_name;
						$space = '';
					//}
				} else {
					// $file = $directory . "/" . $file;
					// $array_items[] = preg_replace("/\/\//si", "/", $file);
				}
			}
		}
		closedir ( $handle );
	}
	// echo $GLOBALS['rem_relative_path']."<br>";
	return array_reverse ( $array_items );
}

function countfiles($path) {
	$handle = opendir ( $path );
	while ( $res = readdir ( $handle ) ) {
		if (! in_array ( $res, $_SESSION ['IgnoreFileList'] )) {
			if (is_dir ( $res )) {
			} else {
				$filecount ++;
			}
		}
	}
	if ($filecount <= 0)
		return 0;
	else
		return $filecount;
}

