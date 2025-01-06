// call word - list from automator
function call_word_list(automator_id) {
    
	$.ajax({
		url : "elba/call_word_list.php",
		global : false,
		type : "POST",
		data : ({
		    automator_id : automator_id
		}),
		dataType : "script"
	});
}