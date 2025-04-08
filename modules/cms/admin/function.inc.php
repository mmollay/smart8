<?php
// session_start();
// if (! $_SESSION['smart_page_id']) {
// echo "Keine Page_id vorhanden - Bitte Seite neu laden!<br>
// <a href='../../'>Hier klicken</a>";
// exit;
// }
include_once (__DIR__ . '/../gadgets/function.inc.php');
include_once (__DIR__ . '/../smart_form/include/smart_functions.php');


// Key_generator
function gen_uuid()
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', 
        // 32 bits for "time_low"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), 

        // 16 bits for "time_mid"
        mt_rand(0, 0xffff), 

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000, 

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000, 

        // 48 bits for "node"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
}

// Aufuf der INDEX_ID
function get_index_id($page_id)
{
    $query = $GLOBALS['mysqli']->query("SELECT option_value FROM smart_options where page_id = '$page_id' AND option_name = 'index_id' ") or die(mysqli_error($GLOBALS['mysqli']));
    $array = mysqli_fetch_array($query);
    return $array[0];
}

// ADMIN - FUNCTIONS
function call_public_url($smart_page_id, $site_id = false)
{
    // ruft den Publicnamen der Seite für spezifischen Aufruf
    if ($site_id) {

        $query = $GLOBALS['mysqli']->query("SELECT site_url,fk_id FROM smart_langSite LEFT JOIN smart_id_site2id_page ON site_id = fk_id WHERE page_id = '$smart_page_id'  AND site_id = '$site_id' ") or die(mysqli_error($GLOBALS['mysqli'])); // AND lang = '{$_SESSION['page_lang']}'
        $array = mysqli_fetch_array($query);
        // $site_id = $array['site_id'];

        // Setzt die Endung des Skriptes html oder php
        $file_ending = check_php_script($site_id);

        // if (! $array['site_url'])
        // $array['site_url'] = $array['fk_id'];

        if ($site_id == get_index_id($smart_page_id)) {
            $save_path = "$path_id_path/" . "index$file_ending";
            $site_url = "/index$file_ending"; // fuer Sitemapå
        } else {
            $site_url = "/" . $array['site_url'] . "$file_ending";
        }
    } else
        $site_url = '';

    $query = $GLOBALS['mysqli']->query("SELECT domain FROM ssi_company.domain WHERE page_id = '$smart_page_id'	");
    $array = mysqli_fetch_array($query);
    $extract = new LayerShifter\TLDExtract\Extract();
    $result = $extract->parse($array[0]);
    $subdomain = $result->getSubdomain();
    if ($subdomain) {
        return 'http://' . $array[0] . $site_url;
    } else {
        return 'http://www.' . $array[0] . $site_url;
    }
}

// Check ob bereits ein gadget fixiert ist
function check_fixed_gadget($site_id, $layer_id)
{
    $query = $GLOBALS['mysqli']->query("SELECT layer_fixed FROM smart_layer WHERE site_id = '$site_id' AND layer_fixed ");
    $count = mysqli_num_rows($query);
    return $count;
}

// Checkt ob Module für entsprechenden User freigeschalten wurde
function check_module_user($user_id, $module)
{
    $query = $GLOBALS['mysqli']->query("SELECT module FROM module2id_user WHERE user_id = '$user_id' AND module = '$module' ");
    $count = mysqli_num_rows($query);
    return $count;
}

/*
 * FUNCTIONS
 * mm@ssi.at 20.02.2017
 */
function call_title($site_id)
{
    $sql = $GLOBALS['mysqli']->query("SELECT title FROM smart_langSite WHERE fk_id = '$site_id'") or die(mysqli_error($GLOBALS['mysqli']));
    $array = mysqli_fetch_array($sql);
    return $array['title'];
}

