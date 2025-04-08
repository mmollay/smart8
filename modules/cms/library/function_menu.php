<?

// Textcutter
function shortText($string, $lenght) {
	if (strlen ( $string ) > $lenght) {
		$string = substr ( $string, 0, $lenght ) . "...";
		$string_ende = strrchr ( $string, " " );
		$string = str_replace ( $string_ende, " ...", $string );
	}
	return $string;
}

// Auslesen der Struktur des Menues aus der Datenbank
function generateMenuStructure($page_id, $admin = false, $add_mysql = false) {
	if (! $admin or $_SESSION ['hole_structure'])
		$add_mysql .= ' AND menu_disable = 0 ';

	// get all menuitems with 1 query
	$sql = "
	SELECT menu_text, title, site_id, parent_id, menu_disable, site_url, funnel_id, funnel_short, menu_url, menu_newpage from
	smart_langSite, smart_id_site2id_page
	WHERE smart_langSite.fk_id = smart_id_site2id_page.site_id
	AND smart_id_site2id_page.site_id = smart_langSite.fk_id
	AND page_id = '$page_id' $add_mysql
	ORDER BY parent_id, position
	";
	// AND menu_disable = 0

	$result = $GLOBALS ['mysqli']->query ( $sql ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	// AND LangMenu.lang = '{$_SESSION['page_lang']}'

	// prepare special array with parent-child relations
	$menuData = array ('items' => array (),'parents' => array () );

	while ( $menuItem = mysqli_fetch_assoc ( $result ) ) {
		$menuData ['items'] [$menuItem ['site_id']] = $menuItem;
		$menuData ['parents'] [$menuItem ['parent_id']] [] = $menuItem ['site_id'];
	}
	return $menuData;
}

// Auslesen der Struktur des Menues aus der Datenbank
function generateMenuStructureList($page_id, $admin = false, $add_mysql = false) {
	if (! $admin or $_SESSION ['hole_structure'])
		$add_mysql .= ' AND menu_disable = 0 ';

	if (! $admin or $_SESSION ['list_structure'])
		$parend_id = 'set';

	// get all menuitems with 1 query
	$sql = "
	SELECT menu_text, title, site_id, parent_id, menu_disable, site_url, funnel_id, funnel_short, menu_url, menu_newpage from
	smart_langSite, smart_id_site2id_page
	WHERE smart_langSite.fk_id = smart_id_site2id_page.site_id
	AND smart_id_site2id_page.site_id = smart_langSite.fk_id
	AND page_id = '$page_id' $add_mysql
	ORDER BY parent_id, position
	";
	// AND menu_disable = 0

	$result = $GLOBALS ['mysqli']->query ( $sql ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	// AND LangMenu.lang = '{$_SESSION['page_lang']}'

	// prepare special array with parent-child relations
	$menuData = array ('items' => array (),'parents' => array () );

	while ( $menuItem = mysqli_fetch_assoc ( $result ) ) {

		if ($parend_id)
			$menuItem ['parent_id'] = 0;

		$menuData ['items'] [$menuItem ['site_id']] = $menuItem;
		$menuData ['parents'] [$menuItem ['parent_id']] [] = $menuItem ['site_id'];
	}
	$_SESSION ['all_sites'] = '';

	return $menuData;
}

// Erzeugt die Baumstruktur das Menu in einem <ul><li>
function buildMenuAdmin1($parentId, $menuData) {
	// print_r ($menuData);
	$html = '';
	if (isset ( $menuData ['parents'] [$parentId] )) {
		if (! $html)
			$html = "<ul>";
		else
			$html = '<ul>';
		foreach ( $menuData ['parents'] [$parentId] as $itemId ) {
			$name = $menuData ['items'] [$itemId] ['menu_text'];
			if (! $name)
				$name = $menuData ['items'] [$itemId] ['title'];
			$id = $menuData ['items'] [$itemId] ['site_id'];
			$manu_disable = $menuData ['items'] [$itemId] ['menu_disable'];
			if ($name) {

				if ($manu_disable)
					$set_type = 'disabled';
				else
					$set_type = 'default';

				$html .= "<li id='$id' data-jstree='{\"type\":\"$set_type\",\"opened\":\"true\"}'>";
				$html .= "<a href='#'>$name</a>";
				// $html .= "<span class='actions'><a class='ui mini compact blue icon button' href='#' onclick=\"CallContentSite('$id')\" ><i class='linkify icon'></i></a><a href='#'>$name</a></span>";
				// find childitems recursively
				$html .= buildMenuAdmin1 ( $itemId, $menuData );
				$html .= '</li>';
			}
		}
		$html .= '</ul>';
	}
	return $html;
}

// Erzeugt die Baumstruktur das Menu in einem <ul><li>
function buildMenuAdmin($parentId, $menuData) {
	// 	 ($menuData);
	$html = '';
	if (isset ( $menuData ['parents'] [$parentId] )) {
		if (! $html)
			$html = "<ul>";
		else
			$html = '<ul>';
		foreach ( $menuData ['parents'] [$parentId] as $itemId ) {
			$name = $menuData ['items'] [$itemId] ['menu_text'];
			if (! $name)
				$name = $menuData ['items'] [$itemId] ['title'];
			$id = $menuData ['items'] [$itemId] ['site_id'];
			$manu_disable = $menuData ['items'] [$itemId] ['menu_disable'];
			if ($name) {

				if ($manu_disable)
					$set_type = 'disabled';
				else
					$set_type = 'default';

				$html .= "<li id='$id' data-jstree='{\"type\":\"$set_type\"}'>"; // "opened\":true,

				// if (strlen ( $name ) >= 22) {
				// $name = substr ( $name, 0, 22 ) . "...";
				// } // $html .= "<a href='#'>$name</a>";

				$html .= "<span class='actions'>
				<a title='Seite öffnen' data-position='left center' class='tooltip ui mini compact blue icon button' onclick=\"CallContentSite('$id')\" ><i class='linkify icon'></i></a>
				<a data-html=\"<i class='mouse pointer icon'></i>(Rechter Mauseklick)\" data-position='left center' class='tooltip'>$name</a></span>";
				// find childitems recursively
				$html .= buildMenuAdmin ( $itemId, $menuData );
				$html .= '</li>';
			}
		}
		$html .= '</ul>';
	}
	return $html;
}

// Erzeugt die Baumstruktur das Menu in einem <ul><li>
function buildMenuFunnelAdmin($parentId, $menuData) {
	// print_r ($menuData);
	$html = '';
	if (isset ( $menuData ['parents'] [$parentId] )) {
		if (! $html)
			$html = "<ul>";
		else
			$html = '<ul>';
		foreach ( $menuData ['parents'] [$parentId] as $itemId ) {
			$name = $menuData ['items'] [$itemId] ['menu_text'];
			if (! $name)
				$name = $menuData ['items'] [$itemId] ['title'];
			$id = $menuData ['items'] [$itemId] ['site_id'];
			$manu_disable = $menuData ['items'] [$itemId] ['menu_disable'];
			if ($name) {
				$html .= "<li id='$id'>";
				if ($menuData ['items'] [$itemId] ['funnel_short'])
					$html .= "<a href='#'>$name</a>";
				else
					$html .= "<span class='actions'><a class='ui mini compact blue icon button tooltip' title='Seite öffnen' href='#' onclick=\"CallContentSite('$id')\" ><i class='linkify icon'></i></a><a href='#'>$name</a></span>";
				// find childitems recursively
				$html .= buildMenuFunnelAdmin ( $itemId, $menuData );
				$html .= '</li>';
			}
		}
		$html .= '</ul>';
	}
	return $html;
}

// Erzeugt die Baumstruktur das Menu in einem <ul><li>
function buildMenuUl($parentId, $menuData) {
	$html = '';
	if (isset ( $menuData ['parents'] [$parentId] )) {

		$html = '<ul>';
		foreach ( $menuData ['parents'] [$parentId] as $itemId ) {
			$name = $menuData ['items'] [$itemId] ['menu_text'];
			if (! $name)
				$name = $menuData ['items'] [$itemId] ['title'];
			$id = $menuData ['items'] [$itemId] ['site_id'];

			if ($name) {
				$html .= "<li>";

				// find childitems recursively
				$html2 = buildMenuUl ( $itemId, $menuData );

				if (! $html2)
					$onclick = "onclick=\"CallContentSite('$id')\"";
				else
					$onclick = '';

				$html .= "<a href=\"#\" $onclick>" . $name . "</a>";
				$html .= $html2;
				$html .= '</li>';
			}
		}
		$html .= '</ul>';
	}
	return $html;
}

// Erzeugt die Baumstruktur das Menu in einem <ul><li>
function buildMenu($parentId, $menuData, $version) {
	$html = '';
	if (isset ( $menuData ['parents'] [$parentId] )) {
		if (! $html) {
			if ($version == 'sitemap') {
				$version = "";
				$main_id = "sitemap";
			} else {
				$version = "sm $version";
				$main_id = "main-menu";
			}

			$html = "<ul id='$main_id' class='$version'>";
		} else
			$html = '<ul>';
		foreach ( $menuData ['parents'] [$parentId] as $itemId ) {
			$name = $menuData ['items'] [$itemId] ['menu_text'];
			if (! $name)
				$name = $menuData ['items'] [$itemId] ['title'];
			$id = $menuData ['items'] [$itemId] ['site_id'];

			if ($id == $_SESSION ['site_id']) {
				$class = "current";
			} else {
				$class = "";
			}

			if ($name) {
				$html .= "<li>";
				// $html .= "<a href='index.php?site_select=$id' class='$class'>" . $name . "</a>";
				$html .= "<a href=\"#\" onclick=\"CallContentSite('$id')\" class='$class menu_item_a'>" . $name . "</a>";

				// find childitems recursively
				$html .= buildMenu ( $itemId, $menuData, '' );
				$html .= '</li>';
			}
		}
		$html .= '</ul>';
	}
	return $html;
}

// Erzeugt die Baumstruktur das Menu in einem <DIV>
function buildMenuDiv($parentId, $menuData) {
	$html = '';
	if (isset ( $menuData ['parents'] [$parentId] )) {
		$html = "<div class='item'>";
		foreach ( $menuData ['parents'] [$parentId] as $itemId ) {

			$name = $menuData ['items'] [$itemId] ['menu_text'];

			if (! $name)
				$name = $menuData ['items'] [$itemId] ['title'];
			$id = $menuData ['items'] [$itemId] ['site_id'];

			if ($name) {

				$html .= "<a href=\"#\" onclick=\"CallContentSite('$id')\" class='item menu_item_a'>" . $name . "</a>";
				// find childitems recursively
				$html .= buildMenuDiv ( $itemId, $menuData );
			}
		}
		$html .= "</div>";
	}
	return $html;
}

/*
 * GET ARRAY form the structure
 */
function buildMenuArray($parentId, $menuData, $level) {
	static $array;

	for($space = 0; $space < $level; $space ++) {
		$set_space .= "&nbsp;&nbsp;&nbsp;'-";
	}
	$level ++;

	if (isset ( $menuData ['parents'] [$parentId] )) {
		foreach ( $menuData ['parents'] [$parentId] as $itemId ) {
			$name = $menuData ['items'] [$itemId] ['menu_text'];
			if (! $name)
				$name = $menuData ['items'] [$itemId] ['title'];
			$id = $menuData ['items'] [$itemId] ['site_id'];

			if ($name) {
				$array [$id] = "$set_space nach " . $name;
				// find childitems recursively
				buildMenuArray ( $itemId, $menuData, $level );
			}
		}
	}
	return $array;
}

// Anzeigen des Menüs zum aufklappen in einer Sitebar
function call_sitebar() {
	$menuData = generateMenuStructure ( $_SESSION ['smart_page_id'] );
	$structure = buildMenuDiv ( 0, $menuData );
	return "
	<div id='m_menu' class='ui right inverted vertical sidebar menu '>" . $structure . "</div>"; // visible orange
}

/*
 * GET ARRAY form the structure
 */
// Erzeugt die Baumstruktur das Menu in einem <DIV>
function buildMenuSemantic($parentId, $menuData, $class_font = false) {
	$html = '';
	if (! $code) {
		$code = rand ( 5, 1000000 );
	}

	if (isset ( $menuData ['parents'] [$parentId] )) {
		foreach ( $menuData ['parents'] [$parentId] as $itemId ) {

			$name = $menuData ['items'] [$itemId] ['menu_text'];

			if (! $name)
				$name = $menuData ['items'] [$itemId] ['title'];
			$id = $menuData ['items'] [$itemId] ['site_id'];

			$menu_url = $menuData ['items'] [$itemId] ['menu_url'];
			$menu_newpage = $menuData ['items'] [$itemId] ['menu_newpage'];

			if ($id == $_SESSION ['site_id']) {
				$class = "active";
			} else {
				$class = "";
			}

			if ($name) {

				$set_sub = buildMenuSemantic ( $itemId, $menuData, $class_font );

				if ($menu_newpage)
					$target = "target = '_newpage$id' ";
				else
					$target = '';

				if (! $menu_url and $id)
					$onclick = "href=\"#\" onclick=\"CallContentSite('$id')\" $target ";
				elseif ($menu_url)
					$onclick = "href=\"$menu_url\" $target ";
				else
					$onclick = '';

				if (! $set_sub) {
					$html .= "\n <a $onclick class='$class $class_font item menu_item_a'> $name</a>";
				} else {
					if ($parentId == 0)
						$add_dropdown = 'ui dropdown menu_dropdown';
					else {
						$add_dropdown = '';
						$style_submenu = 'color:black;';
					}
					$html .= "\n<div class='$add_dropdown item menu_item_a'><a style='$style_submenu' $onclick > $name</a>\n<i class='dropdown icon'></i>\n<div class='menu'>";
					$set_dropdown ++;
					$html .= $set_sub;
					$html .= "\n</div>";
					$html .= "\n</div>";
				}
			}
		}
		// if ($parentId) $html .= "\r\t</div></div></div>";
		// if ($parentId) $html .= "\r\t</div>";
	}
	return $html;
}
function convertNumberToWord($num = false) {
	$num = str_replace ( array (',',' ' ), '', trim ( $num ) );
	if (! $num) {
		return false;
	}
	$num = ( int ) $num;
	$words = array ();
	$list1 = array ('','one','two','three','four','five','six','seven','eight','nine','ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen' );
	$list2 = array ('','ten','twenty','thirty','forty','fifty','sixty','seventy','eighty','ninety','hundred' );
	$list3 = array ('','thousand','million','billion','trillion','quadrillion','quintillion','sextillion','septillion','octillion','nonillion','decillion','undecillion','duodecillion','tredecillion','quattuordecillion','quindecillion','sexdecillion','septendecillion','octodecillion',
			'novemdecillion','vigintillion' );
	$num_length = strlen ( $num );
	$levels = ( int ) (($num_length + 2) / 3);
	$max_length = $levels * 3;
	$num = substr ( '00' . $num, - $max_length );
	$num_levels = str_split ( $num, 3 );
	for($i = 0; $i < count ( $num_levels ); $i ++) {
		$levels --;
		$hundreds = ( int ) ($num_levels [$i] / 100);
		$hundreds = ($hundreds ? ' ' . $list1 [$hundreds] . ' hundred' . ' ' : '');
		$tens = ( int ) ($num_levels [$i] % 100);
		$singles = '';
		if ($tens < 20) {
			$tens = ($tens ? ' ' . $list1 [$tens] . ' ' : '');
		} else {
			$tens = ( int ) ($tens / 10);
			$tens = ' ' . $list2 [$tens] . ' ';
			$singles = ( int ) ($num_levels [$i] % 10);
			$singles = ' ' . $list1 [$singles] . ' ';
		}
		$words [] = $hundreds . $tens . $singles . (($levels && ( int ) ($num_levels [$i])) ? ' ' . $list3 [$levels] . ' ' : '');
	} // end for loop
	$commas = count ( $words );
	if ($commas > 1) {
		$commas = $commas - 1;
	}
	return implode ( ' ', $words );
}

