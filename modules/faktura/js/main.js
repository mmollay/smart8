$(document).ready(function() {

    load_content_set_menu_semantic('faktura', 'start');

    $('#button_send_remind').click(function() {
	var content = $.ajax({
	    url : "exec/SendReminderEmailFaktura.php",
	    global : false,
	    async : false,
	    type : "POST",
	    dataType : "html"
	}).responseText;

	if (content == 'ok') {
	    alert(content);
	} else
	    alert(content);
    });

    $('.menu_dropdown.item').dropdown({
	on : 'hover'
    });
});