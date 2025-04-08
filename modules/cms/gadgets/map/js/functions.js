if (!set_path) var set_path = '';

/***************************************************
 * Setzt die Filter Session für die Anzeige der Map
 ***************************************************/
function call_filter_map(id,value,type) {
	
	if (value  !== "undefined") {
		if (value  == "undefined") value = 1;		
		$.ajax( { type: "POST", url : set_path+'gadgets/map/ajax/set_session.php', data : ({ id:id, value : value, type : type }) });		
		loadMap(Cookies.get("autofit"),Cookies.get("bicyclinglayer"));	
		loadMenu();
	}
}

function removeFilter(id) {
	$.ajax ({ type :'POST', dataType:'script', url: set_path+'gadgets/map/ajax/remove_session.php', data : ({ id : id }) });
}

/**********************************
 * Setzt das Filter für Checkboxen
 **********************************/
function set_filter(id,type) {
	if ($("#"+id).attr('checked')) { value = 1;  } else { value = 0; }
	call_filter_map(id,value,type);
}

/***************************************************
 * Ladet die Baumpaten-liste
 ***************************************************/
function loadList(ID) {
	//$('.map_content').hide(); 
	if ($('#load_'+ID).html() == '') { 
		 $.ajax( {
			url      : set_path+'gadgets/map/ajax/list_'+ID+'.php',
			global   : false,
			type     : "POST",
			dataType : "html",
			beforeSend : function() {  $('#load_'+ID).addClass('loading');  },
			success : function(data) { $('#load_'+ID).removeClass('loading').html(data); }		
		 });
	} 
}

/***************************************************
 * Ladet die Fruitmap neu
 ***************************************************/
function loadMap(autofit,bicyclinglayer) {

	//Cookies.set('autofit',autofit);	
	//Cookies.set('bicyclinglayer',bicyclinglayer);

	if (autofit == true ) autofit = 'autofit';
	if (bicyclinglayer == true) bicyclinglayer = 'bicyclinglayer';
	
	$.ajax( {
		url      : set_path+'gadgets/map/inc/data_map.php',
		global   : false,
		async    : false,
		type     : "POST",
		dataType : "json",
		success : function(array_map) {
			
			if (array_map == null ) { $('#map-no-results').show('fast').delay(3000).hide('fast'); return; }
			
			$('#load_map').gmap3({ clear: {  } });
			$("#load_map").gmap3({
				map:{ options:{ streetViewControl: true, fullscreenControl: false }, },
				marker:{
		        	  values:array_map,
		        	  cluster:{
		        	      radius:20,
		        	      // This style will be used for clusters with more than 0 markers
		        	      0: {
		        	        content: "<div class='cluster cluster-1'>CLUSTER_COUNT</div>",
		        	        width: 30,
		        	        height: 30
		        	      },
		        	  },
		        	  //options:{ icon: "http://www.obststadt.at/explorer/images/tree_klein2.png"},
		        	  events:{
		        		  click: function(marker, event, context){ 
		        			  callInfoWindow(marker, event, context); 
		        		  }  
		              }
		        }
			},bicyclinglayer,autofit);
				
			if (array_map.length == 1) $("#load_map").gmap3("get").setZoom(18);
			
			//Zaehler für Bäume anzeigen
			
			//after_loadMap();
			
		}
	});	
}

//Wird auch im Adminbereich verwendet
function after_loadMap(){
	
	//ladet Tabelle nur dann neu wenn diese bereits aufgerufen wurde
	if ($('#load_client').html() != '') { 
		table_reload('map_client');
	}
	if ($('#load_sort').html() != '') { 
		table_reload('map_sort');
	}
	if ($('#load_sortgroup').html() != '') { 
		table_reload('map_speciesgroup');
	}
	
	$.ajax( {
		url      : set_path+'gadgets/map/inc/call_count_trees.php',
		global   : false,
		async    : false,
		type     : "POST",
		dataType : "html",
		beforeSend : function() { $('#count_trees').html('..loading');  },
		success : function(data) { $('#count_trees').html(data); }
	});
} 

/************************************
 * Abrufen des aktuellen Contents
 ************************************/
function callDataWindow(id) {
	content = $.ajax( {
		url      : set_path+'gadgets/map/inc/data_call_window.php',
		global   : false,
		async    : false,
		type     : "POST",
		data     : ({ tree_id : id }),
		dataType : "html",
		//beforeSend : function() { $('#tree_dialog').html('Bitte warten')  },
		//success : function(data) { $('#tree_dialog').html(data) }
	}).responseText;
	return content;
}

/************************************
 * fuer InfoWindow
 ************************************/
function callInfoWindow(marker, event, context){
	var map = $("#load_map").gmap3("get"),infowindow = $("#load_map").gmap3({get:{name:"infowindow"}});
	
	//if (!tree_id) tree_id = context.id;
	
	data = callDataWindow(context.id) //Call Content via AJAX
    if (infowindow){
      infowindow.open(map, marker);
      infowindow.setContent(data);
    } else {
    	$("#load_map").gmap3({ infowindow:{ anchor:marker,  options:{content: data} } });
    }
}

function callInfoWindow_close(marker, event, context){
	var map = $("#load_map").gmap3("get"),infowindow = $("#load_map").gmap3({get:{name:"infowindow"}});
	 infowindow.open(map, marker);
	 infowindow.close();	
}

/***********************************
 * Call Sponsoring Mask
 ***********************************/
function form_sponsing_mask(ID) {	
	$.ajax( {
		url      : set_path+'gadgets/map/ajax/form_ordertree.php',
		global   : false,
		//async    : false,
		data     : ({ tree_id : ID }),
		type     : "POST",
		dataType : "html",
		beforeSend : function() { 
			//$('#modal_ordertree').modal();
			$('#modal_ordertree>.content').html('bitte warten');
			$('#modal_ordertree').modal({ allowMultiple: true, observeChanges : true, autofocus: false, closable: false }).modal('show');
		},
		success : function(data) { 
			$('#modal_ordertree>.content').html(data);
//			$('#modal_ordertree').modal({ allowMultiple: true, observeChanges : true, autofocus: false, closable: false }).modal('show');
		}
	});
}

/***********************************
 * Menue_Filter laden
 ***********************************/
function loadMenu() {
	$.ajax( {
		url      : set_path+'gadgets/map/ajax/call_menu_filter.php',
		global   : false,
		//async    : false,
		type     : "POST",
		dataType : "html",
		beforeSend : function() { 
			$('#menu_filter').html('Bitte warten')  
		},
		success : function(data) { 
			$('#menu_filter').html(data); 			
		}
	});
}

/***********************************
 * Löscht suchfeld-value session
 ***********************************/
function removeSearch() {
		 $.ajax( { url : set_path+'gadgets/map/ajax/remove_search.php' }); 
}


/***********************************
 * beim ersten laden wir autofocs und mehr gesetzt (Session und Cookie)
 ***********************************/

function set_filter_php(){
	 $.ajax( { url : set_path+'gadgets/map/ajax/set_filter.php' });
}