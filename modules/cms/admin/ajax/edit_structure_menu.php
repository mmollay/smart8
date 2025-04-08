<?
include_once (__DIR__ . '/../../library/function_menu.php');
$menuData = generateMenuStructure ( $_SESSION[smart_page_id],true );
$output_menu = buildMenuAdmin ( 0, $menuData );
$menu_id = 0;

$content_edit_strutcure = "<div id='container'><div id='menu_jstree'>$output_menu</div></div>";

$GLOBALS['add_js'] .= "	
<script type='text/javascript'>
$(function () {
	$('#menu_jstree').jstree({ 
		'contextmenu': {
	        'items': function(n) {
	            var tmp = $.jstree.defaults.contextmenu.items();
	        	delete tmp.ccp; //to delete the edit menu
	            tmp.create.label = 'Neue Seite anlegen';
	            tmp.rename.label = 'Umbenennen';
	            tmp.remove.label = 'Verknüpfung löschen';
	            tmp.create._disabled = true;
	            return tmp;
	        }
	    },
		 'core' : {
        'check_callback' : function (operation, node, node_parent, node_position, more) {
			//alert(operation);
            // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
            // in case of 'rename_node' node_position is filled with the new node name
            
        }
    },
     
		 'plugins' : [ 'themes', 'html_data', 'ui', 'crrm', 'contextmenu','unique', 'dnd' ,'cookies' ]        		      		
	})
	
	//Verknüpfung aus dem Menü loeschen
	.on('delete_node.jstree', function (e, data) {
		$.ajax( {
			url      : 'admin/ajax/menu/tree_remove.php',
			global   : false,
			type     : 'POST',
			data     : ({ 'id' : data.node.id}),
			dataType : 'html',
			success  : function(data) { 
				
			}
		});
	})
	.on('move_node.jstree', function (e, data) {
		$.ajax( {
			url      : 'admin/ajax/menu/tree_move.php',
			global   : false,
			type     : 'POST',
			data     : ({ 
				'id'           : data.node.id, 
				'parent'       : data.parent, 
				'old_parent'   : data.old_parent, 
				'position'     : data.position,
				'old_position' : data.old_position
				 }),
			dataType : 'html',
			success  : function(data) { 
				
				
			}
		});
		
	})
	.on('rename_node.jstree', function (e, data) {
		$.ajax( {
			url      : 'admin/ajax/menu/tree_rename.php',
			global   : false,
			//async    : false,
			type     : 'POST',
			data     : ({ 'id' : data.node.id, 'text':data.node.text  }),
			dataType : 'html',
			//beforeSend : function() {  },
			success  : function(data) { 
				
			}
		})		
	})
	.on('create_node.jstree', function (e, data) {
		alert('Funktion noch nicht aktiv');
	});
});
</script>";