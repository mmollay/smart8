$(function() {
    window.fbAsyncInit = function() {
    	FB.init({ appId : appId, cookie : true, xfbml : true, channelUrl : channelUrl, oauth : true });
    };
    $('body').append('<div id="fb-root"></div>');
    $.getScript(document.location.protocol + '//connect.facebook.net/en_US/all.js');
  })

function CallAfterLogin() {
	FB.login(function(response) {
		if (response.status === "connected") {

			LodingAnimate(); // Animate login
			FB.api('/me?fields=email', function(data) {

				if (data.email == null) {

					// Facbeook user email is empty, you can check something
					// like this.
					alert("You must allow us to access your email id!");
					ResetAnimate();

				} else {
					AjaxResponse();
				}

			});
		}
	}, { scope : scope });
}

// functions
function AjaxResponse() {
	// Load data from the server and place the returned HTML into the matched
	// element using jQuery Load().
	if ( lp != 'center') {  var add_path = 'gadgets/login/'; }
	else add_path = '';
	$.ajax({
		url : add_path+'facebook/process_facebook.php', global : false,
		// async : false,
		type : "POST", dataType : "html", 
		success : function(data) {
			if (data == 'ok') {
				if ( lp == 'center') $(location).attr('href','../../../ssi_dashboard/');  
				else
					window.top.location.reload();
			} else {
				alert('Anmeldung ist ungültig'+data);
			}

		},
		error: function(data){
            alert("Fehler bei der Anmeldung über Facebook. Es scheint ein Paket nicht installiert zu sein (siehe INFO.txt)");
		}
	});
}

// Show loading Image
function LodingAnimate() {
	if ( lp != 'center') {  var add_path = 'gadgets/login/'; }
	else add_path = '';
	$("#LoginButton").hide(); // hide login button once user authorize the application
	$("#results").html('<br><img src="'+add_path+'facebook/img/ajax-loader.gif" /> Bitte warten, es wird verbunden'); 
}

// Reset User button
function ResetAnimate() {
	$("#LoginButton").show(); // Show login button
	$("#results").html(''); // reset element html
}