$(document).ready(function(){
	var user_id = $('.user_id').attr('id');
	//Aufruf der Progressbar
	var retval = []
	$('.dynamic_element').each(function(){
		
		$(this).html($.ajax( {
			url : "gadgets/dynamic/call_element.php",
			global : false,
			async : false,
			type : "POST",
			data : ( { id : this.id, user_id : user_id}),
			dataType : "html"
		}).responseText);
		
	})
	return retval	
});