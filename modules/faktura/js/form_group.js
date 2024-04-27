/**
 * 
 */
$(document).ready(function() {

	//Close - button for the DIALOG
 	$('.ui-dialog-titlebar-close').click ( function() {
 		$('#internet_text,#internet_inside_text').ckeditor(function(){ this.destroy(); });	
 	});
 	
});
	