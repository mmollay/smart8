<?
require_once '../config.inc.php';

foreach ( $_POST as $key => $value ) {
	if ($value) {
		$GLOBALS [$key] = $GLOBALS ['mysqli']->real_escape_string ( $value );
	}
}

$GLOBALS ['mysqli']->query ( "INSERT INTO automator SET
            word = '$word',
            description = '$description',
            client_id   = '$client_id',
            comment = '$comment',
    		account_id     = '$account'
			" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );

$id = mysqli_insert_id ( $GLOBALS ['mysqli'] );


if ($id) {
	$word = $_POST['word'];
	echo "$('body').toast({message: 'Neuer Automator wurde angelegt!'}); ";
	echo "add_val_dropdown('automator_id','$id','$description'); ";
	echo "$('.form_elba#word').val('$word').focus();";
	echo "$('#modal_form_automator_edit').modal('hide');";
}
