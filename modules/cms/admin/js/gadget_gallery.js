		represention($('#representation').attr('value'));
		show_hide_count_item();
		$('#representation').bind('keyup change',function() { represention($('#representation').attr('value')) });
		
		function represention(id){
			$('.row_carousel').html(''');
			if (id =='carousel' ){ $('.row_carousel').hide(); }
			else if (id =='list' )  { $('#row_size,#row_col').show(); }
			else if (id =='logos' ) { $('#row_gallery_style').show(); }
		}
								
		$('#owl_autocount').bind('keyup change',function() { show_hide_count_item() });
								

function show_hide_count_item() {   if ($('#owl_autocount').attr('checked')){ $('#row_owl_item').hide(); } else { $('#row_owl_item').show(); } }
