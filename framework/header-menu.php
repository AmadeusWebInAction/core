<?php
function header2ndMenu() {
	extract(variable('menu-settings'));
	if (!$isPageMenu) return;
	_headerStart(true);

	$files = _skipNodeFiles(disk_scandir(NODEPATH));
	foreach ($files as $page) {
		//if (cannot_access($slug)) continue;
		$page_r = humanize($page);
		$page_r = $wrapTextInADiv ? '<div>' . $page_r . '</div>' : $page_r;
		echo '<li class="' . $itemClass . '"><a href="' . pageUrl(variable('node') . '/' . $page) . '" class="' . $anchorClass . '">' . $page_r . '</a>';
		if (disk_is_dir(NODEPATH . '/' . $page)) {
			menu('/' . variable('section') . '/' . variable('node') . '/' . $page . '/', [
				'link-to-home' => variable('link-to-site-home'),
				'li-class' => $itemClass,
				'a-class' => $anchorClass,
				'ul-class' => $ulClass,
				'parent-slug' => variable('node') . '/' . $page . '/',
			]);
		}
		echo '</li>' . NEWLINES2;
	}

	if ($social = variable('node-social')) {
		//echo '<li class="' . $itemClass . ' ms-sm-3">social: </li>';
		foreach ($social as $item) {
			extract(specialLinkVars($item));

			echo '<li class="d-inline-block my-2"><a target="_blank" href="' . $url . '" class="mt-2 text-white">'
				. '	<i class="social-icon si-mini text-light rounded-circle ' . $class . '"></i> <span class="d-sm-none btn-light">' . $text . '</span></a></li>';
		}
	}

	echo '</ul> <!-- #end page-menu -->' .variable('2nl');
}

function _headerStart($skipMainMenu = false) {
	extract(variable('menu-settings'));

	if (!isset($groupOuterUlClass)) $groupOuterUlClass = $outerUlClass;
	if (!$noOuterUl) echo NEWLINE . '<ul class="' . $groupOuterUlClass . '">';
	
	if (isset($diagonalSpacer)) echo $diagonalSpacer; //for anbagam!
	
	if($skipMainMenu) return;

	$mainMenu = variable($isPageMenu ? 'nodeSiteName' : 'siteMenuName') . $topLevelAngle;
	if ($wrapTextInADiv) $mainMenu = '<div>' . $mainMenu . '</div>';
	echo '	<li class="' . $itemClass . '"><a class="' . $anchorClass . '" href="javascript: void(0);">' . $mainMenu . '</a>' . NEWLINE;
}

function headerMenuFrom() {
	extract(variable('menu-settings'));
	_headerStart();


$append = variable('scaffold') ? array_merge(['----'], variable('scaffold')) : false;
menu('/' . variable('folder'), [
	'link-to-home' => variable('link-to-site-home'),
	'files-to-append' => $append,
	'a-class' => $anchorClass,
	'ul-class' => $ulClass,
]);
echo '</li>' . NEWLINE;

if ($groups = variable('section-groups')) {
	renderIfCurrentMenu();
	foreach ($groups as $group => $items) {
		$isGroup = true;
		if (is_string($items)) {
			$group = $items;
			$items = [$items];
			$isGroup = false;
		}

		$name = humanize($group);
		if ($wrapTextInADiv) $name = '<div>' . $name . $topLevelAngle . '</div>';

		if ($isGroup) echo '<li class="' . $itemClass . ' ' . $subMenuClass . '"><a class="' . $anchorClass . '">' . $name . '</a>' . NEWLINE;
		if ($isGroup) echo '	<ul class="' . $ulClass . '">' . NEWLINE;

		foreach ($items as $slug) {
			//if (cannot_access($slug)) continue;
			if ($slug[0] == '_') continue;
			renderHeaderMenu($slug);
		}

		if ($isGroup) echo '	</ul>' . variable('2nl');
		if ($isGroup) echo '</li>' . NEWLINE;
	}
} else {
	renderIfCurrentMenu();
	foreach (variable('sections') as $slug) {
		if ($slug[0] == '_') continue;
		//if (cannot_access($slug)) continue;
		renderHeaderMenu($slug);
	}
}

if (function_exists('after_menu')) after_menu();
if (function_exists('network_after_menu')) network_after_menu();
if (!$noOuterUl) echo '</ul> <!-- #end site menu -->' .variable('2nl');
} //end of new headerMenuFrom

