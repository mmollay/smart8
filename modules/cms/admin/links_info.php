<?
function set_link($url, $title, $text, $img) {
	return "
	<div class='item'>
		<a href='$url' target='new' class='ui tiny image'><img src='$img'></a><div class='content'>
		<a class='header' href='$url' target='new' >$title</a><div class='description'>$text</div>
	</div></div>";
}

echo '<div class="ui divided items">';
echo set_link('https://www.firmenwebseiten.at/datenschutz-generator/','Datenschutzgenerator','Gestalte dir deine eigenen Datenschutzbestimmungen','https://www.firmenwebseiten.at/wp-content/uploads/firmenwebseiten-logo-2018-1.png');
echo set_link('https://www.cssportal.com/css3-text-shadow-generator/', 'Shadow-Generator', 'This CSS3 text shadow generator will help you learn and design shadows for your hyperlinks, headings ar any text you have on a webpage. The CSS code for shadows requires four values, they are: Horizontal Length, Vertical Length, Blur Radius and Shadow Color.', 'https://www.cssportal.com/images/cssportal.png');
echo set_link('https://onlinepngtools.com/create-transparent-png', 'Png transparency creator', "World's simplest online Portable Network Graphics transparency maker. Just import your PNG image in the editor on the left and you will instantly get a transparent PNG on the right. Free, quick, and very powerful. Import a PNG – get a transparent PNG. Created with love by team Browserling.", 'https://onlinepngtools.com/images/logo-onlinepngtools.png');
echo "</div>";