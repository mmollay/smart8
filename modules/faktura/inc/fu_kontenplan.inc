<?

// Kontenplan wird nach nach IGL oder Inland aufgelistet
function fu_sql_abfrage($option, $zusatz, $zusatz_felder = FALSE)
{
    $GLOBALS['mysqli'] = $GLOBALS['mysqli'];

    if ($option == 'ag' or $option == 'ag2') {
        $sql = "SELECT $zusatz_felder " . // accounts.nr, accounts.bez, accounts.bill_value,
        " SUM(issues.netto) AS summe, " . "	SUM(IF(accounts.tax='20', issues.netto * 0.2, 0)) AS mwst20, " . "	SUM(IF(accounts.tax='10', issues.netto * 0.1, 0)) AS mwst10 " . "   FROM issues,accounts " . // fa_ausgabe_detail,
                                                                                                                                                                                                              // " WHERE id_ausgabe=fa_ausgabe.id ".
        " 	WHERE accounts.account_id=issues.account " . 
        // " and issues.company_id = '{$_SESSION['faktura_company_id']}' ".
        $zusatz;
    } else
        $sql = "SELECT  $zusatz_felder " . // accounts.nr, accounts.bez, accounts.bill_value,
        "	SUM((bill_details.netto - bill_details.netto/100*rabatt)*count -
				((bill_details.netto - bill_details.netto/100*rabatt)*count)/100*discount) AS summe," . 
        // Berechnung 20% Mwst.
        "	SUM(IF(accounts.tax='20' && no_mwst='' , (((bill_details.netto-bill_details.netto/100*rabatt)*count)-
						((bill_details.netto-bill_details.netto/100*rabatt)*count)/100*
						discount)* 0.2	, 0)) AS mwst20, " . 
        // Berechnung 10% Mwst.
        "	SUM(IF(accounts.tax='10' && no_mwst='', (((bill_details.netto-bill_details.netto/100*rabatt)*count)-
								((bill_details.netto-bill_details.netto/100*rabatt)*count)/100*
								discount)* 0.1	, 0)) AS mwst10 " . "   FROM bills,bill_details,accounts " . "   WHERE bill_details.bill_id=bills.bill_id " . 
        // " and bills.company_id = '{$_SESSION['faktura_company_id']}' ".

        " 	AND accounts.account_id=bill_details.account " . " 	AND date_storno = '0000-00-00'  AND document = 'rn' " . 
        // " AND (IF(bills.country='AT', accounts.account_id=bill_details.account, 0)) ".
        // " OR (IF(bills.land !='AT', accounts.nr=bill_details.erloes_igl, 0))) ".

        $zusatz;

    return $sql;
}

/**
 * ***********************************************************************************
 * Berechnung der Summe des Monats Netto
 * ************************************************************************************
 */
function fu_summe_netto($option, $month, $Year)
{
    $GLOBALS['mysqli'] = $GLOBALS['mysqli'];
    // Ausgabe der monatlichen Umsaetze
    if ($month && $Year) {
        $zusatz = "and DATE_FORMAT(date_create,'%c-%Y') = '$month-$Year' ";
        $zusatz_v2 = "and DATE_FORMAT(date_booking,'%c-%Y') = '$month-$Year' ";
    } // Ausgabe der jaehrlichen Umsaetze
    elseif ($Year) {
        $zusatz = "and DATE_FORMAT(date_create,'%Y') = '$Year' ";
        $zusatz_v2 = "and DATE_FORMAT(date_booking,'%Y') = '$Year' ";
    }

    if ($option == 'eg' or $option == 'ag')
        $sql_string = fu_sql_abfrage($option, $zusatz);
    if ($option == 'eg2' or $option == 'ag2') {
        $sql_string = fu_sql_abfrage($option, $zusatz_v2);
    }

    // echo $sql_string; exit;
    $summe_query = $GLOBALS['mysqli']->query($sql_string) or die(mysqli_error($GLOBALS['mysqli']));
    $summe_spalte = mysqli_fetch_array($summe_query);
    $summe = $summe_spalte['summe'];
    return $summe;
}

/**
 * ***************************************************************
 * //Summenbildung Zeitlich bestimmt /$steuer,Eingang oder Ausgang
 * /****************************************************************
 */
function mwst($from, $to, $option)
{
    $GLOBALS['mysqli'] = $GLOBALS['mysqli'];
    if ($option == 'ag' or $option == 'eg') {
        $sql_zusatz = " AND date_format(date_create, '%Y-%m-%d') between '$from' and '$to' ";
    } else {
        $sql_zusatz = " AND date_format(date_booking, '%Y-%m-%d') between '$from' and '$to' ";
    }

    $sql_string = fu_sql_abfrage($option, $sql_zusatz);

    // echo $sql_string; exit;
    $query = $GLOBALS['mysqli']->query($sql_string) or die(mysqli_error($GLOBALS['mysqli']));
    $spalte = mysqli_fetch_array($query);
    // Summenbildung
    return $spalte['mwst20'] + $spalte['mwst10'];
}

