<?php
include(__DIR__.'/../config.php');
$cfg_mysql ['db'] = 'ssi_faktura94';

mysqli_select_db ( $GLOBALS['mysqli'], $cfg_mysql['db'] ) or die ( 'Could not select database' . $cfg_mysql['db'] );