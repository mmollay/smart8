<?php
if ($show_all)
	$_SESSION['challange_show_all'] = $show_all;

if (! $GLOBALS['load_miniplay']) {
	// Nur laden wenn die Seite erzeugt wird
	$add_css2 .= "\n<style type='text/css'>@import 'gadgets/miniplayer/css/miniplayer.css'; </style>";
	$add_path_js .= "\n<script type='text/javascript' src='gadgets/miniplayer/inc/jquery.jplayer.min.js'></script>";
	$add_path_js .= "\n<script type='text/javascript' src='gadgets/miniplayer/inc/jquery.mb.miniPlayer.js'></script>";
	
	$add_js2 .= "
	$(function () {
	
		$(\".audio\").mb_miniPlayer({
			width: 240,
			inLine: false,
			onEnd: playNext
		});
	
		function playNext(player) {
			var players = $(\".audio\");
			document.playerIDX = (player.idx <= players.length - 1 ? player.idx : 0);
			players.eq(document.playerIDX).mb_miniPlayer_play();
		}
	
	});
";
}
$GLOBALS['load_miniplay'] = TRUE;
if (!$color) $color = 'gray';
$output = "<a class=\"audio {skin:'$color shadow', autoPlay:false,showRew:false, showTime:false}\" href=\"$explorer\">$title</a>";