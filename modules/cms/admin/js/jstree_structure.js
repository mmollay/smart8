function call_jstree_structure(set) {
	
	if (set == 'funnel')
		var set_icons = false;
	else {
		var set = 'menu';
		var set_icons = false;
	}
	
	var newArray = { 
		'types' : { 'default' : { 'icon' : 'unhide icon orange' }, 'disabled' : { 'icon' : 'unhide icon disabled' } },
		'contextmenu' : { 'items' : customMenu },
		'core' : {
			'check_callback' : function(operation, node, node_parent, node_position, more) { },
			"themes":{ "variant" : "large", 'icons': set_icons } 
		},
		"search": {
            "show_only_matches" : true
        },
		'plugins' : [ 'search', 'themes', 'html_data', 'ui', 'crrm', 'contextmenu', 'unique', 'dnd', 'cookies', 'types' ]
	};
	
	$('.'+set+'#menu_jstree').jstree(newArray).on(
			'move_node.jstree',
			function(e, data) {
				$.ajax({
					url : 'admin/ajax/menu/tree_move.php',
					global : false,
					type : 'POST',
					data : ({
						'id' : data.node.id, 'parent' : data.parent,
						'old_parent' : data.old_parent,
						'position' : data.position,
						'old_position' : data.old_position
					}), dataType : 'html', success : function(data) {
						reload_gadget_class('menu_field');
					}
				});

			}).on('rename_node.jstree', function(e, data) {
		$.ajax({
			url : 'admin/ajax/menu/tree_rename.php', global : false,
			// async : false,
			type : 'POST', data : ({ 'id' : data.node.id, 'text' : data.node.text }), dataType : 'html',
			// beforeSend : function() {

			// align=center>...Bitte warten</div>'); },
			success : function(data) {
				// Menufeld wird neu geladen
				reload_gadget_class('menu_field');
			}
		})
	}).on('create_node.jstree', function(e, data) {
		alert('Funktion noch nicht aktiv');
	});

	$('#s').submit(function(e) {
		e.preventDefault();
		$('.'+set+'#menu_jstree').jstree(true).search($('#q').val());
	});
	
	$('.tooltip').popup();
}

function customMenu(node) {
	var items = {
		'open' : {
			'icon' : 'icon linkify', 'label' : 'Seite öffnen',
			'action' : function(data) {
				//$('.modal-edit-menu').modal('hide');
				CallContentSite(node.id)
				//location.href='index.php?site_select='+node.id	
			}
		},
			
		'item1' : {
			'icon' : 'icon hide',
			'label' : 'Im Menü ausblenden',
			'action' : function() {
				$.ajax({
					url : 'admin/ajax/menu/menufield_disable.php',
					global : false, type : 'POST', data : ({
						'id' : node.id
					}), dataType : 'html', success : function(data) {
						reload_gadget_class('menu_field');
						call_sitebar_content();
					}
				});
			}
		},
		'item2' : {
			'icon' : 'icon unhide',
			'label' : 'Im Menü einblenden',
			'action' : function() {
				$.ajax({
					url : 'admin/ajax/menu/menufield_enable.php',
					global : false, type : 'POST', data : ({
						'id' : node.id
					}), dataType : 'html', success : function(data) {
						reload_gadget_class('menu_field');
						call_sitebar_content();
					}
				});

			}
		},
		'option' : {
			'icon' : 'icon settings', 'label' : 'Einstellungen',
			'action' : function() {
				$.ajax( {
					url      : "admin/ajax/form_edit.php",
					global   : false,
					async    : false,
					type     : 'POST',
					dataType : 'html',
					data     : { 'list_id':'site_form', 'update_id':node.id },
					success  : function(data) {
						$('#option_site>.content').html(data);
						//$('#option_site').modal({allowMultiple: true, observeChanges : true });
						$('#option_site').modal('show');
					}	
				});
			}
		},
		'delete' : {
			'icon' : 'icon remove', 'label' : 'Seite löschen',
			'action' : function() {
				call_semantic_form(node.id,'modal_small','admin/ajax/form_delete.php','site_structure_sitebar','');
			}
		}
	}

	if (node.type === 'default') {
		delete items.item2;
	} else if (node.type === 'disabled') {
		delete items.item1;
	}
	
	

	return items;
}
	