function favorite_sites($page_id, $class = false)
{
    // Initialisierung von Variablen
    $ii = 0;
    $div_item = ['fav' => '', '2' => ''];
    $output = '';
    
    // SQL-Query zur Ermittlung der favorisierten Seiten
    $query = $GLOBALS['mysqli']->query("
        SELECT *,
            (CASE
                WHEN DATE(max(timestamp)) = CURDATE() THEN 'Heute'
                WHEN DATE(max(timestamp)) = CURDATE() - INTERVAL 1 DAY THEN 'Gestern'
                ELSE CONCAT('vor ', DATEDIFF(NOW(), DATE(max(timestamp))), ' Tagen')
            END) AS timestamp2
        FROM smart_langSite
        LEFT JOIN smart_id_site2id_page ON site_id = fk_id
        WHERE page_id = '$page_id'
        GROUP BY site_id
        ORDER BY timestamp DESC, title
    ") or die(mysqli_error($GLOBALS['mysqli']));
    
    // Verarbeiten der SQL-Ergebnisse
    while ($array = mysqli_fetch_array($query)) {
        $ii++;
        $title = $array['title'];
        $id = $array['fk_id'];
        $timestamp = $array['timestamp2'];
        $favorite = isset($array['favorite']) ? $array['favorite'] : '';
        
        // Erzeugen der HTML-Elemente basierend auf Favoriten-Status
        $favStatus = $array['favorite'] ? 'fav' : '2';
        $div_item[$favStatus] .= "<div class='item' onclick=\"CallContentSite('$id')\">";
        $div_item[$favStatus] .= "$title  <div style='position:absolute; right:0px;' class='ui small label'>$timestamp</div>";
        $div_item[$favStatus] .= "</div>";
    }
    
    // Ausgabe-String zusammenstellen
    $output = "<div id='dropdown_search_sites' class='ui $class dropdown item icon'>";
    $output .= "<div class='text' id='get_title'>";
    $output .= call_title($_SESSION['site_id']);
    $output .= "</div>";
    $output .= "<div class='menu' style='min-width:370px; max-width:650px;'>";
    $output .= "<div class='ui icon search input'><i class='search icon'></i><input type='text' placeholder='Seite suchen'></div>";
    
    // Favoriten hinzufügen, falls vorhanden
    if ($div_item['fav']) {
        $output .= "<div class='divider'></div><div class='header grey'><i class='star yellow icon'></i>Favoriten</div>";
        $output .= "<div style='background-color:#EEE' class='scrolling menu'>{$div_item['fav']}</div>";
    }
    
    // Nicht-Favoriten hinzufügen, falls vorhanden
    if ($div_item['2']) {
        if ($div_item['2'] && $div_item['fav']) {
            $output .= "<div class='divider'></div><div class='grey header'><i class='star grey icon'></i>Andere Seiten</div>";
        }
        $output .= "<div class='scrolling menu'>{$div_item['2']}</div>";
    }
    
    $output .= "</div> &nbsp;&nbsp;&nbsp;&nbsp;<i class='caret down icon'></i></div>";
    
    return $output;
}



// Erzeugt einen neuen Layer mit gesamter Struktur falls es sich um einen Splitter handelt
function clone_layer_splitter($layer_id)
{
    // global $splitter_layer_array;
    // Erzeugt einen neuen Layer - Wenn Splitter dann wird neue ID von Layer gemerkt
    $splitter_layer_array[$layer_id] = clone_layer($layer_id);

    if (! $GLOBALS['first_clone_layer_id'])
        $GLOBALS['first_clone_layer_id'] = $splitter_layer_array[$layer_id];

    // Es werden alle weiteren layer abgerufen welche sich im Splitter befinden
    $sql = $GLOBALS['mysqli']->query("SELECT layer_id from smart_layer WHERE splitter_layer_id  = '$layer_id' ");
    while ($query = mysqli_fetch_array($sql)) {
        $clone_layer_id = $query['layer_id'];

        // generiert neuen Layer und weist in splitter zu
        $splitter_layer_array[$clone_layer_id] = clone_layer_splitter($clone_layer_id);
    }

    // print_r($splitter_layer_array);

    // Einschlichten der Layer Splitter IDs
    foreach ($splitter_layer_array as $old_splitter_layer => $new_layer_id) {
        $GLOBALS['mysqli']->query("UPDATE smart_layer SET splitter_layer_id = '$new_layer_id' 
            WHERE splitter_layer_id = '$old_splitter_layer' AND layer_id >= '{$GLOBALS['first_clone_layer_id']}'
            ");
    }
    // $splitter_layer_array = array();
    return $GLOBALS['first_clone_layer_id'];
}

// Klonen eines Layers
function clone_layer($clone_layer_id, $site_id = false)
{
    
    // Defaultmässig wird die gleiche Seite verwendet
    if (! $site_id)
        $site_id = $_SESSION['site_id'];

    $sql = $GLOBALS['mysqli']->query("SELECT gadget from smart_layer WHERE layer_id  = '$clone_layer_id'");
    $query = mysqli_fetch_array($sql);
    $gadget = $query['gadget'];
    
    $clone_fields = "page_id,matchcode,layer_x,layer_y,layer_h,layer_w,field,set_textfield,gadget,gadget_id,sort,position,layer_fixed,gadget_array,dynamic_modus,dynamic_name,format,archive,hidden,from_id,splitter_layer_id";
    // echo "insert into smart_layer( $clone_fields,site_id ) select $clone_fields,$new_site_id from smart_layer WHERE layer_id = '$clone_layer_id'";
    $GLOBALS['mysqli']->query("insert into smart_layer( $clone_fields,site_id ) select $clone_fields,$site_id from smart_layer WHERE layer_id = '$clone_layer_id'") or die(mysqli_error($GLOBALS['mysqli']));
    
    $new_layer_id = mysqli_insert_id($GLOBALS['mysqli']);
    $clone_fields2 = "lang,title,text";
    
    // echo "insert into smart_langLayer( $clone_fields2,fk_id ) select $clone_fields2,$new_layer_id from smart_langLayer where fk_id = '$clone_layer_id' ";
    $GLOBALS['mysqli']->query("insert into smart_langLayer( $clone_fields2,fk_id ) select $clone_fields2,$new_layer_id from smart_langLayer where fk_id = '$clone_layer_id' ") or die(mysqli_error($GLOBALS['mysqli']));

    // Clone Formular - Felder
    $sql_formular = $GLOBALS['mysqli']->query("SELECT * from smart_formular WHERE layer_id  = '$clone_layer_id' ");
    while ($query = mysqli_fetch_array($sql_formular)) {
        $clone_field_id = $query['field_id'];
        $clone_fields3 = "label,placeholder,help,value,sort,type,text,validate,setting_array";
        $GLOBALS['mysqli']->query("insert into smart_formular($clone_fields3,layer_id) select $clone_fields3,$new_layer_id from smart_formular where field_id = '$clone_field_id' ") or die(mysqli_error($GLOBALS['mysqli']));
    }
    
    // Clone Buttons
    $sql_formular = $GLOBALS['mysqli']->query("SELECT * from smart_gadget_button WHERE layer_id  = '$clone_layer_id' ") or die(mysqli_error($GLOBALS['mysqli']));
    while ($query = mysqli_fetch_array($sql_formular)) {
        $clone_button_id = $query['button_id'];
        $clone_fields3 = "sequence,title,icon,color,url,link,tooltip,target";
        $GLOBALS['mysqli']->query("insert into smart_gadget_button($clone_fields3,layer_id) select $clone_fields3,$new_layer_id from smart_gadget_button where button_id = '$clone_button_id' ") or die(mysqli_error($GLOBALS['mysqli']));
    }
    
    // Clonen aller Optionen des Layers
    $sql_formular = $GLOBALS['mysqli']->query("SELECT * from smart_element_options WHERE element_id  = '$clone_layer_id' ") or die(mysqli_error($GLOBALS['mysqli']));
    while ($query = mysqli_fetch_array($sql_formular)) {
        $clone_option_name = $query['option_name'];
        $clone_option_value = $query['option_value'];
        $clone_option_id = $query['option_id'];
        $GLOBALS['mysqli']->query("insert into smart_element_options(option_name,option_value,element_id) select option_name,option_value,$new_layer_id from smart_element_options where option_id = '$clone_option_id' ") or die(mysqli_error($GLOBALS['mysqli']));
    }

    // if ($gadget == 'splitter') {
    // Übergeben der Layer_Id für splitter
    return $new_layer_id;
    // }
}

// Klonen einer Seite
function clone_site($clone_site_id, $new_site_id)
{
    $sql = $GLOBALS['mysqli']->query("SELECT layer_id from smart_layer WHERE site_id  = '$clone_site_id'");
    
    while ($query = mysqli_fetch_array($sql)) {
        $clone_layer_id = $query['layer_id'];
        
        // generiert neuen Layer und weist in splitter zu
        $splitter_layer_array[$clone_layer_id] = clone_layer($clone_layer_id, $new_site_id);
        
    }
    
    // print_r($splitter_layer_array);

    // Einschlichten der Felder in Splitter_layer
    $sql = $GLOBALS['mysqli']->query("SELECT * from smart_layer WHERE site_id = '$new_site_id' AND splitter_layer_id != 0 ") or die(mysqli_error($GLOBALS['mysqli']));
    while ($query = mysqli_fetch_array($sql)) {
        $layer_id = $query['layer_id'];
        foreach ($splitter_layer_array as $old_splitter_layer => $new_layer_id) {
            
            $GLOBALS['mysqli']->query("UPDATE smart_layer SET splitter_layer_id = '$new_layer_id' WHERE splitter_layer_id = '$old_splitter_layer' AND layer_id = '$layer_id' ");
            
        }
    }
}

// Read Array from table
function GenerateArraySql($sql, $ausgabe_feld = FALSE, $single = FALSE)
{
    global $array;
    if (! $ausgabe_feld)
        $ausgabe_feld = 'matchcode';
    $query = $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
    while ($array = mysqli_fetch_array($query)) {
        ++ $count;
        $single_value = $array[$ausgabe_feld];
        if (preg_match('/%/', $ausgabe_feld))
            // $set_array[$array[0]] = preg_replace_callback ( '/%(\w+)%/', GenerateArrayCallback, $ausgabe_feld );
            $set_array[$array[0]] = preg_replace_callback('/%(\w+)%/', function ($matches) {
                global $array;
                return $array[$matches[1]];
            }, $ausgabe_feld);
        else
            $set_array[$array[0]] = $array[$ausgabe_feld];
    }
    // übergibt Wert direkt ohne in eine Array zu schreiben
    if ($single)
        $set_array = $single_value;

    return $set_array;
}

/*
 * Read Columns from Table
 */
function mysql_columns($table)
{
    $result = $GLOBALS['mysqli']->query("SHOW COLUMNS FROM $table");
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $array[] = $row['Field'];
        }
    }
    return $array;
}

