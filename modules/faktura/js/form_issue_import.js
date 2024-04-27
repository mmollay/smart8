/*******************************************************************************
 * Laden der Basiseinstellung fuer die Contact_site - anlegen, loeschen,
 * bearbeiten von Usern und Gruppen martin@ssi.at am 14.08.2011
 ******************************************************************************/

$.fn.enterKey = function (fnc) {
    return this.each(function () {
        $(this).keypress(function (ev) {
            var keycode = (ev.keyCode ? ev.keyCode : ev.which);
            if (keycode == '13') {
                fnc.call(this, ev);
            }
        })
    })
}

$(document).ready( function() {
    
    	$( "#sortable_label" ).sortable({
    	    update: function(event, ui) {
                var productOrder = $(this).sortable('toArray').toString();
                
                $.ajax ({
 		   url: 'inc/sortable_issue_templates.php',
 		   global: false,
 		   data: ({ data : productOrder}),
 		   type: 'POST',
 		   dataType: "text",
 		  success:    function(data){
 		     $("#sortable-10").text (data);      
 		  }
                }); 
            }
    	});
	
    	$('#setTEXT').focus();
	
});