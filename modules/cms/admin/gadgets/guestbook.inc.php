<?php
// vorhandene Guestbooks auslesen
$array_guestbook['new'] = "-Neues Gästebuch anlegen-";

// Read all exists Formulars from the page
$form_query = $GLOBALS['mysqli']->query("SELECT * from smart_gadget_guestbook where page_id = '{$_SESSION['smart_page_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
while ($form_array = mysqli_fetch_array($form_query)) {
    if (! $form_array['title'])
        $form_array['title'] = 'Gästebuch_' . $form_array['guestbook_id'];
    $array_guestbook[$form_array['guestbook_id']] = $form_array['title'];
}

if (! $guestbook_id) {
    $guestbook_id = 'new';
}

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'two fields' );
$arr['field']['guestbook_id'] = array(
    'tab' => 'first',
    'label' => "Gästebücher",
    'type' => 'dropdown',
    'array' => $array_guestbook,
    'validate' => 'Gästebuch wählen',
    'value' => $guestbook_id
);
$arr['field']['guestbook_name'] = array(
    'tab' => 'first',
    'label' => "Gästbuchname",
    'type' => 'input',
    'value' => $title,
    'validate' => true,
    'focus' => true
);
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

// Wenn bereits Guestebuch vorhanden ist, dann wird Name in den Input geschrieben
if ($guestbook_id)
    $onLoad = "
			if ($('#guestbook_id').val() && $('#guestbook_id').val() != 'new' ) {
				$('#guestbook_name').val($('#'+$('#guestbook_id').val()).html() );
			}
			
 			$('#guestbook_id').change(function() {
				if ($('#guestbook_id').val() == 'new'){
					$('#guestbook_name').val('');
					$('#guestbook_name').focus();
				}
				else {
					if ($('#guestbook_id').val()) $('#guestbook_name').val ($('#'+$('#guestbook_id').val()).html());
				}
 			});	";