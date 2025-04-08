<?php
$report_id = $_POST ['report_id'];

if (! $report_id) {
	$content = 'Keine Seite gewählt';
} else {

	require_once ('../config.php');
	//Stellt die Verbindung  zur Finder->Webseite her
	$query2 = $GLOBALS ['mysqli']->query ( "SELECT * FROM ssi_paneon.report LEFT JOIN ssi_paneon.report2tag ON report.report_id = report2tag.report_id WHERE report.report_id = '$report_id' " );
	$array = mysqli_fetch_array ( $query2 );
	$set_icon = $array ['category'];

	if ($array ['image']) {
		//if (file_exists ( $array ['image'] ) ) {
		//$get_image = "<img class='ui centered medium image' src='{$array ['image']}'>";
		$get_image = "<div class='ui center aligned container'><a  href='{$array ['image']}' data-tooltip='Bild vergrößern' data-fancybox=''><img class='ui centered small bordered rounded image' src='{$array ['image']}'></a></div>";
	} else {
		$get_image = "<div class'ui center aligned container'><i class='circular $set_icon blue huge icon'></i></div>";
	}

	// 	$content = "<a href class='ui icon button' data-tooltip='Add users to your feed'><i class='share alternate icon'></i></a>";
	$content [] = set_message ( "Problem", $array ['problem'] );
	$content [] = set_message ( "Highlight", $array ['highlight'] );
	$content [] = set_message ( "Brief", $array ['text'] );
	$content [] = set_message ( "Anwort", $array ['answer'] );
	//echo set_message ("Jahre","blue", $array ['age']." Jahre" );
}

//function
function set_message($title, $text, $icon = '', $color = '', $class = 'segment') {
	$GLOBALS ['count_set_message'] ++;
	//$content ['text'] = "<div class='ui large $color left ribbon label'>$title</div>";
	$content ['text'] .= "<div id='{$GLOBALS['count_set_message']}'></div><div class='ui header title'>$title</div>$text";
	$content ['text'] .= "<br><br><a href='#top'><i class='angle double up icon'></i> Top</a><div class='ui divider'></div>";

	if (! $icon)
		$icon = "angle double right";

	$content ['list'] = "<div class='item'>";
	$content ['list'] .= "<i class='$icon icon'></i>";
	$content ['list'] .= "<div class='content'><a href='#{$GLOBALS['count_set_message']}'>$title</a></div>";
	$content ['list'] .= "</div>";

	return $content;
}
?>

<div class="ui center aligned container">

	<br>
	<div id='top'></div>
	<div class='ui huge header'><?=$array ['title']?></div>
		<?=$get_image?>
		<br>

	<div class="ui left aligned  container">
		<div class="ui divider"></div>
		<div class="ui large list"><?php foreach  ($content as $value) { echo $value['list']; } ?></div>
		<div class="ui divider"></div>
			<?php foreach  ($content as $value) { echo $value['text']; } ?>
		<?=$content['text']?>
		</div>
</div>
<div id='share_link' class='ui modal small'>
	<i class='close icon'></i>
	<div class='header'>Share Link</div>
	<div class='content'></div>
</div>
</div>
<div style='position: absolute; z-index: 1000; padding: 20px;'>
	<a class='ui icon  button' href='?' data-position='right center'
		data-tooltip='Zurück zur Übersicht'><i class="arrow circle left icon"></i> Übersicht</a> <a
		class='ui icon  blue button'
		onclick="call_semantic_form('','share_link','gadgets/paneon_report/share_link.php?share_id=<?=$report_id?>','report_list','1');"
		data-position='right center' data-tooltip='Link teilen'><i class="share  alternate icon"></i></a>
</div><br>
