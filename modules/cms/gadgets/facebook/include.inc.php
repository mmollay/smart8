<?php

// Webseite name + page_name
if ($select_plugin == 'share_button') {
	
	$output = '<iframe src="https://www.facebook.com/plugins/share_button.php?href=http%3A%2F%2F' . call_static_page_url () . '&amp;layout=button_count&amp;appId=224205327626181" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:120px; height:20px;" allowTransparency="true"></iframe>';
	// $output = '<iframe src="//www.facebook.com/plugins/share_button.php?href=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;layout=button_count&amp;appId=224205327626181" scrolling="no" frameborder="0" style="border:none; overflow:hidden;" allowTransparency="true"></iframe>';
} elseif ($select_plugin == 'like_share_button') {
	$output = '
	<div id="fb-root">&nbsp;</div>
	<script>(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) {return;}
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/de_DE/all.js#xfbml=1&appId=224205327626181";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, \'script\', \'facebook-jssdk\'));</script>		
	<div class="fb-like" data-href="' . $fb_link . '" data-layout="button_count" data-send="true" data-show-faces="false" data-width="460">&nbsp;</div>
	';
} else
	$output = '<iframe src="https://www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2F' . $fb_link . '&amp;width&amp;height=62&amp;colorscheme=light&amp;show_faces=false&amp;header=true&amp;stream=false&amp;show_border=true&amp;appId=224205327626181" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:auto; height:62px;" allowTransparency="true"></iframe>';

//Noch nicht aktiviert
// Google + 
$googleplus = '
<!-- Platzieren Sie dieses Tag an der Stelle, an der die +1-Schaltfläche angezeigt werden soll. --><g:plusone size="small"></g:plusone> <!-- Platzieren Sie diese Render-Anweisung an einer geeigneten Stelle. --><script type="text/javascript">
window.___gcfg = {lang: \'de\'};

(function() {
var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
po.src = \'https://apis.google.com/js/plusone.js\';
var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
})();
</script>';?>

