//Aufruf nach erfolgter Challenge (1)
function call_success_challenge(id) {
    
	//Nachfragne welcher Tag es ist
	$.post( path21+'/inc/call_day.php', { challenge_id : id }, function( day ) {
		if (day == '21') {	
			$.fn.imgExplosion({
		        img: path21+'/js/jquery.imgExplosion/star.png',
		        angle:true,
		        //centerOn:this,
		        interval:2,
		        minThrow:300,
		        maxThrow:900,
		        rot:true,
		        angle:true,
		        explode: false,
		        extraWidth:200,
		        num:300
		    });
			ion.sound({ sounds: [{name: "applaus2"}], path: path21+"/js/ion.sound/sounds/", preload: true, volume: 1.0 });
			ion.sound.play("applaus2");
			//call_form(id,'GESCHAFFT!!!!',path21+'/ajax/form_comment_success.php',['600','400']);
			call_modal_form(id,'',path21+'/ajax/form_comment_success.php');
		}
		else {
			ion.sound({ sounds: [{name: "bell_ring"}], path: path21+"/js/ion.sound/sounds/", preload: true, volume: 1.0 });
			ion.sound.play("bell_ring");
			
			//call_form(id,'Kommentar zum Verlauf',path21+'/ajax/form_comment.php',['600','400']);
			call_modal_form(id,'',path21+'/ajax/form_comment.php');
		}
	});

}

//Aufruf nach erfolgter Challenge (1)
function call_lose_challenge(ID) { 
	ion.sound({
		sounds: [{name: "fail"}],
		path: path21+'/js/ion.sound/sounds/',
        preload: true,
        volume: 1.0
    });
    
    ion.sound.play("fail");
    //call_form(id,'Dein Feedback',path21+'/ajax/form_comment_cancel.php',['600','500']);
    call_modal_form(ID,'',path21+'/ajax/form_comment_cancel.php');
}

//Bestätigen der Challenge
function call_form_challenge_multi(ID) {
	//call_form(ID,'Geschaffte Tage', path21+'/ajax/form_challenge.php?save_multi=true',['700','600']);
	call_modal_form(ID,'',path21+'/ajax/form_multiconfirm.php');
}

//Ruft Form zu multi - bestätigen
function call_form_challenge(ID) {
	$.post( path21+'/inc/save_action.php', { challenge_id : ID, action : 'success' }, function( data ) {
		if ( data == 'ok') {
			call_success_challenge(ID);
    		call_filter();
    		call_list();
		}
	});
}

/*
 * CALL CANCEL CHALLENGE
 */
function cancel_challenge(ID) {
	$('#chancel_challenge').modal({
	    onDeny    : function(){
	    	$('#modal_delete').modal('hide');
	    	//return false;
		},
		onApprove : function() {
			$.post( path21+'/inc/save_action.php', { challenge_id : ID, action:'fail' }, function( data ) {
				if ( data == 'ok') {
					call_lose_challenge(ID);
		    		call_filter();
		    		call_list();
				}
			});
		}
	});	
	$('#chancel_challenge').modal('show');
}


/*
 * DELETE CHALLENGE
 */
function del_challenge(ID) {
	$('#chancel_challenge').modal({
	    onDeny    : function(){
	    	$('#modal_delete').modal('hide');
		},
		onApprove : function() {
			$.post( path21+'/inc/del_challenge.php', { challenge_id : ID }, function( data ) {
				if ( data == 'ok') {
		    		call_filter();
		    		call_list();
				}
			});
		}
	});	
	$('#chancel_modal_content').html('Challenge tatsächlich löschen?');
	$('#chancel_challenge').modal('show');
}


function call_modal_form(ID,option,path) {
	if (!path) path = path21+'/ajax/form_challenge.php';
	$.ajax( {
		url      : path,
		global   : false,
		async    : false,
		type     : "POST",
		data     : ({ update_id : ID, option : option   }),
		dataType : "html",
		success  : function(data) { 
			$("#modal_content").html(data);
			$("#modal_challenge").modal('show');
		},
	});
}


/*
 * Ruft die Anzahl der aktuellen Kommentare und trägt diese ein
 */
function call_count_comment(id,element) {
	$.ajax({
		type: "POST",
		url: path21+'/inc/call_count_comment.php',
		data: { id:  id, 'element': element },
		dataType: "script"
	});
}


/*
 * Like & Comment - Functions
 */
function set_button_cool(id,element) {
	$.ajax({
		type: "POST",
		url: path21+'/inc/call_save_cool.php',
		data: { id:  id, 'element': element },
		dataType: "script"
	});
}

function cancel_comment(id,element){
	$( ".container_form_usercomment_"+element+"_"+id ).html('');
}

function activate_hide_button(){
	$('.comment_edit_button').hide();
	$('.container_comment_div').hover( function() { 
		$('#' + this.id + ' .comment_edit_button').show();		
	}, function() {
		$('#' + this.id + ' .comment_edit_button').hide();
	});
}

function submit_comment(id,element){
	comment = $('#'+id+'.textarea_comment').val();
	$.ajax({
		type: "POST",
		url: path21+'/inc/call_save_comment.php',
		data: { id:  id, 'comment': comment, 'element': element },
		dataType: "html",
		success: function (data) {
			$('.comment_list'+element+'_'+id).prepend(data);
			$('.container_form_usercomment_'+element+'_'+id).html('');
			activate_hide_button();
			call_count_comment(id,element);
		}
	});
}

function set_form_textarea (id,element){
	$.ajax({
		type: "POST",
		url: path21+'/inc/call_form_usercomment.php',
		data: { id:  id, 'element': element },
		dataType: "script"
	});	
}

function edit_own_comment(id) {
	$.ajax({
		type: "POST",
		url: path21+'/inc/call_form_update_usercomment.php',
		data: { id:  id },
		success: function(data){
			$('#comment_text_'+id).html(data);
			$('#'+id+'.textarea_comment').autosize().focus();
		},
		dataType: "html"
	});	
}

function submit_update_comment(id){
	comment = $('#'+id+'.textarea_comment').val();
	$.ajax({
		type: "POST",
		url: path21+'/inc/call_save_update_comment.php',
		data: { id:  id, 'comment': comment },
		success: function(data){
			$('#comment_text_'+id).html(data);
			activate_hide_button();
		},
		dataType: "html"
	});
}

//Ruft alten Content wenn bei UPDATE abgebrochen wird
function cancel_update_comment(id){
	$.ajax({
		type: "POST",
		url: path21+'/inc/call_comment_content.php',
		data: { id:  id },
		success: function(data){
			$('#comment_text_'+id).html(data);
		},
		dataType: "html"
	});	
}

function rm_own_comment(id) {
	$.ajax({
		type: "POST",
		url: path21+'/inc/call_rm_comment.php',
		data: { id:  id },
		dataType: "script"
	});	
}