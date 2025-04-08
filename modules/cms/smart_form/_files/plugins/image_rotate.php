<?php
ini_set ( 'display_errors', 1 );
ini_set ( 'display_startup_errors', 1 );
error_reporting ( E_ERROR | E_PARSE );
error_reporting ( 1 );

if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
	include_once ('../config/config.php');
	$filename = $root . $_POST ['url'];

	// Determine the image type based on the file extension
	$imageType = strtolower ( pathinfo ( $filename, PATHINFO_EXTENSION ) );

	// Load the image based on its type
	switch ($imageType) {
		case 'jpeg' :
		case 'jpg' :
			$image = imagecreatefromjpeg ( $filename );
			break;
		case 'png' :
			$image = imagecreatefrompng ( $filename );
			break;
		case 'gif' :
			$image = imagecreatefromgif ( $filename );
			break;
		case 'bmp' :
			$image = imagecreatefrombmp ( $filename );
			break;
		case 'webp' :
			$image = imagecreatefromwebp ( $filename );
			break;
		default :
			echo "Unsupported image format.";
			exit ();
	}

	// Check if the 'direction' parameter is set
	$direction = isset ( $_POST ['direction'] ) ? $_POST ['direction'] : 'right';

	// Determine the angle of rotation
	$angle = ($direction === 'right') ? - 90 : 90;

	// Perform the rotation
	$rotatedImage = imagerotate ( $image, $angle, 0 );

	// Save the rotated image based on its type
	switch ($imageType) {
		case 'jpeg' :
		case 'jpg' :
			imagejpeg ( $rotatedImage, $filename, 100 );
			break;
		case 'png' :
			imagepng ( $rotatedImage, $filename );
			break;
		case 'gif' :
			imagegif ( $rotatedImage, $filename );
			break;
		case 'bmp' :
			imagebmp ( $rotatedImage, $filename );
			break;
		case 'webp' :
			imagewebp ( $rotatedImage, $filename );
			break;
	}

	// Free up memory
	imagedestroy ( $image );
	imagedestroy ( $rotatedImage );

	$bildPfad = dirname ( $filename );

	touch ( $bildPfad );
	echo "ok";
}

?>