// Generiert aus den Werten array für das speichern der css Werte in der Datenbank
function generate_array($key, $value, $array)
{
    if ($array) {
        $array1 = explode("|", $array);

        foreach ($array1 as $array2) {

            $array3 = explode("=", $array2);

            if ($array3['0'] == $key) {
                if (isset($value))
                    $array_new .= $key . "=$value" . "|";
                $set_insert_value[$key] = 1;
            } elseif ($array3['0'] and $array3['1']) {
                $array_new .= $array3['0'] . "=" . $array3['1'] . "|";
                $set_insert_value[$array3['0']] = 1;
            }
        }
    }

    // Wenn Wert nicht existiert, dann wird neu geschrieben
    if (! $set_insert_value[$key] and isset($value)) {
        $array_new .= "$key=$value" . "|";
    }
    return $array_new;
}

function parse_youtube($link)
{
    $regexstr = '~
            # Match Youtube link and embed code
            (?:                             # Group to match embed codes
                (?:<iframe [^>]*src=")?       # If iframe match up to first quote of src
                |(?:                        # Group to match if older embed
                    (?:<object .*>)?      # Match opening Object tag
                    (?:<param .*</param>)*  # Match all param tags
                    (?:<embed [^>]*src=")?  # Match embed tag to the first quote of src
                )?                          # End older embed code group
            )?                              # End embed code groups
            (?:                             # Group youtube url
                https?:\/\/                 # Either http or https
                (?:[\w]+\.)*                # Optional subdomains
                (?:                         # Group host alternatives.
                youtu\.be/                  # Either youtu.be,
                | youtube\.com              # or youtube.com
                | youtube-nocookie\.com     # or youtube-nocookie.com
                )                           # End Host Group
                (?:\S*[^\w\-\s])?           # Extra stuff up to VIDEO_ID
                ([\w\-]{11})                # $1: VIDEO_ID is numeric
                [^\s]*                      # Not a space
            )                               # End group
            "?                              # Match end quote if part of src
            (?:[^>]*>)?                       # Match any extra stuff up to close brace
            (?:                             # Group to match last embed code
                </iframe>                 # Match the end of the iframe
                |</embed></object>          # or Match the end of the older embed
            )?                              # End Group of last bit of embed code
            ~ix';

    preg_match($regexstr, $link, $matches);

    return $matches[1];
}

function parse_vimeo($link)
{
    $regexstr = '~
            # Match Vimeo link and embed code
            (?:<iframe [^>]*src=")?       # If iframe match up to first quote of src
            (?:                         # Group vimeo url
                https?:\/\/             # Either http or https
                (?:[\w]+\.)*            # Optional subdomains
                vimeo\.com              # Match vimeo.com
                (?:[\/\w]*\/videos?)?   # Optional video sub directory this handles groups links also
                \/                      # Slash before Id
                ([0-9]+)                # $1: VIDEO_ID is numeric
                [^\s]*                  # Not a space
            )                           # End group
            "?                          # Match end quote if part of src
            (?:[^>]*></iframe>)?        # Match the end of the iframe
            (?:<p>.*</p>)?              # Match any title information stuff
            ~ix';

    preg_match($regexstr, $link, $matches);

    return $matches[1];
}

// schreibt Datei immer wieder neu drueber
function fu_txt_writer($text, $path)
{
    $fp = fopen("$path", "w");
    fwrite($fp, "$text");
    fclose($fp);
}

function change_path_inner_file($dir, $pattern, $replace)
{
    $content = file_get_contents($dir);
    $content = preg_replace($pattern, $replace, $content);
    file_put_contents($dir, $content);
}

// Update - Setzer
function set_update_site($site_id = false, $layer_id = false)
{
    if ($layer_id) {}

    if ($site_id == 'all') {
        $add_mysql = ",hole_page = '1' ";
    } else
        $add_mysql = ",hole_page = '0' ";

    $GLOBALS['mysqli']->query("REPLACE into log_change_site SET page_id = '{$_SESSION['smart_page_id']}', site_id = '{$_SESSION['site_id']}', user_id = '{$_SESSION['user_id']}' $add_mysql") or die(mysqli_error($GLOBALS['mysqli']));

    if ($site_id == 'all') {
        // Update für alle Seiten
        $GLOBALS['mysqli']->query("UPDATE smart_page SET set_update = 1 WHERE page_id = '{$_SESSION['smart_page_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
        $GLOBALS['mysqli']->query("UPDATE smart_id_site2id_page SET set_update = 1 WHERE page_id = '{$_SESSION['smart_page_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
    } else {
        if (! $site_id) {
            $site_id = $_SESSION['site_id'];
        }
        // Setzt UPDATE-marker bei einer Änderung
        $GLOBALS['mysqli']->query("UPDATE smart_id_site2id_page SET set_update = 1,timestamp = NOW() WHERE site_id = '$site_id'  ") or die(mysqli_error($GLOBALS['mysqli']));
    }

    // Setzt Update-Zeit für Übersicht fest
    $GLOBALS['mysqli']->query("UPDATE smart_page SET update_date = NOW() WHERE page_id = '{$_SESSION['smart_page_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
}

/**
 * ZENTRALE FUNKTIONEN FÜR ABRUFEN VON DATEN WELCHE IM SYSTEM BENÖTIGT WERDEN
 * mm@ssi.at am 23.03.2017
 */
// call_company_option_single($company_id,$name) ....AUFRUF VON EINEM EINZELENEN WERT AUS DEN OPTIONEN
// save_company_option($array, $id) .... SPEICHERN DER OPTIONEN
// call_mysql_value($query, $db = false) .... EINZELNES FELD ABRUFEN

// Ruft company_id über user Domain
// domain nachsehen in
function call_company_id_v2($domain)
{
    $domain = preg_replace("/www./", "", $domain);
    $query = $GLOBALS['mysqli']->query("SELECT company_id FROM ssi_company.domain WHERE domain = '$domain' and set_ssl = '1' ") or die(mysqli_error());
    // $user = $query->fetch_array(MYSQLI_BOTH);
    $array = mysqli_fetch_array($query);
    return $array[0];
}

// Holt page_id
function call_page_id($domain)
{
    $domain = preg_replace("/www./", "", $domain);
    $query = $GLOBALS['mysqli']->query("SELECT page_id FROM ssi_company.domain WHERE domain = '$domain' ") or die(mysqli_error());
    $array = mysqli_fetch_array($query);
    return $array[0];
}

/**
 * *******************************************************************************************
 * SMART - OPTIONS
 * /********************************************************************************************
 */

// Save page and site options 
// mm@ssi.at  14.10.2022 - remove old setting if is not needet
function save_smart_option($array, $page_id, $site_id = 0)
{
    if (! $page_id) {
        echo "ID zum speichern der Optionen ist nicht definiert";
        return;
        exit();
    }

    foreach ($array as $key => $value) {
        if ($GLOBALS[$value]) {
            $option_value = $GLOBALS['mysqli']->real_escape_string($GLOBALS[$value]);
            $key = $value;
        } else {
            $option_value = $GLOBALS['mysqli']->real_escape_string($value);
        }

        $option_value = stripslashes(str_replace('\r\n', PHP_EOL, $option_value));

        //Remove if value = ''
        if (! $option_value)
            $GLOBALS['mysqli']->query("
			DELETE FROM smart_options WHERE 
			page_id = '$page_id' AND site_id = '$site_id' AND option_name = '$key'
			") or die(mysqli_error($GLOBALS['mysqli']));
        else {

     
            $GLOBALS['mysqli']->query("
			REPLACE INTO smart_options SET
			page_id = '$page_id',
            site_id = '$site_id',
			option_name = '$key',
            autoload =  '',
			option_value = '$option_value'
			") or die(mysqli_error($GLOBALS['mysqli']));
        }
    }
}

/**
 * *******************************************************************************************
 * COMPANY - OPTIONS
 * /********************************************************************************************
 */

// Speichern von Company-Optionen
function save_company_option($array, $id)
{
    if (! $id) {
        echo "ID zum speichern der Optionen ist nicht definiert";
        return;
        exit();
    }
    foreach ($array as $key => $value) {
        if ($GLOBALS[$value]) {
            $option_value = $GLOBALS['mysqli']->real_escape_string($GLOBALS[$value]);
            $key = $value;
        } else {
            $option_value = $GLOBALS['mysqli']->real_escape_string($value);
        }

        $option_value = stripslashes(str_replace('\r\n', PHP_EOL, $option_value));

        $GLOBALS['mysqli']->query("
			REPLACE INTO ssi_company.comp_options SET
			company_id = '$id',
			option_name = '$key',
			option_value = '$option_value'
			") or die(mysqli_error($GLOBALS['mysqli']));
    }
}

// Ruft einzelnen Wert aus
function call_company_option_single($company_id, $name)
{
    $query = $GLOBALS['mysqli']->query("SELECT option_value FROM ssi_company.comp_options WHERE company_id = '$company_id' AND option_name='$name' ") or die(mysqli_error($GLOBALS['mysqli']));
    $array = mysqli_fetch_array($query);
    return $array[0];
}

// Ruft company_id ab
function call_company_id($domain)
{
    $query = $GLOBALS['mysqli']->query("SELECT company_id FROM comp_options WHERE option_value = '$domain' AND option_name='center_domain' ") or die(mysqli_error());
    // $user = $query->fetch_array(MYSQLI_BOTH);
    $array = mysqli_fetch_array($query);
    return $array[0];
}

// Abruf eines einzelnen Feldes - kann auch zu überprüfen verwendet werden ob Eintrag vorhanden ist
function call_mysql_value($query, $db = false)
{

    // MSQLI
    if ($db) {
        $query = $GLOBALS['mysqli']->query($query) or die(mysqli_error($GLOBALS['mysqli']));
        $array = mysqli_fetch_array($query);
    } else {
        $query = $GLOBALS['mysqli']->query($query) or die(mysqli_error($GLOBALS['mysqli']));
        $array = mysqli_fetch_array($query);
    }
    return $array[0];
}

// Read Value from Table
function mysql_singleoutput($sql, $indexColumn = false)
{
    $query = $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
    $array = mysqli_fetch_array($query);
    if ($indexColumn)
        return $array[$indexColumn];
    else
        return $array[0];
}

// seo_permalink() - Transform "Sonderzeichen"
function seo_permalink($value)
{
    $value = preg_replace('/ {2,}/', ' ', $value);
    $turkce = array(" ","ü","Ü","ö","Ö","Ä","ä","ß");
    $duzgun = array("-","ue","Ue","oe","Oe","Ae","ae","sz");
    $value = str_replace($turkce, $duzgun, $value);
    $value = preg_replace("@[^A-Za-z0-9\-_.]+@i", "", $value);
    $value = preg_replace("/-{2,}/", "-", $value);
    return $value;
}

/* Convert hexdec color string to rgb(a) string */
// function hex2rgba($color, $opacity = false)
// {
//     $default = 'rgb(0,0,0)';

//     // Return default if no color provided
//     if (empty($color))
//         return $default;

//     // Sanitize $color if "#" is provided
//     if ($color[0] == '#') {
//         $color = substr($color, 1);
//     }

//     // Check if color has 6 or 3 characters and get values
//     if (strlen($color) == 6) {
//         $hex = array($color[0] . $color[1],$color[2] . $color[3],$color[4] . $color[5]);
//     } elseif (strlen($color) == 3) {
//         $hex = array($color[0] . $color[0],$color[1] . $color[1],$color[2] . $color[2]);
//     } else {
//         return $default;
//     }

//     // Convert hexadec to rgb
//     $rgb = array_map('hexdec', $hex);

//     // Check if opacity is set(rgba or rgb)
//     if ($opacity) {
//         if (abs($opacity) > 1)
//             $opacity = 1.0;
//         $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
//     } else {
//         $output = 'rgb(' . implode(",", $rgb) . ')';
//     }

//     // Return rgb(a) color string
//     return $output;
// }

function hex2rgba($color, $opacity = false)
{
    if (empty($color)) {
        return 'rgb(0,0,0)';
    }
    
    $color = ltrim($color, '#');
    $hexLength = strlen($color);
    
    if ($hexLength != 3 && $hexLength != 6) {
        return 'rgb(0,0,0)';
    }
    
    $rgb = str_split($hexLength == 3 ? $color . $color : $color, 2);
    $rgb = array_map('hexdec', $rgb);
    
    if ($opacity !== false) {
        $opacity = max(0, min(1, $opacity));
        return sprintf('rgba(%d,%d,%d,%s)', $rgb[0], $rgb[1], $rgb[2], $opacity);
    }
    
    return sprintf('rgb(%d,%d,%d)', $rgb[0], $rgb[1], $rgb[2]);
}


