<?php
// echo "<div id='productTitle'>Title</div>";
// echo "<div id='pzr-features-containers'>Features</div>";
// echo "<div id='productDescription'>dsfadsfasdfsdf<br>awdsfasdgasdgasdgsdg<br>sdasdgasdgsdgasdgasdgasdgasgsgasd dsaasdfasdg<br></div>";
// echo "<div id='altImages'>
// <img scr='/smart_users/ssi/user40/explorer/13/1932299_727343890623104_1153650400_n.jpg'>
// <img scr='/smart_users/ssi/user40/explorer/13/1932299_727343890623104_1153650400_n.jpg'>
// <img scr='/smart_users/ssi/user40/explorer/13/1932299_727343890623104_1153650400_n.jpg'>
// <img scr='/smart_users/ssi/user40/explorer/13/1932299_727343890623104_1153650400_n.jpg'>
// </div>";
// echo "<div id='priceblock_ourprice'>20 Euro</div>";
// echo "<div id='feature-bullets'><li>Bullets<li>Bullets<li>Bullets<li>Bullets<li>Bullets</div>";
// exit;

if ($_POST['product_id']) {
	echo file_get_contents ( "https://www.amazon.de/dp/{$_POST['product_id']}" );
} else
	echo "error";
// echo file_get_contents("https://www.amazon.de/Lotuscrafts-Meditationsmatte-ZABUTON-Baumwolle-zertifiziert/dp/B005Q6S97E");
// echo file_get_contents("https://www.amazon.de/KlarGeist-Meditationskissen-Yogakissen-Ungelinkige-Zen-Kissen/dp/B00U7GC2NU/ref=pd_sim_200_7?_encoding=UTF8&psc=1&refRID=364WZ22JTHGRV5QBNVW7");
?>