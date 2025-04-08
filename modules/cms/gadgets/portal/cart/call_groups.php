<?php
$group_id = $_SESSION['group_id'];
if (! isset ( $group_id ))
	$group_id = 0;
	
	// get all menuitems with 1 query
$sql = "SELECT * FROM
		 article_group       
		WHERE company_id = '$company_id'
	    ORDER BY
			`sort` desc, `parent_id` ";
$result = $GLOBALS['mysqli']->query ( $sql ) or die ( mysqli_error ($GLOBALS['mysqli']) );
// AND LangMenu.lang = '{$_SESSION['page_lang']}'

// prepare special array with parent-child relations
$menuData = array ( 'items' => array () , 'parents' => array () );

while ( $menuItem = $result->fetch_assoc ( ) ) {
	$menuData['items'][$menuItem['group_id']] = $menuItem;
	$menuData['parents'][$menuItem['parent_id']][] = $menuItem['group_id'];
}

$menu_structure = buildGroupMap2 ( $group_id, $menuData );
if ($menu_structure) {
	$content_groups = "<button class='ui button' onclick=\"$('#sitemap_dropdown').dropdown('set text','Bitte wählen'); call_group('$group_id')\" >Übersicht</button>";
	$content_groups .= '<div id="sitemap_dropdown" class="ui floating labeled icon dropdown button"><i class="dropdown icon"></i><span class="text">Bitte wählen</span>' . $menu_structure . '</div><br><br>';
}
// Neue Darstellung in einem Dropdown-Menü
function buildGroupMap2($parentId, $menuData) {
	$html = '';
	if (isset ( $menuData['parents'][$parentId] )) {
		$html = '<div class="menu">';
		foreach ( $menuData['parents'][$parentId] as $itemId ) {
			$name = $menuData['items'][$itemId]['title'];
			$group_id = $menuData['items'][$itemId]['group_id'];
			
			$html .= '<div class="item">';
			$html .= "<a href='#' onclick=call_group('$group_id')  class='call_group'>$name</a>";
			
			// find childitems recursively
			$html .= buildGroupMap2 ( $itemId, $menuData );
			
			$html .= '</div>';
		}
		$html .= '</div>';
	}
	return $html;
}

// menu builder function, parentId 0 is the root
function buildGroupMap($parentId, $menuData) {
	$html = '';
	if (isset ( $menuData['parents'][$parentId] )) {
		if (! $html)
			$html = "<ul id='sitemap'>";
		else
			$html = '<ul>';
		foreach ( $menuData['parents'][$parentId] as $itemId ) {
			$name = $menuData['items'][$itemId]['title'];
			$group_id = $menuData['items'][$itemId]['group_id'];
			
			$html .= '<li>';
			$html .= "<a href='#' onclick=call_group('$group_id')  class='call_group'>$name</a>";
			
			// find childitems recursively
			$html .= buildGroupMap ( $itemId, $menuData );
			
			$html .= '</li>';
		}
		$html .= '</ul>';
	}
	return $html;
}
