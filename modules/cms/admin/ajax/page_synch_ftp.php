<?php
// session_start();
include ('page_generate.php');

$page_id = $_SESSION['smart_page_id'];
$user_id = $_SESSION['user_id'];

if (! $page_id) {
	echo "Kein page_id vorhanden";
	return;
}

// $source_file = $user_page_path;

$host = $_POST['ftp_host'];
$username = $_POST['ftp_user'];
$password = $_POST['ftp_password'];
$path = $_POST['ftp_path'];
$sync_options = $_POST['sync_options'];

/*
 * TEST mm@ssi.at 15.10.2012
 * $host = "www.ssi.at";
 * $username = "ftp_1";
 * $password = "mcfly2";
 */

$source_file = "../../../../{$_SESSION['path_template']}/user$user_id/page$page_id/";
$destination_file = $path;

/*
 * Übertragen des jeweiligen Paketes
 */
if ($sync_options == 'all')
	$strNoTransferFile = array ( '' );
elseif ($sync_options == 'text_img')
	$strNoTransferFile = array ( 'gadgets' , 'js' , 'ajax' , 'bazar' , 'jquery-ui' , 'ssi_form2' );
elseif ($sync_options == 'text')
	$strNoTransferFile = array ( 'gadgets' , 'js' , 'ajax' , 'bazar' , 'jquery-ui' , 'ssi_form2' , 'explorer' );
	
	// Daten via ftp übertragen
include ("../ftp_upload.inc");
// --------------------------------------------------------------------
// THE TRIGGER
// --------------------------------------------------------------------
// set the various variables

$ftproot = "/$destination_file";
$srcroot = realpath ( $source_file );
$srcrela = "/";

// connect to the destination FTP & enter appropriate directories both locally and remotely
$ftpc = ftp_connect ( $host );
$ftpr = ftp_login ( $ftpc, $username, $password );

if ($ftpr) {
	// start ftp'ing over the directory recursively
	ftpRec ( $srcrela );
	// close the FTP connection
	ftp_close ( $ftpc );
	$str_meldung = "ok";
}
?>