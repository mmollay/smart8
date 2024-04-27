<?php
/*
 * UDATE 07.01.2019
 * Martin Mollay
 * Remove hacker-"scripts" inner post_contents 
 * Script is startig from crontab -e every 10 min
 * https://stackoverflow.com/questions/30737684/how-to-use-mariadbs-regexp-replace
 * https://mariadb.com/kb/en/library/regexp_replace/
 */

/**
 * ********************************************************
 * Filmservice
 * ********************************************************
 */
$sql_host = 'localhost';
$sql_user = 'user88';
$sql_pass = 'Sdsmegpw21;';

// Verbindung zur Datenbank
$cfg = mysqli_connect ( $sql_host, $sql_user, $sql_pass ) or die ( 'Could not open connection to server' );

mysqli_select_db ( $cfg, 'wordpress88' ) or die ( 'Could not select database ' . $sql_db );
$sql1 = "UPDATE `wp_posts` SET `post_content` = REGEXP_REPLACE( `post_content`,\"<script(.*)</script>\",\"\") WHERE `post_content` IS NOT NULL";
$sql2 = "UPDATE `wp_fsi_posts` SET `post_content` = REGEXP_REPLACE( `post_content`,\"<script(.*)</script>\",\"\") WHERE `post_content` IS NOT NULL";
mysqli_query ( $cfg, $sql1 ) or die ( mysqli_error ( $cfg ) );
mysqli_query ( $cfg, $sql2 ) or die ( mysqli_error ( $cfg ) );

mysqli_select_db ( $cfg, 'wordpress88_3' ) or die ( 'Could not select database ' . $sql_db );
$sql1 = "UPDATE `wpwft_posts` SET `post_content` = REGEXP_REPLACE( `post_content`,\"<script(.*)</script>\",\"\") WHERE `post_content` IS NOT NULL";
mysqli_query ( $cfg, $sql1 ) or die ( mysqli_error ( $cfg ) );

/**
 * ********************************************************
 * Hotel - Zentral
 * ********************************************************
 */
$sql_host = 'localhost';
$sql_user = 'wordpress113';
$sql_pass = 'hotel21;';

$cfg = mysqli_connect ( $sql_host, $sql_user, $sql_pass ) or die ( 'Could not open connection to server' );
// wordpress_113
mysqli_select_db ( $cfg, 'wordpress113' ) or die ( 'Could not select database ' . $sql_db );
$sql1 = "UPDATE `wp_posts` SET `post_content` = REGEXP_REPLACE( `post_content`,\"<script(.*)</script>\",\"\") WHERE `post_content` IS NOT NULL";
mysqli_query ( $cfg, $sql1 ) or die ( mysqli_error ( $cfg ) );

echo "fertig!";