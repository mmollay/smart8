$(document)
	.ready(
		function() {

		    $('.lazy_load').visibility({
			type : 'image',
			transition : 'fade in',
			duration : 1000
		    });

		    $(window).resize(function() {
			get_padding_hight()
		    });

		    $(window).on('load', function() {
			$('body').css({
			    'opacity' : '1',
			    'transition' : '1s opacity'
			});
			get_padding_hight();
			// open_autopopup();
			
		    });

		    $('.modal-video').fancybox({
			youtube : {
			    controls : 0,
			    showinfo : 0
			},
			vimeo : {
			    color : 'f00'
			}
		    });

		    $('.image img').visibility({
			type : 'image',
			transition : 'fade in',
			duration : 1000
		    });

		    $('#m_menu').sidebar('attach events', '#m_btn').sidebar(
			    'setting', 'transition', 'overlay').sidebar(
			    'setting', 'dimPage', false);

		    // ist notwendig damit der eigene Hintergrund angezeigt
		    // wird!
		    $("body").removeClass("pushable");

		    // Image-Viewer
		    $(".fancybox")
			    .fancybox(
				    {
					openEffect : 'elastic',
					closeEffect : 'elastic',
					beforeShow : function() {
					    var alt = this.element.find('img')
						    .attr('alt');
					    this.inner.find('img').attr('alt',
						    alt);
					    this.title = alt;
					},
					prevEffect : 'elastic',
					nextEffect : 'elastic',
					helpers : {
					    title : {
						type : 'over',
						position : 'top'
					    },
					    overlay : {
						locked : false
					    },
					    buttons : {}
					},
					afterShow : function() {
					    $('.fancybox-wrap')
						    .swipe(
							    {
								swipe : function(
									event,
									direction,
									distance,
									duration,
									fingerCount) {
								    if (direction === 'left'
									    || direction === 'up') {
									$.fancybox
										.prev(direction);
								    } else {
									$.fancybox
										.next(direction);
								    }
								}
							    });
					}
				    });

		    // Scroller
		    $.scrollUp({
			animation : 'slide',
			scrollText : "Zum Anfang"
		    });

		});

// PRÜFT OB SCRIPT BEREITS GELADEN IST
function appendScript(filepath) {
    if ($('head script[src="' + filepath + '"]').length > 0)
	return;

    var ele = document.createElement('script');
    ele.setAttribute("type", "text/javascript");
    ele.setAttribute("src", filepath);
    $('head').append(ele);
}

function get_padding_hight() {
    if ($('.phone-nav').css('position') == 'fixed'
	    && $('.menu_field').css('display') == 'none')
	$('.top_phone_menu').css({
	    'padding-top' : '58px'
	});

    else {
	$('.top_phone_menu').css({
	    'padding-top' : '0px'
	});
    }
}

function open_autopopup(site_id) {
    $
	    .ajax({
		beforeSend : function() {
		    $('.autopopup>.content')
			    .html(
				    `<div class="ui large basic segment"><br><br><div class="ui active inverted dimmer"><div class="ui text loader">Inhalt wird geladen</div></div><p></p></div>`);
		    $('.autopopup').modal({
			centered : false,
			closable : false,
			observeChanges : true,
		    }).modal('show');
		},
		success : function(data) {
		    $('.autopopup>.content').html(data);
		},
		data : ({
		    site_id : site_id
		}),
		url : "gadgets/autopopup/include.inc.php",
		global : false,
		type : 'POST',
		dataType : 'html',
	    });
}


function call_content(site_id) {
	//load content
	$.ajax( {				
		url :"inc/ajax_call_element.php",
		global   : false,
		data : ({ site_id : site_id }),
		type     : "POST",	
		dataType : "html",
		beforeSend: function () { 
		    $("#left_0").html('Inhalt wird geladen' ); 
		    $('#scroll_loader').addClass('active');  },
		success:    function(data){  
		    $("#left_0").html(data);
		    $('#scroll_loader').removeClass('active'); 
//		    SetNewTextfield();
		},
	});    
}
