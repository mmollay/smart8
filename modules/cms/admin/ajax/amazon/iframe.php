
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<script type='text/javascript' src='../../../smart_form/jquery-ui/jquery.min.js'></script>
<script type='text/javascript' src='../../../admin/js/smart_form.js'></script>
<script type='text/javascript'>
// Get HTML from page
$(document).ready(function() {
	call_amazon('<?=$_GET['layer_id']?>','<?=$_GET['product_id']?>','<?=$_GET['generate']?>');
});

function call_amazon(layer_id,product_id,generate) {
	$.ajax( {
		beforeSend : function() { 
			window.parent.$('.ui.modal.amazon').modal({ allowMultiple: true, observeChanges : true });
		},	
		success:    function(data){ 
			if (data == 'error')
				alert('Kein Produkt gefunden');
			else {
				//Wird angelegt, damit die Werte ausgelesen werden 
				$('#amazon_content').html(data);

				//Wird für die Vorschau aufgerufen	
				if ( generate == 0 ) {
					 var amazon_price = ""; 
					 var amazon_specification ='';

					if (typeof $('.pzr-features-containers').html() !== "undefined" ) 
						 var amazon_specification ="<div id='amanzon_specification'>"+$('.pzr-features-containers').html()+"</div>";

					if (typeof $('#priceblock_ourprice').html() !== "undefined" )  
						var amazon_price ="<div id='amanzon_specification' style='color:red;'><b>"+$('#priceblock_ourprice').html()+"</b></div>";
						
					window.parent.$("#modal_amazon_content").html(`
							<div id='amanzon_title'><b>`+$('#productTitle').text()+`</b></div>
							<div id='amanzon_text'>`+$('#feature-bullets').html()+`</div>
							`+amazon_price+`
							<img id='amanzon_pic' src=`+$('#landingImage').attr('src')+`>
							`+amazon_specification+`
					`);
					
					window.parent.$('.ui.modal.amazon').modal({ allowMultiple: true, observeChanges : true });
					window.parent.$('.ui.modal.amazon').modal('refresh');
					
				}
				//Wird nach erzeugen wieder geschlossen
				else {

					//Übergeben und anlegen eines Splitters
					$.ajax( {
						type :"POST",			
						url :"generate_amazon_splitter.php",
						data :( {
							'layer_id' : layer_id,
							'amazon_id' : product_id,
							'amanzon_specification' : $('.pzr-features-containers').html(),
							'amazon_title': $('#productTitle').text() ,
							'amazon_pic': $('#landingImage').attr('src') ,
							'amazon_bullets': $('#feature-bullets').html(),
							'amazon_price': $('#priceblock_ourprice').html(),
							'amazon_pic_gallery' : $('#altImages').html(),
							'amazon_description' : $('#productDescription').html()
							 }),
						global   : false,
						async    : false,
						dataType: 'html',
						beforeSend : function() {
							//window.parent.$('.ui.modal.amazon').modal('hide');
							//window.parent.$('#sort_'+layer_id).html('<div class="ui basic segment"><br><br><div class="ui active inverted dimmer"><div class="ui text loader">Amazon-Inhalte werden geladen...</div></div><p></p></div>');
						},	
						success:    function(data){
							//löscht Layerinhalt und stoppt die weiteren Ausfühungn und Ladeprozesse vom Amazon 
							window.parent.$('#iframe_amazon').attr('src','');

							//lade die ganze Seite neu
							window.parent.$('.button_reload').click(); 
							//window.parent.CallContentLayer(layer_id);
						}
					});
					
				}
				
			}					
		},	
		type :"POST",			
		url :"call_amazon_content.php",
		data :( {  'product_id' : product_id }),
		global   : false,
		//async    : false,
		dataType: 'html',
	});

	
}
</script>
</head>
<body>
	<div id='amazon_content' style='display: none'>content</div>
</body>
</html>