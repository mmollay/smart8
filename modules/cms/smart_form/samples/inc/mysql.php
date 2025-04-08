<?php
$cfg_mysql['user']     = 'root';
$cfg_mysql['password'] = '';
$cfg_mysql['server']   = 'localhost';
$cfg_mysql['db']       = 'ssi_smart1';

$gaSql['link'] = mysql_pconnect( $cfg_mysql['server'], $cfg_mysql['user'], $cfg_mysql['password']  ) or die( 'Could not open connection to server' );
$GLOBALS['mysqli']->query("SET NAMES 'utf8'");
mysql_select_db( $cfg_mysql['db'], $gaSql['link'] ) or die( 'Could not select database '. $cfg_mysql['db']);