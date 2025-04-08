<?php
include ('functions.inc');
/**
 * ************************************
 * faq page
 * INFO für Ausblendung alles Elemente für Detailansicht
 * im load_content -> libray/function.php wurde (line 60) manuel eingerichtet über element 'other'
 * *************************************
 */
$faq_id = $_POST['faq_id'];

if (! $faq_id) {
    $content = 'Keine Seite gewählt';
} else {

    require_once ('../config.php');
    // Stellt die Verbindung zur Finder->Webseite her
    $query2 = $GLOBALS['mysqli']->query("SELECT * FROM ssi_paneon.faq LEFT JOIN ssi_paneon.faq2tag ON faq.faq_id = faq2tag.faq_id WHERE faq.faq_id = '$faq_id' ");
    $array = mysqli_fetch_array($query2);
    $set_icon = $array['category'];
    $foto_owner = $array['foto_owner'];

    if (! $foto_owner)
        $foto_owner = 'Unbekannt';

    if ($array['image']) {
        // if (file_exists ( $array ['image'] ) ) {
        // $get_image = "<img class='ui centered medium image' src='{$array ['image']}'>";
        $get_image = "<div class='ui center aligned container'><a  href='{$array ['image']}' data-tooltip='Bild vergrößern' data-fancybox=''><img class='ui centered large bordered rounded image' src='{$array ['image']}'></a></div>";
    } else {
        $get_image = "<div class'ui center aligned container'><i class='circular $set_icon blue huge icon'></i></div>";
    }

    // $content = "<a href class='ui icon button' data-tooltip='Add users to your feed'><i class='share alternate icon'></i></a>";
    // $content [] = set_message ( "Problem", $array ['problem'] );
    // $content [] = set_message ( "Highlight", $array ['highlight'] );
    // $content[] = set_message("Frage", $array['question']);
    $content[] = set_message("Anwort", $array['answer']);
    // echo set_message ("Jahre","blue", $array ['age']." Jahre" );
}

// function
function set_message($title, $text, $icon = '', $color = '', $class = 'segment')
{
    $GLOBALS['count_set_message'] ++;
    // $content ['text'] = "<div class='ui large $color left ribbon label'>$title</div>";
    $content['text'] .= "<div style='text-align:left'>";
    $content['text'] .= "<div id='{$GLOBALS['count_set_message']}'></div><div class='ui header title'>$title</div>$text";
    $content['text'] .= "<br><br><a href='#top'><i class='angle double up icon'></i> Top</a><div class='ui divider'></div>";
    $content['text'] .= "</div>";

    if (! $icon)
        $icon = "angle double right";

    $content['list'] = "<div class='item'>";
    $content['list'] .= "<i class='$icon icon'></i>";
    $content['list'] .= "<div class='content'><a href='#{$GLOBALS['count_set_message']}'>$title</a></div>";
    $content['list'] .= "</div>";

    return $content;
}

$buttons = "
<div class='ui equal width grid'>
<div class='column'>
<a class='ui icon fluid button circular' href='?' data-position='right center' data-tooltip='Zurück zur Übersicht'><i class='arrow circle left icon'></i> Übersicht</a> 
</div>
<div class='column'>
   <a class='ui icon fluid green button circular' target='contact' href='https://www.paneon.net/Kontakt.php' data-position='right center' data-tooltip='Kontaktiere uns!'><i class='envelope icon'></i> Kontaktiere uns!</a>
</div>";

$buttons .= "
<div class='column'>
<a class='ui icon fluid blue button circular' onclick=\"call_semantic_form('','share_link','gadgets/paneon_faq/share_link.php?share_id=$faq_id','faq_list','1');\" data-position='right center' data-tooltip='Link teilen'><i class='share  alternate icon'></i> Teilen
</a>
</div>";
$buttons .= "</div>";

$array['highlight'] = nl2br($array['highlight']);

$nachweistext = '
<div>
<div id="m_7330661018388838514gmail-sort_18921">
<div id="m_7330661018388838514gmail-sort_div_18921">
<div style="text-align:left">
<div id="m_7330661018388838514gmail-18921">
<div>
<h3><strong>Allgemeine Hintergrundinformation:</strong></h3>

<h3><span style="color:#3498db">Wir bestehen aus nichts anderem als Nahrung!</span></h3>

