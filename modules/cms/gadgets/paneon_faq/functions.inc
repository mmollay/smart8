<?php
// Functions for this module
$array_category = array('human' => '<i class=\'icon user\'></i> Mensch','dog' => '<i class=\'icon dog\'></i> Hund','cat' => '<i class=\'icon cat\'></i> Katze','horse' => '<i class=\'icon horse\'></i> Pferd');

// 'alien'=>'<i class=\'icon reddit alien\'></i> Alien'
/*
 * mm@ssi.at 20.04.2011
 * Output an array from a file (wert1 = 234).... array(wert1 => 234)
 */
function file2array_save($file)
{
    if (! is_file($file)) {
        echo "File - $file - nicht erreichbar";
        return;
    }
    $handle = fopen($file, "r");
    $ii = 0;
    while (! feof($handle)) {
        $ii ++;
        if ($ii > 1) {
            $array[] = fgets($handle, 4096); // erste Zeile wird nicht ins Array aufgenommen
        } else {
            $array2[] = fgets($handle, 4096); // erste Zeile wird nicht ins Array aufgenommen
        }
    }
    fclose($handle);
    return $array;
}

// Aufruf aller TAGS
function call_array_faq_tags($faq_id = false)
{
    if ($faq_id)
        $mysql_add = "WHERE faq.faq_id = '$faq_id' ";

    $mysql_query = $GLOBALS['mysqli']->query("
	SELECT tag.tag_id tag_id, tag.title, tag.color, COUNT(IF(faq.faq_id,1,null)) count FROM ssi_paneon.tag 
		LEFT JOIN (ssi_paneon.faq2tag, ssi_paneon.faq) ON (faq.faq_id = faq2tag.faq_id AND faq2tag.tag_id = tag.tag_id)
        $mysql_add
		GROUP BY tag.tag_id
			ORDER BY tag.title
	") or die(mysqli_error($GLOBALS['mysqli']));

    while ($mysql_fetch = mysqli_fetch_array($mysql_query)) {
        $tag_id = $mysql_fetch['tag_id'];

        $tag_name = "";
        if ($mysql_fetch['color'])
            $tag_name .= "<a class='ui " . $mysql_fetch['color'] . " empty circular label'>d</a>";

        $tag_name .= $mysql_fetch['title'] . " (" . $mysql_fetch['count'] . ")";
        $tag_array[$tag_id] = "$tag_name";

        $tag_name_public[$tag_id] .= $mysql_fetch['title'];
    }

    if ($faq_id)
        return $tag_name_public;
    else
        return $tag_array;
}

?>