function eintrag_buch($title, $align, $color)
{
    if (! isset($color))
        $color = hg_li;
    return "<td	class='$color $align aligned'>$title</td>";
}

function summe_buch($title)
{
    return "<td class='hg_li right aligned'><b>$title</b></td>";
}

/**
 * Update: 16.04.2007
 * - Es werden im Kontenplan die Eintraege nach dem Buchungsdatum ausgegeben (statt Datum jetzt bookdate)
 * - Neuausruchtung der Konten
 */
function title_buch($title, $order, $width, $class = false)
{
    $GLOBALS['mysqli'] = $GLOBALS['mysqli'];
    global $link_values;
    global $PHP_SELF;
    if ($width)
        $width = "width='$width' ";
    if ($order)
        $title = "<a href='?$link_values&order=$order'>$title</a>";
    return "
	<th $width $class>$title</th>";
}

function ausgabe_kontenplan($bill_value, $year)
{
    global $str2300AdmFieldGroup;
    global $k_summe_value;

    // Button zum hinzufuegen einer weiteren Gruppe
    // global $ButtonAddPic;
    // $button_add ="<a href=$PHPSELF$link_values&add_value=1>$ButtonAddPic</a><br>";

    if ($select_book == "1")
        $zusatz_sql1 .= " AND date_booking !='0000-00-00' ";
    elseif ($select_book == "2")
        $zusatz_sql1 .= " AND date_booking ='0000-00-00' ";
    elseif ($select_book == "3")
        $zusatz_sql1 .= "";

    $sql_string = fu_sql_abfrage($bill_value, " AND DATE_FORMAT(date_booking,'%Y') = '$year' GROUP BY accounts.account_id ORDER BY title", "accounts.tax, accounts.code, accounts.title, accounts.option, accounts.account_id,");

    $gruppen_query = $GLOBALS['mysqli']->query($sql_string);
    while ($spalte = mysqli_fetch_array($gruppen_query)) {

        $steuer_10 = number($spalte['mwst10']);
        $steuer_20 = number($spalte['mwst20']);
        $summe = number($spalte['summe']);
        $summe_brutto = number($spalte['mwst10'] + $spalte['mwst20'] + $spalte['summe']);
        $nr = $spalte['code'];
        $account_id = $spalte['account_id'];
        $bez = $spalte['title'];
        $tax = $spalte['tax'];
        $id_konto = $spalte['account_id'];
        $summe_value += $spalte['summe'];
        $summe_steuer10 += $spalte['mwst10'];
        $summe_steuer20 += $spalte['mwst20'];

        $ausgabe_gruppen .= "<tr>";

        $ausgabe_gruppen .= eintrag_buch($account_id, "", "");

        if ($bill_value == 'ag')
            $ausgabe_gruppen .= eintrag_buch("<button id='$id_konto' class='show_issues ui icon compact button mini'><i class='external icon'></i> Details</button> $bez", "left", "hg_li");
        else
            $ausgabe_gruppen .= eintrag_buch("<button id='$id_konto' class='show_earnings ui icon compact button mini'><i class='external icon'></i> Details</button> $bez", "left", "hg_li");

        $ausgabe_gruppen .= eintrag_buch($tax . "%", "center", "hg_li");
        $ausgabe_gruppen .= eintrag_buch($summe, "right", "hg_li");
        $ausgabe_gruppen .= eintrag_buch($summe_brutto, "right", "hg_li");
        $ausgabe_gruppen .= eintrag_buch($steuer_20, "right", "hg_li");
        $ausgabe_gruppen .= eintrag_buch($steuer_10, "right", "hg_li");
        $ausgabe_gruppen .= "</tr>";
    }

    $ausgabe = "<thead>";
    $ausgabe .= "<tr>";
    $ausgabe .= title_buch("ID", 'account_id', '20');
    $ausgabe .= title_buch($str2300AdmFieldGroup[3], 'bez', '');
    $ausgabe .= title_buch("Mwst", 'tax', '70');
    $ausgabe .= title_buch('Brutto', 'summe', '120', 'right aligned');
    $ausgabe .= title_buch($str2300AdmFieldGroup[4], 'summe', '120', 'right aligned');
    $ausgabe .= title_buch($str2300AdmFieldGroup[5], 'mwst20', '100', 'right aligned');
    $ausgabe .= title_buch($str2300AdmFieldGroup[6], 'mwst10', '100', 'right aligned');
    $ausgabe .= "</tr>";
    $ausgabe = "</thead>";

    if ($bill_value == 'ag') {
        $ausgabe .= "<tr>";
        $ausgabe .= "<th></th>";
        $ausgabe .= "<th>" . $str2300AdmFieldGroup[$bill_value] . "&nbsp;=&nbsp;<b><u>" . number($summe_steuer10 + $summe_steuer20) . "</u></b></th>";
        $ausgabe .= "<th></th>";
        $ausgabe .= "<th class='right aligned'><b>" . number($summe_value + $summe_steuer20 + $summe_steuer10) . "</b></th>";
        $ausgabe .= "<th class='right aligned'><b>" . number($summe_value) . "</b></th>";
        $ausgabe .= "<th class='right aligned'><b>" . number($summe_steuer20) . "</b></th>";
        $ausgabe .= "<th class='right aligned'><b>" . number($summe_steuer10) . "</b></th>";
        $ausgabe .= "</tr>";
        $ausgabe .= "</thead>";
    }

    $ausgabe .= $ausgabe_gruppen;

    $active_colspan = "";

    $k_summe_value[$bill_value]['netto'] = $summe_value;
    $k_summe_value[$bill_value]['steuer_20'] = $summe_steuer20;
    $k_summe_value[$bill_value]['steuer_10'] = $summe_steuer10;
    $k_summe_value[$bill_value]['steuer_summe'] = $summe_steuer10 + $summe_steuer20;
    // Nettosumme bei 20% Mwst
    $k_summe_value[$bill_value]['netto20'] = $summe_steuer20 * 5;

    return $ausgabe;
}

