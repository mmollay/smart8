<?php
/*
 * FUNCTION - CALL Dir -structur from an Folder
 * mm@ssi.at 17.11.2011
 */
$_SESSION ['IgnoreFileList'] = array (
		'thumbnail',
		'thumbcollage',
		'thumb',
		'thumb_gallery',
		"autoresize",
		'pikachoose_gallery' 
);

function directoryToArray($directory, $recursive, $level) {
	if (! is_dir ( $directory ))
		return;
	$array_items = array ();
	
	if ($handle = opendir ( $directory )) {
		$level ++;
		while ( false !== ($file = readdir ( $handle )) ) {
			if ($file != "." && $file != "..") {
				if (! in_array ( $file, $_SESSION ['IgnoreFileList'] ) && is_dir ( $directory . "/" . $file )) {
					if ($recursive) {
						$array_items = array_merge ( $array_items, directoryToArray ( $directory . "/" . $file, $recursive, $level ) );
					}
					for($aa = 1; $aa < $level; $aa ++) {
						$space .= "&nbsp;&nbsp;&nbsp;";
					}
					
					$file_name = $file;
					$file = $directory . "/" . $file;
					$file = preg_replace ( "/\.\.\/\.\.\/\.\.\/\.\.\//", "../../", $file );
					if ($level == 1)
						$array_items [$file] = $file_name;
					else
						$array_items [$file] = "$space'&rarr; " . $file_name;
					$space = '';
				} else {
					// $file = $directory . "/" . $file;
					// $array_items[] = preg_replace("/\/\//si", "/", $file);
				}
			}
		}
		closedir ( $handle );
	}
	return $array_items;
}