/**
 * 
 */
if (!destination) var destination = ''; 
	
$(document).ready(function(){   
	removeSearch();

	//Filter setzen für php - Menu
	set_filter_php();
	
	//Autofit wird gesetzt
	if (!Cookies.get('autofit')) Cookies.set('autofit',1);
	//if (!Cookies.get('bicyclinglayer')) Cookies.set('bicyclinglayer',1); 

	//Aufruf der Map mit Übergabe des vorgewählten Destination
	call_filter_map('map_zip',destination);
	
	$('.menu .item').tab();
	
	$('#map_sidebar').sidebar({ context: $('.bottom#map_sidebar_segment'), closable : false, dimPage : false })
	.sidebar('setting', 'transition', 'overlay');
	//.sidebar('setting','onVisible', function() { $('.load_segment').animate({ marginLeft: '260px' }) })
//	.sidebar('setting','onShow', function() {$('#map_filter_icon').removeClass('right').addClass('left'); $('.load_segment').css('margin-left','260px');  } )
//	.sidebar('setting','onHide', function() {$('#map_filter_icon').removeClass('left').addClass('right'); $('.load_segment').animate({ marginLeft: '0px' }); } );
//	//.sidebar('toggle');
	$('.toggle_sidemap').click ( function () { $('#map_sidebar').sidebar('toggle'); });
	
	$('.search_input').change( function() { call_filter_map('map_search', $('.search_input').val() ) });
	
	// open in fullscreen
	$('#fullsrceen_toggle').click(function() {
		if ($.fullscreen.isFullScreen()) { 
			$.fullscreen.exit()
			$('.load_segment').height($height);
			$('#count_trees').addClass('floating');
	
		}
		else {
			$('#map_container').fullscreen();
			$('.load_segment').height(screen.height-50);
			$('#count_trees').removeClass('floating');
			
			
			//$('#load_map').width(screen.width-280);
			//$('#container').css({'background-color':'white','padding':'5px'});
		}
		return false;
	});
	
	$(window).resize(function() {
	    $('#load_map').height($(window).height() - 140);
	});

	$(window).trigger('resize');
	
	
}); 