function ausgabe_kontenplan_gruppe($year)
{
    global $str2300AdmFieldGroup;

    $sql_string = "
	SELECT accountgroup.title title, accountgroup.accountgroup_id id,
	SUM((bill_details.netto - bill_details.netto/100*rabatt)*count - ((bill_details.netto - bill_details.netto/100*rabatt)*count)/100*discount) AS netto_sum,
	SUM(IF(accounts.tax='20' && no_mwst='' , (((bill_details.netto-bill_details.netto/100*rabatt)*count)- ((bill_details.netto-bill_details.netto/100*rabatt)*count)/100* discount)* 0.2 , 0)) AS mwst20,
	SUM(IF(accounts.tax='10' && no_mwst='', (((bill_details.netto-bill_details.netto/100*rabatt)*count)- ((bill_details.netto-bill_details.netto/100*rabatt)*count)/100* discount)* 0.1 , 0)) AS mwst10
	FROM bill_details
	INNER JOIN bills ON bill_details.bill_id = bills.bill_id
	INNER JOIN accounts ON account_id = account
	INNER JOIN accountgroup ON accounts.accountgroup_id = accountgroup.accountgroup_id
	WHERE DATE_FORMAT(date_booking,'%Y') = '$year'
	AND accounts.option = 'in'
	AND document = 'rn'
	GROUP by accountgroup.accountgroup_id
	";

    $gruppen_query = $GLOBALS['mysqli']->query($sql_string) or die(mysqli_error($GLOBALS['mysqli']));
    while ($spalte = mysqli_fetch_array($gruppen_query)) {

        $netto_sum_total += $spalte['netto_sum'];
        $summe_steuer20 += $spalte['mwst20'];
        $summe_steuer10 += $spalte['mwst10'];
        $list .= "<tr>";
        $list .= eintrag_buch($spalte['id'], "left", "hg_li");
        $list .= eintrag_buch($spalte['title'], "left", "hg_li");
        $list .= eintrag_buch(number($spalte['netto_sum'] + $spalte['mwst20'] + $spalte['mwst10']), "right", "hg_li");
        $list .= eintrag_buch(number($spalte['netto_sum']), "right", "hg_li");
        $list .= eintrag_buch(number($spalte['mwst20']), "right", "hg_li");
        $list .= eintrag_buch(number($spalte['mwst10']), "right", "hg_li");
        $list .= "</tr>";
    }

    $output .= "<thead>";
    $output .= "<tr>";
    // title_buch($str2300AdmFieldGroup[2],'nr','50').
    $output .= title_buch("ID", 'account_id', '20');
    $output .= title_buch("Gruppen", 'bez', '');
    $output .= title_buch("Brutto", 'summe_brutto', '130');
    $output .= title_buch($str2300AdmFieldGroup[4], 'summe', '130');
    $output .= title_buch($str2300AdmFieldGroup[5], 'mwst20', '100');
    $output .= title_buch($str2300AdmFieldGroup[6], 'mwst10', '100');
    $output .= "</tr>";
    $output .= "</thead>";

    $output .= $list;

    $output .= "<tfoot>";
    $output .= "<tr>";
    $output .= "<th align=right></th>";
    $output .= "<th colspan=1 align=right>Umsatzsteuer&nbsp;=&nbsp;<b><u>" . number($summe_steuer10 + $summe_steuer20) . "</u></b></th>";
    // "<td colspan=1 align=right>".$str2300AdmFieldGroup[$bill_value]."&nbsp;=&nbsp;<b><u>".number($summe_steuer10+$summe_steuer20)."</u></b></td>".
    $output .= "<th class='right aligned'><b>" . number($netto_sum_total + $summe_steuer10 + $summe_steuer20) . "<b></th>";
    $output .= "<th class='right aligned'><b>" . number($netto_sum_total) . "<b></th>";
    $output .= "<th class='right aligned'><b>" . number($summe_steuer20) . "<b></th>";
    $output .= "<th class='right aligned'><b>" . number($summe_steuer10) . "<b></th>";
    // "<td colspan=2>&nbsp;</td>".
    $output .= "</tr>";
    $output .= "<tfoot>";
    return $output;
}

?>