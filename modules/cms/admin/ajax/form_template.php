<?php
include_once ('../../../login/config_main.inc.php');
include ('../../smart_form/include_form.php');

$template_array['new'] = "Als neue Vorlage speichern";

// Templates auslesen
$sql_query = $GLOBALS['mysqli']->query ( "SELECT title,template_id,set_public FROM smart_templates where user_id = '{$_SESSION['user_id']}' order by set_public" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
while ( $fetch_array = mysqli_fetch_array ( $sql_query ) ) {
	if ($fetch_array['set_public'])
		$zusatz = 'Öffentlich -> ';
	else
		$zusatz = 'Privat -> ';
	
	$template_id = $fetch_array['template_id'];
	$template_name = $fetch_array['title'];
	$template_array[$template_id] = "$zusatz " . $template_name;
}

$arr['form'] = array ( 'action' => "admin/ajax/form_template2.php" , 'id' => 'form_template' );
$arr['ajax'] = array (  'dataType' => "html" ,  'success' => " if (data == 'ok') { $('#ProzessBarBox').message({ status:'info', title: 'Vorlage wurde erzeugt!' }); $('.form-template').modal('hide'); } " );

$arr['field']['template_id'] = array ( 'label' => 'Vorlagen' , 'type' => 'dropdown' , 'array' => $template_array );
$arr['field']['template_title'] = array ( 'label' => 'Titel' , 'type' => 'input',  'focus' => true );
$arr['field']['template_url'] = array ( 'label' => 'Webadresse' , 'type' => 'input' );
$arr['field']['template_text'] = array ( 'label' => 'Beschreibung' , 'type' => 'textarea' , 'rows' => '3' );

// Just for Superuser
if (in_array ( $_SESSION['user_id'], $_SESSION['array_superuser_id'] )) {
	$arr['field']['set_public'] = array ( 'label' => 'Seite veröffentlichen' , 'type' => 'checkbox' );
} else {
	$arr['field']['propose_public'] = array ( 'label' => '<span id=msg_update_public>Zur Veröffentlichung vorschlagen</span>' , 'type' => 'checkbox' );
}

$arr['button']['submit'] = array ( 'value' => 'Speichern' , 'icon' => 'save', 'color'=>'blue' );
$arr['button']['close'] = array ( 'value' => 'Abbrechen' , 'color' => 'gray' ,  'js' => "$('.form-template').modal('hide');" );
$output = call_form ( $arr );

echo $output['html'];
echo $add_js;
echo $output['js'];
echo "<script type=\"text/javascript\">
		$(document).ready(function(){
		$('#template_id').bind('change keyup', function() {

			if ( $('#template_id').val() == 'new') {
				$('#propose_public').attr('checked', false);
				$('#template_title').val('');
				$('#template_text').val('');
				$('#template_url').val('');
				$('#template_title').focus();
			}
			else {
			$.ajax( {
				url :'admin/ajax/template_values.php',
				global :false,
				data: ({ template_id : $('#template_id').val() }),
				type :'POST',
				dataType :'json',
		        success : function (arrayFromPHP) { 
					var template_title = arrayFromPHP.title;
					var template_text  = arrayFromPHP.text;
					var template_url  = arrayFromPHP.url;
					var propose_public = arrayFromPHP.propose_public;
					var set_public     = arrayFromPHP.set_public;
			
					$('#template_title').val(template_title);
					$('#template_text').val(template_text);
					$('#template_url').val(template_url);
					
					if (set_public == '1') {
						$('#set_public_text').html(' Vorlage veröffentlicht').css({'color':'green'});
						$('#msg_update_public').html('Veröffentlichung updaten');
						$('#set_public').attr('checked', true);
					}
					else {
						$('#set_public_text').html('');
						$('#msg_update_public').html('Zur Veröffentlichung vorschlagen');
						$('#set_public').attr('checked', false);
					}
			
					if (propose_public == '1' ) { $('#propose_public').attr('checked', true);}
					else
					{ $('#propose_public').attr('checked', false);}				
				}
			});

		}
	});
});
</script>";