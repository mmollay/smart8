<?php
$arr['mysql'] = array(
    'table' => "bills
    LEFT JOIN (
        SELECT bill_details.bill_id,
            SUM(bill_details.netto * count * (1 - COALESCE(discount, 0) / 100)) AS detail_netto_sum,
            SUM(bill_details.netto * count * (1 + (tax / 100)) * (1 - COALESCE(discount, 0) / 100)) AS detail_brutto_sum,
            SUM(CASE WHEN tax = 10 THEN bill_details.netto * count * (tax / 100) * (1 - COALESCE(discount, 0) / 100) ELSE 0 END) AS mwst_10_sum,
            SUM(CASE WHEN tax = 20 THEN bill_details.netto * count * (tax / 100) * (1 - COALESCE(discount, 0) / 100) ELSE 0 END) AS mwst_20_sum
        FROM bill_details
        JOIN bills ON bill_details.bill_id = bills.bill_id
        GROUP BY bill_id
    ) AS details ON bills.bill_id = details.bill_id
    LEFT JOIN (
        SELECT YEAR(date_booking) AS year, SUM(netto * (1 - COALESCE(discount, 0) / 100)) AS booked_netto_sum, SUM(brutto * (1 - COALESCE(discount, 0) / 100)) AS booked_brutto_sum
        FROM bills
        WHERE date_booking IS NOT NULL
        GROUP BY YEAR(date_booking)
    ) AS booked ON YEAR(bills.date_booking) = booked.year
    LEFT JOIN (
        SELECT YEAR(date_storno) AS year, COUNT(*) AS storno_count
        FROM bills
        WHERE date_storno IS NOT NULL
        GROUP BY YEAR(date_storno)
    ) AS stornos ON YEAR(bills.date_create) = stornos.year",
    'field' => "YEAR(bills.date_create) AS year,
        SUM(bills.netto ) AS total_netto_bills,
        SUM(bills.brutto ) AS total_brutto_bills,
        SUM(details.detail_netto_sum * (1 - COALESCE(bills.discount, 0) / 100)) AS total_netto_details,
        SUM(details.detail_brutto_sum * (1 - COALESCE(bills.discount, 0) / 100)) AS total_with_tax_details,
        SUM(details.mwst_10_sum * (1 - COALESCE(bills.discount, 0) / 100)) AS total_mwst_10,
        SUM(details.mwst_20_sum * (1 - COALESCE(bills.discount, 0) / 100)) AS total_mwst_20,
        (SUM(details.mwst_10_sum) + SUM(details.mwst_20_sum)) * (1 - COALESCE(bills.discount, 0) / 100) AS total_mwst,
        COALESCE(booked.booked_netto_sum, 0) AS booked_netto_sum,
        COALESCE(booked.booked_brutto_sum, 0) AS booked_brutto_sum,
        (SUM(details.detail_brutto_sum) - COALESCE(booked.booked_brutto_sum, 0)) * (1 - COALESCE(bills.discount, 0) / 100) AS not_booked_brutto_difference,
        COALESCE(stornos.storno_count, 0) AS storno_count",
    'group' => "YEAR(bills.date_create)",
    'order' => 'YEAR(bills.date_create) DESC',
    'limit' => 25,
    'where' => "AND bills.date_storno IS NULL",
    'like' => '',
    //'debug' => true
);

$arr['list'] = array(
    'id' => 'comparison_sum_list',
    'width' => '1000px',
    'align' => '',
    'size' => 'small',
    'class' => 'compact selectable celled striped definition'
);


// Hinzufügen der Spaltendefinitionen für die Tabelle, inklusive der neuen Spalte für Stornierungen
$arr['th']['year'] = array('title' => "Jahr", 'align' => 'center');
$arr['th']['total_netto_bills'] = array('title' => "Summe Netto (Bills)", 'format' => 'number', 'align' => 'right', 'width' => '100px');
$arr['th']['total_netto_details'] = array('title' => "Summe Netto (Details)", 'format' => 'number', 'align' => 'right', 'width' => '100px');
$arr['th']['total_brutto_bills'] = array('title' => "Summe Brutto (Bills)", 'format' => 'number', 'align' => 'right', 'width' => '100px');
$arr['th']['total_with_tax_details'] = array('title' => "Summe Brutto (Details)", 'format' => 'number', 'align' => 'right', 'width' => '100px');
//$arr['th']['total_mwst_10'] = array('title' => "Summe MwSt 10%", 'format' => 'number', 'align' => 'right');
//$arr['th']['total_mwst_20'] = array('title' => "Summe MwSt 20%", 'format' => 'number', 'align' => 'right');
$arr['th']['total_mwst'] = array('title' => "Gesamte MwSt", 'format' => 'number', 'align' => 'right');
//$arr['th']['storno_count'] = array('title' => "Anzahl Stornos", 'format' => 'number', 'align' => 'right');

