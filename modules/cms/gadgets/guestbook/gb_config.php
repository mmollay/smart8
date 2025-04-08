<?php
// use for Admin-modus
include (__DIR__."/../config.php");

// ####################################
// hnGuestbook version 1.0.8
// http://www.hnscripts.com
// ####################################

$guestbook_user_pfad = "/var/www/ssi/smart_users/$company/user$user_id/guestbook";

$data_file = "$guestbook_user_pfad/$guestbook_id/data.txt"; // The data file
$ip_file = "$guestbook_user_pfad/$guestbook_id/ip.txt"; // The IP file

exec ( "mkdir $guestbook_user_pfad" );
exec ( "mkdir $guestbook_user_pfad/$guestbook_id" );
exec ( "touch $data_file");

$script = "index.php"; // the filename of the guestbook

$ausername = "admin"; // Your admin username
$apassword = "admin"; // Your admin password

$gb_title = ""; // The title of your guestbook
                
// $home = "http://www.ssi.at"; //The url of your homepage

$messages_per_page = 10; // The number of entries display per page

$newest_on_top = 1; // 1 = display the newest entries on top, 0 = oldest entries on top

$bad_words = "fuck, shit, bitch"; // filter out the bad words

$lang_file = "languages/gb_german.php"; // Your language file

$flood_protection = "1"; // Flood protection, 0 = Off, 1 = 1 minute

$word_wrap = "70"; // The number of characters displaying per line

$convert_link = "1"; // Convert URL to clickable link. 0 = No, 1 = Yes

$disable_gb = "0"; // Disable the guestbook. 0 = No, 1 = Yes

$spam_protection_code = "das_ist_ein_guter_schutz"; // This code will make it harder for spammers to spam your guestbook. You can change it to anything

$httpd_path = "gadgets/guestbook";

// End Configuration
// ######################################
include ($lang_file);
?>