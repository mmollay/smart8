<?php
include (__DIR__ . '/../f_config.php');

foreach ($_POST as $key => $value) {
    if ($value) {
        $GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string($value);
    }
}

if ($bill_id) {

    // Standartwerte auslesen
    $sql_earning = "SELECT bill_id,brutto,bill_number FROM bills WHERE bill_id = $bill_id ";
    $automator_earning_query = $GLOBALS['mysqli']->query($sql_earning) or die(mysqli_error($GLOBALS['mysqli']));
    $automator_earning_array = mysqli_fetch_array($automator_earning_query);
    $bill_number = $automator_earning_array['bill_number'];
    $amount = $automator_earning_array['brutto'];

    // check inner DB
    $sql = "SELECT  date,amount FROM data_elba  where elba_id = '$elba_id' ";
    $query = $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
    $build_earning = mysqli_fetch_array($query);
    $date = $build_earning['date'];

    // Werte in Bill speichern
    $GLOBALS['mysqli']->query("UPDATE bills SET
	date_booking = '$date',
	booking_total = '$amount',
	booking_command = 'automatic elba',
    elba_id = '$elba_id'
	WHERE bill_id = '$bill_id'
	") or die(mysqli_error($GLOBALS['mysqli']));

    // bill_id in elba_list speichern
    $GLOBALS['mysqli']->query("UPDATE data_elba SET connect_id = '$bill_id' WHERE elba_id = '$elba_id' ") or die(mysqli_error($GLOBALS['mysqli']));

    echo "$('body').toast({message: '$date $bill_id wurde verbucht'});";
    echo "$('#tr_earning$bill_id').remove();";
}

// } elseif ($automator_id && $elba_id) {
// 	echo "$('body').toast({message: '$count_insert neue Einträge wurden erzeugt'});";
// 	echo "$('#table_$automator_id').remove();";
// 	echo "$('#tr_$automator_id').remove();";
// }



