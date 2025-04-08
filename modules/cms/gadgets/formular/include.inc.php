<?php
$output .= "<div id='container-form$layer_id'><div class='ui message small'><br><br><br><div class='ui active inverted dimmer'><div class='ui text loader'>Formular wird geladen</div></div><br><br></div></div>";

$add_js2 .= "$(document).ready(function() { $.ajax( { url : 'gadgets/formular/formular.php', global :false, type :'POST', data: ({'layer_id':$layer_id}), dataType :'html', success: function(data) { $('#container-form$layer_id').html(data); } }); });";
