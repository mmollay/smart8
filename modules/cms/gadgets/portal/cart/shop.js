 /**
* Default js - query mm@ssi.at
* */

$(document).ready(function(){
	
	//wird uebergeben von include.inc.php
	var relative_path  = "gadgets/portal/";
	
	if (!$('#article_in_cart').val())  $('#button_call_order').hide(); 
	
	$('#button_logout_account').click( function(){
		$('#portal_content').load(relative_path+"inc/logout_user.php",{load_main:true});
	})
	
	$("#button_setting_account").click( function(){
		$.ajax( {
			url :relative_path+"sites/form_setting.php",
			type: 'POST',
			success:     function(data){ 
				$("#modal_content_reg").html(data);		
				$("#modal_reg").modal('show');
			}
		});		
	})
	
	//Meine Artikel seite aufrufen
	$('#button_my_products').click( function(){
		$.ajax( {
			url :relative_path+"sites/mask_my_articles.php",
			data: ({  ajax:true }),
			type: 'POST',
			success:    function(data){ 
				$("#modal_content_article").html(data);		
				$("#modal_article").modal('show'); 
			}
		});
	})
	
	//$('.content').load(relative_path+"sites/mask_my_articles.php");
	
	//Meine Artikel seite aufrufen
	//$('#button_back_to_shop').click( function(){ $('.content').load(relative_path+"sites/mask_shop.php", { group_id:group_default_id}); })
	
});

var strTitleSettings   = 'Einstellungen';
var strTitleReg        = 'Registrierung';
var strTitleLogin      = 'Anmelden';
var strTitleInfo       = 'Information';
var setTextFirstLogIn  = '<div align=center>Um bestellen zu können,<br>bitte vorher einloggen oder registrieren<br><br><button class="ui button" id=button_login_account onclick=button_login_account() >Login</button><button class="ui button" id=button_reg_account onclick=button_reg_account()>Registrieren</button></div>';
var setConnectToPaypal = 'Verbinden mit Paypal...';
var setSendOrder       = 'Bestellung wird versendet...';
var setEmptyCart       = 'Keine Artikel im Warenkorb';

/*
 * New Account - Mask
 */
function button_reg_account(){
	$.ajax( {
		url :relative_path+"sites/form_new_account.php",
		type: 'POST',
		success:  function(data){ 
			$("#modal_content_reg").html(data);		
			$("#modal_reg").modal('show');
		}
	});
}

/*
 * Login-Mask
 */
function button_login_account(){
	$.ajax( {
		url :relative_path+"sites/form_login.php",
		type: 'POST',
		success:     function(data){ 
			$("#modal_content_login").html(data);		
			$("#modal_login").modal('show');
		}
	});
}

/*
 * Add new Article
 */
function add_article_save(id) {
	$('#cart').load(relative_path+"cart/call_cart.php", { ajax:true, id:id });
	$('#button_call_order').show(); 
}

/*
 * New article in the cart
 */
function add_article(id) {
	$.ajax( {
		url :relative_path+"cart/call_cart.php",
		data: ({  ajax:true, id:id}),
		async: false,
		type: 'POST',
		success:    function(data){ 
			$('#cart').html(data); 
			$('#button_call_order').show(); 
		}
	});
}

/*
 * Gruppenauswahl
 */
function call_group(id) {
	$.ajax( {
		url :relative_path+"cart/call_articles.php",
		data: { ajax:true, group_id:id },
		async: false,
		type: 'POST',
		beforeSend: function(){ $('#portal_products').html("<div class='ui segment'><br><br><br><div class='ui active inverted dimmer'><div class='ui text loader'>...Inhalt wird geladen</div></div><p></p><br><br></div>"); },
		success:    function(data){ 
			$('#portal_products').html(data);
			//$('.add_cart,.show_details,.add_cart_first_login_msg').button();	
		}
	});	
}

/*
 * Back to base
 */
function cart_close() {
	$("#modal_product_detail").modal('hide');
}

/*
 * remove Article from the cart
 */
function del_article(id) {
		$.ajax( {
			url :relative_path+'cart/call_cart.php',
			data: ({ ajax:true, del_id:id }),
			async: false,
			type: 'POST',
			success:    function(data){ 
				$('#cart').html(data);
				if (!$('#article_in_cart').val())  $('#button_call_order').hide(); 
				//$("#button_call_order").button();
			}
	})
}

function show_detail(id,inside) {	
	$.ajax( {
		url :relative_path+"cart/call_details.php",
		data: ({  ajax:true, id:id, inside:inside }),
		//async: false,
		type: 'POST',
		beforeSend : function(){ 	
		},
		success:     function(data){ 
			//_gaq.push(['_trackEvent', 'Popup', 'Clicked', 'Popup title']);
			$("#modal_content_product_detail").html(data);		
			$("#modal_product_detail").modal('show');
		}
	});
}

/*
 * Aufruf einer Seite wenn noch nicht registriert wurde
 */
function add_cart_first_login_msg(id) {
	add_article(id)
	$("#modal_content_login").html(setTextFirstLogIn);		
	$("#modal_login").modal('show');
	
}

/*
 * Back to base
 */
function cart_back() {
	$('.content').load(relative_path+"sites/mask_shop.php");
}

/*
 * Aufruf des Warenkorbes
 */
function call_order(){
	$.ajax( {
		url :relative_path+"cart/call_order.php",
		data: ({  ajax:true }),
		async: false,
		type: 'POST',
		success:    function(data){ 
			$("#modal_content_product_detail").html(data);		
			$("#modal_product_detail").modal('show');
		}
	});
}

/*
 * call submit page
 */
function submit_order() {
	$.ajax( {
		url :relative_path+'cart/call_order2.php',
		data: ({ ajax:true }),
		//async: false,
		type: 'POST',
		beforeSend: function() { 
			$('#modal_product_detail').html('<br><br><br><br><div align=center>'+setSendOrder+'</div>'); 	
		},
		success:    function(data){ 
			//$('#portal_window').dialog('close');
			$('#modal_product_detail').modal('hide');
			$('#content_cart').html(data);
			$('#cart').html(setEmptyCart);
		}
	});
}

/*
 * Connect to Paypal
 */
function submit_order_paypal() {
	$.ajax( {
		url :relative_path+'cart/call_order2.php',
		data: ({ ajax:true, paypal:true }),
		//async: false,
		type: 'POST',
		beforeSend: function() { 
			$('#portal_window').html('<br><br><br><br><div align=center>'+setConnectToPaypal+'</div>'); 
			//$('#window_progress').dialog({'width':'500px','height':'300',modal:true}).html('<div align=center>'+setConnectToPaypal+'</div>');
		},
		success:    function(data){ 
			//window.open(data,'Paypal');
			//$('#window_progress').html(data);
			$(location).attr({'href':data});
			//return false;
			//$('#portal_window').dialog('close');
			//$('#content_cart').html(data);
			//$("#button_call_order").button();
		}
	});
}