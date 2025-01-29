<?php
session_start();

echo "Session Info:\n";
echo "-------------\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Name: " . session_name() . "\n\n";

echo "Session Contents:\n";
echo "----------------\n";
print_r($_SESSION);

echo "\nPHPINFO Session Details:\n";
echo "----------------------\n";
phpinfo(INFO_VARIABLES);
