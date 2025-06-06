function call_jstree_structure1() {
	
	$('#menu_jstree_modal').jstree(
			{
				'types' : {
					'default' : { 'icon' : 'unhide icon orange' }, 
					'disabled' : { 'icon' : 'unhide icon disabled' }
				},
				'contextmenu' : {
					'items' : customMenu1
				},
				'core' : {
					'check_callback' : function(operation, node, node_parent,
							node_position, more) {
						// alert(operation);

						// operation can be 'create_node',
						// 'rename_node',
						// 'delete_node', 'move_node' or 'copy_node'
						// in case of 'rename_node' node_position is
						// filled with the
						// new node name

					}
				},
				"search": {
		            "show_only_matches" : true
		        },
				'plugins' : [ 'search', 'themes', 'html_data', 'ui', 'crrm',
						'contextmenu', 'unique', 'dnd', 'cookies', 'types' ]
			}).on(
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
			type : 'POST', data : ({
				'id' : data.node.id, 'text' : data.node.text
			}), dataType : 'html',
			// beforeSend : function() {
			// $('#dialog_window').html('<br><div
			// align=center>...Bitte warten</div>'); },
			success : function(data) {
				reload_gadget_class('menu_field');
				
			}
		})
	}).on('create_node.jstree', function(e, data) {
		alert('Funktion noch nicht aktiv');
	});

	$('#s1').submit(function(e) {
		e.preventDefault();
		$('#menu_jstree_modal').jstree(true).search($('#q1').val());
	});
}

function customMenu1(node) {
	var items = {
		'open' : {
				'icon' : 'icon linkify', 'label' : 'Seite öffnen',
				'action' : function(data) {
					CallContentSite(node.id)
					$('.modal-edit-menu').modal('hide');
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
						editMenuStructure();
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
						editMenuStructure();
					}
				});

			}
		},
		'rename' : {
			'icon' : 'icon edit', 'label' : 'Menütitel umbennenen',
			'action' : function(data) {
				var inst = $.jstree.reference(data.reference);
				obj = inst.get_node(data.reference);
				inst.edit(obj);
			}
		},
		'option' : {
			'icon' : 'icon settings', 'label' : 'Seiteneinstellungen bearbeiten',
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
				call_semantic_form(node.id,'modal_small','admin/ajax/form_delete.php','site_structure','');
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