<div>Wir (und unsere Tiere) bestehen sprichw&ouml;rtlich genau aus jenen Nahrungsmitteln die wir in den letzten Monaten und Jahren zu uns genommen haben.<br />
<br />
Aufgrund und mit Hilfe der Nahrungsaufnahme erneuert sich der K&ouml;rper innerhalb von wenigen Monaten (Tier) bis wenigen Jahren (Mensch) fast komplett durch den nat&uuml;rlichen Zellstoffwechsel.<br />
<br />
In einem Jahr beim Menschen, in drei Monaten bei Hund und Katze, sind wir durch den Stoffwechsel daher praktisch fast g&auml;nzlich runderneuert.<br />
<br />
So ist klar, dass die Qualit&auml;t unseres K&ouml;rpers mit der Qualit&auml;t unserer Nahrung praktisch identisch ist. Die Gesundheit eines Wurfes/Kindes ist die Qualit&auml;t der Nahrung des Muttertieres/der Mutter. Nat&uuml;rlich stets gemeinsam mit guter Pflege und Lebensweise.<br />
&nbsp;</div>

<div><strong>Die gute Nachricht:</strong><br />
Was ist zu tun, um Gesundheit zu bewahren? Ganz einfach:<br />
&nbsp;</div>

<div><strong>Die Ursachen der Krankheit vermeiden:</strong><br />
a) das Bindegewebe st&auml;rken, durch Zufuhr von hochwertigen N&auml;hrstoffen</div>

<p>b) das Immunsystem st&auml;rken, durch Entgiftung und spezielle N&auml;hrstoffe<br />
c) die Belastungen vermeiden durch hochreine Nahrung plus Entgiftung</p>

<div>&nbsp;</div>

<div><strong>Schlusswort:</strong><br />
Naturgesundheit ist der nat&uuml;rliche Zustand unseres K&ouml;rpers.<br />
Wenn nat&uuml;rliche Lebensmittel in den K&ouml;rper kommen, kann dieser nicht anders, als gesund zu<br />
sein. Wenn wir ein liebevolles und stressfreies Leben voraussetzen.<br />
oder kurz:</div>

<h3><span style="color:#3498db"><strong>Gift raus, Vitalstoffe rein!<br />
Stress raus, Freude rein!</strong></span><br />
&nbsp;</h3>
</div>
</div>
</div>
</div>
</div>
<div id="m_7330661018388838514gmail-sort_18922">
<div id="m_7330661018388838514gmail-sort_div_18922">
<div>
<div id="m_7330661018388838514gmail-18922">
<div style="text-align:justify">Fotonachweis: ' . $foto_owner . ' Es gelten der Disclaimer und die Allgemeine Gesch&auml;ftsbedingungen der PANEON GmbH (<a href="http://www.paneon.net" target="_blank">www.paneon.net</a> ), Irrtum und Schreibfehler vorbehalten. Es handelt sich um einen authentischen Erlebnisbericht, Namen und Adressen liegen vor, die Begebenheiten sind<br />
glaubw&uuml;rdig, da sie mit der &uuml;ber l&auml;ngere Zeit laufenden Korrespondenz und Warenlieferungen exakt &uuml;bereinstimmen. Der Bericht ist exemplarisch. In &auml;hnlichen F&auml;llen k&ouml;nnen nicht 100% die gleichen Ergebnisse erwartet werden, da die Randbedingungen und die genetische Disposition stark variieren k&ouml;nnen. Es ist peinlich darauf zu achten, dass nicht &uuml;ber Leckerlis oder Zuf&uuml;tterung zuviel Getreide oder gef&auml;hrliche Chemie-Hilfsstoffe den Metabolismus belasten, ein liebevolles, ruhiges, stressfreies Umfeld ist ebenso wichtig.</div>
</div>
</div>
</div>
</div>
</div>
';

echo "<div class='smart_content_element'><div class='element_padding'>";
echo "
	$get_image<br>
	<div align=center>
	<div class='ui huge  header'>{$array ['question']}</div>";

// echo "Thema: {$array['title']}<br>";

$array_faq = call_array_faq_tags($faq_id);

if (is_array($array_faq)) {
    foreach (call_array_faq_tags($faq_id) as $value) {
        if ($output_tags)
            $output_tags .= ",";
        $output_tags .= $value;
    }
}

if ($output_tags)
    echo "Stichworte: $output_tags";

echo "<br><br><br>";
echo "$buttons";
foreach ($content as $value)
    echo $value['text'];
echo "{$content['text']}";
echo $buttons;
echo "<br>";
// echo "$nachweistext";

echo "
<div id='share_link' class='ui modal small'>
	<i class='close icon'></i>
	<div class='header'>Share Link</div>
	<div class='content'></div>
</div>
";

echo "</div></div>";