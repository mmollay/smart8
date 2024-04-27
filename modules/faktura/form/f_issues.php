<?php
$add_js .= "<script type=\"text/javascript\" src=\"js/form_issues.js\"></script>";

if (isset ($_GET['clone']))
    $clone = true;

if ($_POST['update_id']) {
    $arr['sql'] = array(
        'query' => "SELECT * from issues WHERE bill_id = '{$_POST['update_id']}' "
    );

    if ($clone)
        $bill_number = mysql_singleoutput("SELECT MAX(bill_number) as bill_number FROM issues  ", "bill_number") + 1;
} else {
    $bill_number = mysql_singleoutput("SELECT MAX(bill_number) as bill_number FROM issues  ", "bill_number") + 1; // WHERE company_id = '{$_SESSION['faktura_company_id']}'
    if ($_COOKIE['last_date_create'])
        $date_create = $_COOKIE['last_date_create'];
}

// Wenn kein Datum im COOKIE ist wird das letzt eingetragene verwendet
if (!$date_create and !$_POST['update_id']) {
    $date_create = mysql_singleoutput("SELECT date_create FROM issues WHERE bill_number = (SELECT MAX(bill_number) as bill_number FROM issues)", "date_create"); // WHERE company_id = '{$_SESSION['faktura_company_id']}'
}

// if (! $_POST['update_id'] or $clone) {
// $focus_date = true;
// }

// Konten auslesen aus der Datenbank
$sql_query = $GLOBALS['mysqli']->query("SELECT * FROM accounts WHERE `option` = 'out' order by title ") or die (mysqli_error($GLOBALS['mysqli'])); // AND company_id = '{$_SESSION['faktura_company_id']}'
while ($sql_array = mysqli_fetch_array($sql_query)) {
    $account_array_out[$sql_array['account_id']] = $sql_array['title'] . "(" . $sql_array['tax'] . "%)";
}

$sql_query = $GLOBALS['mysqli']->query("SELECT * FROM issues_group order by name") or die (mysqli_error($GLOBALS['mysqli'])); // AND company_id = '{$_SESSION['faktura_company_id']}'
while ($sql_array = mysqli_fetch_array($sql_query)) {
    if ($sql_array['name']) {
        $issues_group_array[$sql_array['issues_group_id']] = $sql_array['name'];
    }
}

$arr['field'][] = array(
    'type' => 'div',
    'class' => 'two fields'
);
$arr['field']['date_create'] = array(
    'type' => 'date',
    'value' => $date_create,
    'label' => 'Erstelldatum',
    'validate' => true,
    'focus' => true
);
// $arr['field']['date_booking'] = array ( 'type' => 'date' , 'label' => 'Buchungsdatum' , 'validate' => true );
$arr['field'][] = array(
    'type' => 'div_close'
);

// $arr['field']['bill_number'] = "value_default=>$bill_number_clone##value=>$bill_number##type=>input##text=>Rechnungsnummer##size=>40##maxlength=>40##required_text=>Rechnungsnummer eingeben##check=>true" );
$arr['field'][] = array(
    'type' => 'div',
    'class' => 'fields'
);
$arr['field']['bill_number'] = array(
    'type' => 'input',
    'label' => 'Nr.',
    'value_default' => $bill_number,
    'validate' => true,
    'class' => 'three wide'
);
$arr['field']['description'] = array(
    'type' => 'input',
    'label' => 'Beschreibung',
    'validate' => true,
    'class' => 'desc thirteen wide',
    'search' => true,
);
// $arr['field'][] = array ( 'label' => 'Beschreibung', 'type' => 'content' , text=>'<div class="ui search"><input id="description" class="prompt" type="text" placeholder="Begriff eingeben"><div class="results"></div></div>');
$arr['field'][] = array(
    'type' => 'div_close'
);



$arr['field']['client_id'] = array(
    'class' => 'search',
    'type' => 'dropdown',
    'label' => "Firma <div id='add_group' data-position='bottom center' class='button mini ui blue label circular'>+</div>",
    'array' => $issues_group_array,
    'clear' => true
);

$arr['field'][] = array(
    'type' => 'div',
    'class' => 'fields'
);
$arr['field']['brutto'] = array(
    'class' => 'three wide',
    'type' => 'input',
    'label' => 'Betrag (Brutto)',
    'format' => 'euro'
);
$arr['field']['netto'] = array(
    'class' => 'three wide',
    'type' => 'input',
    'label' => 'Betrag (Netto)',
    'format' => 'euro'
);
$arr['field']['account'] = array(
    'class' => 'ten wide search',
    'type' => 'dropdown',
    'label' => 'Konto',
    'array' => $account_array_out,
    'validate' => true,
    'clear' => true
);
$arr['field'][] = array(
    'type' => 'div_close'
);

$arr['field'][] = array(
    'type' => 'div',
    'class' => 'two fields'
);
// $arr['field']['description'] = array ( 'type' => 'input' , 'label' => 'Beschreibung' , 'validate' => true );
$arr['field'][] = array(
    'type' => 'div_close'
);

$arr['field']['comment'] = array(
    'type' => 'input',
    'label' => 'Kommentar'
);

if (!$clone and $_POST['update_id']) {
    $arr['hidden']['bill_id'] = $_POST['update_id'];
}

if ($clone)
    $success_add_js = "$('#modal_form_edit').modal('hide');";
elseif (!$_POST['update_id']) {
    $success_add_js = "
			new_bill_number = parseInt($('#bill_number').val());
			$('#bill_number').val(new_bill_number+1);
			$('#description,#date_booking,#company_1,#netto,#brutto,#comment').val('');
			$('#dropdown_account, #dropdown_client_id').dropdown('clear');
			$('#netto').prop('disabled', false);
			$('#brutto').prop('disabled', false);
            $('#date_create').focus().focus(900);
			";
}

$success = "
		
				if ( data  == 'number_exist' ) {
					$('#message').message({status:'error', title:'Rechnungsnummer bereits vergeben' });
				}
				else if ( data  == 'ok' ) {
					$success_add_js
					$('#message').message({status:'info', title:'Eine neue Ausgabe wurde gespeichert' });
					table_reload();
				}
				else if ( data  == 'update') {
					$('#modal_form_edit').modal('hide');
					$('#message').message({status:'info', title:'Die Ausgabe wurde aktualisiert' });
					table_reload();
				}";

$arr['ajax'] = array(
    'id' => 'form_issues',
    'success' => $success,
    'dataType' => "html"
);




