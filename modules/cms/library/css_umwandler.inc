<?php

// mm@ssi.at: 06.12.2016 (Update: url http://xxx nun zu gelassen
function css_umwandler2($matches)
{

    // Diese Arrays wird ein "px" angefuehrt
    $array_style_px = array(
        'width',
        'max-height',
        'min-height',
        'height',
        'top',
        'left',
        'bottom',
        'margin-top',
        'margin-bottom',
        'margin-right',
        'margin-left',
        'font-size',
        'padding',
        'padding-top',
        'padding-left',
        'padding-right',
        'padding-top',
        'padding-bottom',
        'margin',
        'line-height',
        'max-width',
        'margin-top'
    );

    // Pruefen ob Wert ein Array ist
    if (is_array($matches))
        $array = $matches[1];
    else
        $array = $matches;
    $array1 = preg_split("/;/", $array);
    foreach ($array1 as $value1) {
        $array2 = preg_split("~:~", $value1, 2);
        $wert_links = trim($array2[0]);
        $wert_rechts = trim($array2[1]);

        if ($wert_rechts) {
            if (preg_match("/url\(\)/", $wert_rechts))
                $nicht_einbinden = 1;
            else
                $nicht_einbinden = 0;

            if (in_array($wert_links, $array_style_px)) {
                if (! preg_match("/%|px/", $wert_rechts))
                    $wert_rechts = $wert_rechts . "px";
            }
            if (! $nicht_einbinden)
                $ausgabe .= $wert_links . ":" . $wert_rechts . ";";
        }
    }
    // return $ausgabe;
    if (is_array($matches))
        return "{" . $ausgabe . "}";
    else
        return $ausgabe;
}

function css_umwandeln($zeichenkette)
{
    $suchmuster = "/\{(.*)}/";
    // $zeichenkette = preg_replace("/\r|\n/s", "", $zeichenkette);
    return preg_replace_callback($suchmuster, 'css_umwandler2', $zeichenkette);
    // return preg_replace("/}/","}\n",$ausgabe);
}

/*
 * $zeichenkette = ".font {
 * font:red;
 * background-color:red;
 * width:200;}";
 * $zeichenkette .= ".font2 {font:red; background-color:; width:100;}";
 *
 * echo css_umwandeln($zeichenkette);
 */
?>