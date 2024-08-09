<?php
/**
 *
 * @param DB-ID $id
 * @param
 *        	Array -welche option-werte übergeben werden solllen $array_name
 * @param
 *        	Ob global-values ausgegeben werden sollen $output_global
 * @return void|unknown
 */
function call_company_option($id, $array_name = FALSE, $output_global = FALSE) {
	if (! $id) {
		echo "<div align=center><br><br> Diese Domain scheint keine Zugriffsberechtigung zu haben. call_company_option</div>\n";
		exit ();
		return;
	}
	
	// Wenn nur gewiese Werte ausgelesen werden sollen - Sonst werden alle ausgelsen
	if (is_array ( $array_name )) {
		foreach ( $array_name as $key => $value ) {
			if (!isset( $add_sql))
				$add_sql = " WHERE";
			else
				$add_sql .= "OR ";
			$add_sql .= " (company_id = '$id' AND option_name = '$value' ) ";
		}
	} else {
		$add_sql = "WHERE company_id = '$id' ";
	}
	
	$query = $GLOBALS ['mysqli']->query ( "SELECT option_name, option_value FROM ssi_company.comp_options $add_sql " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	while ( $array = mysqli_fetch_array ( $query ) ) {
	    
		if ($output_global) {
			$GLOBALS [$array ['option_name']] = $array ['option_value'];
		}
		$output [$array ['option_name']] = $array ['option_value'];
	}
	
	return $output;
}

/**
 * ********************************************************************************
 * Bsp1: $index_id = call_smart_option ('123','','index_id'); form page_id
 * Bsp2: $index_id = call_smart_option ('123','32','index_id'); //from site_id
 * Bsp2: $index_id = call_smart_option ('','32','index_id'); //from site_id
 *
 *
 * Bsp3: $array = call_smart_option ('123','',array('index_id','app_id')); $index=$array['index_id']; $app_id=$array['app_id'];
 * Bsp4: call_smart_option ('123','','',true); $index=$array['index_id']; echo $index_id; usw.....
 * ********************************************************************************
 */
function call_smart_option($page_id, $site_id = FALSE, $array_name = FALSE, $output_global = FALSE) {
	
// 	if (! $_SESSION ['page_id']) {
// 		echo "<div aligen=center>Diese Domain scheint keine Zugriffsberechtigung zu haben.</div>\n";
// 		exit ();
// 		return;
// 	}

	if ($site_id) {
		$add_site_id_sql .= "AND site_id = '$site_id' ";
	} else
		$add_site_id_sql .= "AND site_id = 0 ";

	if ($page_id)
		$add_page_id_sql .= "AND page_id = '$page_id' ";

	$add_sql = '';
	// Wenn nur gewiese Werte ausgelesen werden sollen - Sonst werden alle ausgelsen
	// mm@ssi.at am 06.02.2019
	if (is_array ( $array_name ) or ! $array_name) {

		if (is_array ( $array_name )) {
			foreach ( $array_name as $key => $value ) {

				if ($add_option_name_sql)
					$add_option_name_sql .= "OR ";
				$add_option_name_sql .= " option_name = '$value' ";
			}
			$add_option_name_sql = "AND ($add_option_name_sql)";
		}
		
		//echo "<br><br><br>SELECT option_name, option_value FROM smart_options WHERE 1 $add_sql <br>";
		$query = $GLOBALS ['mysqli']->query ( "SELECT option_name, option_value FROM smart_options WHERE 1 $add_option_name_sql $add_page_id_sql $add_site_id_sql" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		while ( $array = mysqli_fetch_array ( $query ) ) {
			if ($output_global) {
				$GLOBALS [$array ['option_name']] = $array ['option_value'];
			}
			if ($array ['option_value']) {
				$output [$array ['option_name']] = $array ['option_value'];
				//echo $array ['option_name'] . "=" . $array ['option_value'] . "<br>";
			}
		}
		return $output;
	} else {
		// Wenn nur ein Wert übergeben werden soll (wird nicht als Array übergeben)
		$query = $GLOBALS ['mysqli']->query ( "SELECT option_name, option_value FROM smart_options WHERE 1 AND option_name = '$array_name' $add_page_id_sql $add_site_id_sql " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		$array = mysqli_fetch_array ( $query );
		return $array ['option_value'];
	}
}

/**
 * ********************************************************************************
 * Bsp1: $index_id = call_smart_element_option ('1','color'); //site_id
 * Bsp2: $array = call_smart_element_option ('1',array('index_id','app_id')); $index=$array['index_id']; $app_id=$array['app_id'];
 * Bsp3: call_smart_element_option ('123','',true); $index=$array['index_id']; echo $color; usw.....
 * ********************************************************************************
 */
function call_smart_element_option($element_id, $array_name = FALSE, $output_global = FALSE) {
	
// 	if (! $_SESSION ['smart_page_id'] or ! $_SESSION ['site_id']) {
// 		echo "<div aligen=center>Diese Domain scheint keine Zugriffsberechtigung zu haben. call_smart_element_option</div>\n";
// 		exit ();
// 	}

	// Wenn nur gewiese Werte ausgelesen werden sollen - Sonst werden alle ausgelsen
	// mm@ssi.at am 08.12.2019
	if (is_array ( $array_name ) or ! $array_name) {

		if (is_array ( $array_name )) {
			foreach ( $array_name as $key => $value ) {
				if (! $add_sql)
					$add_sql = " AND";
				else
					$add_sql .= "OR ";
				$add_sql .= " (element_id = '$element_id' AND option_name = '$value' ) ";
			}
		} else
			$add_sql .= "AND element_id = '$element_id' ";

		$query = $GLOBALS ['mysqli']->query ( "SELECT option_name, option_value FROM smart_element_options WHERE 1 $add_sql " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		while ( $array = mysqli_fetch_array ( $query ) ) {
			if ($output_global) {
				$GLOBALS [$array ['option_name']] = $array ['option_value'];
			}
			$output [$array ['option_name']] = $array ['option_value'];
		}
		return $output;
	} else {
		// Wenn nur ein Wert übergeben werden soll (wird nicht als Array übergeben)
		$add_sql = "WHERE element_id = '$element_id' AND option_name = '$array_name' ";
		$query = $GLOBALS ['mysqli']->query ( "SELECT option_name, option_value FROM smart_element_options $add_sql " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		$array = mysqli_fetch_array ( $query );
		return $array ['option_value'];
	}
}

// Speichern von Element-Optionen
function save_smart_element_option($element_id, $array) {
	if (! $_SESSION ['smart_page_id'] or ! $_SESSION ['site_id']) {
		echo "ID zum speichern der Optionen ist nicht definiert";
		exit ();
	}

	foreach ( $array as $key => $value ) {

		$value = stripslashes ( str_replace ( '\r\n', PHP_EOL, $value ) );

		$GLOBALS ['mysqli']->query ( "
			REPLACE INTO smart_element_options SET
            element_id = '$element_id',
			option_name = '$key',
			option_value = '$value'
			" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	}
}