function renderHeaderMenu($slug, $node = '', $name = false) {
	$parentSlug = $node ? $node : $slug;

	if ($name) ; //noop
	else if (contains($node, '/'))  { $bits = explode('/', $node); $name = humanize(array_pop($bits)) . ' (' . humanize(array_pop($bits)) . ')'; }
	else if ($node) { $name = humanize($node) . ' (' . humanize($slug) . ')'; }
	else { $name = humanize($parentSlug); }

	extract(variable('menu-settings'));

	$files = false; $tiss = false;
	$standalones = variableOr('standalone-sections', []);
	if (in_array($slug, $standalones)) {
		$tiss = true;
		$files = disk_include(variable('path') . '/' . $slug . '/menu.php', ['callingFrom' => 'header-menu', 'limit' => 5]);
		if ($tsmn = variable(getSectionKey($slug, MENUNAME)))
			$name = $tsmn;
	}

	$homeNA = variable(getSectionKey($slug, MENUNAME) . '_home') == 'off';
	if ($wrapTextInADiv) $name = '<div>' . $name . $topLevelAngle . '</div>';

	echo '<li class="' . $itemClass . ' ' . $subMenuClass . '"><a class="' . $anchorClass . '">' . $name . '</a>';

	if ($node) $slug .= '/' . $node;
	menu('/' . $slug . '/', [
		'a-class' => $anchorClass,
		'ul-class' => $ulClass . ($node ? ' of-node node-' . $node : ''),
		'files' => $files, 'this-is-standalone-section' => $tiss,
		'list-only-folders' => $node == '',
		'list-only-files' => variable('sections-have-files'),
		'link-to-home' => variable('link-to-section-home') && !$homeNA,
		'parent-slug-for-home-link' => $parentSlug . '/',
		'parent-slug' => $node ? $node . '/' : '',
	]);
	echo '</li>' . NEWLINE;
}

function renderIfCurrentMenu() {
	$items = getCurrentMenus();
	if (count($items) == 0) return;

	extract(variable('menu-settings'));
	echo '	<li class="' . $itemClass . '"><a class="' . $anchorClass . '" href="javascript: void(0);"><div>((Current Menu))</div></a>' . NEWLINE;
	echo '		<ul' . cssClass([$ulClass]) . '>' . NEWLINE;

	foreach ($items as $params) {
		renderHeaderMenu($params[0], $params[1]);
	}

	echo '		</ul>' . NEWLINE;
	echo '	</li>' . variable('2nl');
}

function getCurrentMenus() {
	//TODO: if (cannot_access(variable('section'))) return;
	if (variable('section') == variable('node'))
		return [];

	$result = [];

	$toCheck = [
		'/' . variable('section') . '/' . variable('node') . '/'
			=> [ variable('section'), variable('node') ],
		'/' . variable('section') . '/' . variable('node') . '/' . ($nfi1 = variableOr('node-folder-item1', 'nothing')) . '/'
			=> [ variable('section'), variable('node') . '/' . $nfi1 ],
		'/' . variable('section') . '/' . variable('node') . '/' . ($nfi2 = variableOr('node-folder-item2', 'nothing')) . '/'
			=> [ variable('section'), variable('node') . '/' . $nfi2 ],
	];

	foreach ($toCheck as $folRelative => $params) {
		if (!disk_is_dir(variable('path') . $folRelative)) break;
		$result[] = $params;
	}

	return $result;
}
