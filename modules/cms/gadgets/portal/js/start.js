/**
 * Default js - query mm@ssi.at
 */
//var strLoadImg = relative_path+'img/load.png';

$(document).ready(function() {

	var code = getUrlParameter('code');
	getArticleContent(code);
	
	$('#sitemap_dropdown').dropdown();
	
});
	
//Call Content
function getArticleContent(code) {
	if (code) {
		var link = 'gadgets/portal/cart/call_details.php';
	}
	else 
		var link = 'gadgets/portal/sites/portal.php';
	
	$.ajax( {
		url :link,
		global :false,
		type :'POST',
		data: ({load_main : true, group_id : group_id, group_default_id : group_default_id, url_name : url_name, code : code}),
		dataType :'html',
		beforeSend: function(){ $('#portal_content').html("<div class='ui segment'><br><br><br><div class='ui active inverted dimmer'><div class='ui text loader'>...Inhalt wird geladen</div></div><p></p><br><br></div>"); },
		success: function(data) { $('#portal_content').html(data); },
	});	
}	

//Red GET-Parameters
function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),sParameterName,i;

	for (i = 0; i < sURLVariables.length; i++) {
    	sParameterName = sURLVariables[i].split('=');

    	if (sParameterName[0] === sParam) {
       		return sParameterName[1] === undefined ? true : sParameterName[1];
    	}
	}  
}