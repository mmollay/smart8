$(document).ready(function(){
	
	var user_id = $('.user_id').attr('id');
	var dynamic_page_id = $('.dynamic_page_id').attr('id');
	var dynamic_user_id = $('.dynamic_user_id').attr('id');
	
	id = $('.dynamic_site_left').attr('id');
	
	content_left = $.ajax( {
		url : "gadgets/dynamic/call_site.php",
		global : false,
		async : false,
		type : "POST",
		data : ( { id : id, user_id : user_id, dynamic_page_id: dynamic_page_id, dynamic_user_id : dynamic_user_id,  position : 'left' }),
		dataType : "html"
	}).responseText;
	
	content_right = $.ajax( {
		url : "gadgets/dynamic/call_site.php",
		global : false,
		async : false,
		type : "POST",
		data : ( { id : id, user_id : user_id, dynamic_page_id: dynamic_page_id, dynamic_user_id : dynamic_user_id,  position : 'right' }),
		dataType : "html"
	}).responseText;
	
	$('.dynamic_site_left').html(content_left);
	$('.dynamic_site_right').html(content_right);
});