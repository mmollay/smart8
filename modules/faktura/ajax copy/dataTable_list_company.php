<?php
require ("../config.inc.php");

$GLOBALS['mysqli']->query ( 'set character set utf8;' );

/* Paging */
$sLimit = "";
if (isset ( $_GET ['iDisplayStart'] )) {
	$sLimit = "LIMIT " . $GLOBALS['mysqli']->real_escape_string ( $_GET ['iDisplayStart'] ) . ", " . $GLOBALS['mysqli']->real_escape_string ( $_GET ['iDisplayLength'] );
}

/* Ordering */
if (isset ( $_GET ['iSortCol_0'] )) {
	$sOrder = "ORDER BY  ";
	for($i = 0; $i < $GLOBALS['mysqli']->real_escape_string ( $_GET ['iSortingCols'] ); $i ++) {
		$sOrder .= fnColumnToField ( $GLOBALS['mysqli']->real_escape_string ( $_GET ['iSortCol_' . $i] ) ) . "
				" . $GLOBALS['mysqli']->real_escape_string ( $_GET ['sSortDir_' . $i] ) . ", ";
	}
	$sOrder = substr_replace ( $sOrder, "", - 2 );
}

/*
 * Filtering - NOTE this does not match the built-in DataTables filtering which does it
 * word by word on any field. It's possible to do here, but concerned about efficiency
 * on very large tables, and MySQL's regex functionality is very limited
 */
$sWhere = "";
if ($_GET ['sSearch'] != "") {
	$sWhere = "WHERE name LIKE '%" . $GLOBALS['mysqli']->real_escape_string ( $_GET ['sSearch'] ) . "%' ";
}

$sQuery = "
SELECT SQL_CALC_FOUND_ROWS company_id,name FROM company
$sWhere
$sOrder
$sLimit ";

$rResult = $GLOBALS['mysqli']->query ( $sQuery, $gaSql ['link'] ) or die ( mysqli_error ($GLOBALS['mysqli']) );

$sQuery = "SELECT FOUND_ROWS()";
$rResultFilterTotal = $GLOBALS['mysqli']->query ( $sQuery, $gaSql ['link'] ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$aResultFilterTotal = mysqli_fetch_array ( $rResultFilterTotal );
$iFilteredTotal = $aResultFilterTotal [0];

$sQuery = "SELECT COUNT(company_id) FROM company ";
$rResultTotal = $GLOBALS['mysqli']->query ( $sQuery, $gaSql ['link'] ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$aResultTotal = mysqli_fetch_array ( $rResultTotal );
$iTotal = $aResultTotal [0];

$sOutput = '{';
$sOutput .= '"sEcho": ' . intval ( $_GET ['sEcho'] ) . ', ';
$sOutput .= '"iTotalRecords": ' . $iTotal . ', ';
$sOutput .= '"iTotalDisplayRecords": ' . $iFilteredTotal . ', ';
$sOutput .= '"aaData": [ ';
while ( $aRow = mysqli_fetch_array ( $rResult ) ) {
	$sOutput .= "[";
	$sOutput .= '"' . addslashes ( $aRow ['company_id'] ) . '",';
	$sOutput .= '"' . addslashes ( $aRow ['name'] ) . '",';
	$sOutput .= '"<button onclick=edit_faktura(' . $id . ')>Edit</button><button class=button_submit>Submit</button>",';
	$sOutput .= "],";
}
$sOutput = substr_replace ( $sOutput, "", - 1 );
$sOutput .= '] }';

echo $sOutput;
function fnColumnToField($i) {
	if ($i == 0)
		return "company_id";
	else if ($i == 1)
		return "name";
}
?>