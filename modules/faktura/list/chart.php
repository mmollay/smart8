<?php
include ('../config.inc.php');

//ksort ( $array_year_finance );
foreach ( $array_year_finance as $year ) {
	$earning_netto = call_data_earning_netto ( $year );
	$earning_brutto = call_data_earning_brutto ( $year );

	$issues_netto = call_data_issues_netto ( $year );
	$issues_brutto = call_data_issues_brutto ( $year );

	$diference_netto = $earning_netto - $issues_netto;
	$diference_brutto = $earning_brutto - $issues_brutto;
	
	$diference_mwst = ($earning_brutto-$earning_netto) - ($issues_brutto-$issues_netto);
	
	$arr ['data'] [$year] = array (
			'earning_netto' => $earning_netto,
			'earning_brutto' => $earning_brutto,
			'issues_netto' => $issues_netto,
			'issues_brutto' => $issues_brutto,
			'diference_netto' => ($earning_brutto - $earning_netto),
			'diference_brutto' =>($issues_brutto - $issues_netto),
			'diference_mwst' => ($issues_brutto-$issues_netto) - ($earning_brutto-$earning_netto)
	);
}

$arr ['list'] = array ('id' => 'demo_list','size' => 'mini','class' => 'compact collapsing celled striped definitio','footer' => false );

$arr ['tr_top'] = array ('style' => 'background-color:#EEE',"align" => 'center' );
$arr ['th_top'] [] = array ('title' => "",'colspan' => '1' );
$arr ['th_top'] [] = array ('title' => "Brutto",'colspan' => '2', 'align'=>'center' );
$arr ['th_top'] [] = array ('title' => "Netto",'colspan' => '2', 'align'=>'center' );
$arr ['th_top'] [] = array ('title' => "Steuer",'colspan' => '3', 'align'=>'center' );

$arr ['th'] ['id'] = array ('title' => "Jahr" );
$arr ['th'] ['earning_brutto'] = array ('title' => "Einnahmen",'format' => 'euro_color' );
$arr ['th'] ['issues_brutto'] = array ('title' => "Ausgaben",'format' => 'euro_color' );
$arr ['th'] ['earning_netto'] = array ('title' => "Einnahmen",'format' => 'euro_color' );
$arr ['th'] ['issues_netto'] = array ('title' => "Ausgaben",'format' => 'euro_color' );
$arr ['th'] ['diference_netto'] = array ('title' => "Einnahmen",'format' => 'euro_color' );
$arr ['th'] ['diference_brutto'] = array ('title' => "Ausgaben",'format' => 'euro_color' );
$arr ['th'] ['diference_mwst'] = array ('title' => "Differenz",'format' => 'euro_color' );

$arr ['modal'] ['modal_form_clone'] = array ('title' => 'Ausgabe bearbeiten','class' => '','url' => 'form_edit.php?clone' );