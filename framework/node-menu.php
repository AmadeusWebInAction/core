<?php
renderNodeMenu();

function renderNodeMenu() {
	extract(variable('menu-settings'));
	_menuULStart(NOPAGESTART);

	$mainMenu = variable('nodeSiteName');
	if ($wrapTextInADiv) $mainMenu = '<div>' . $mainMenu . '</div>';

	$bc = false;
	//if ($bc)
	renderBreadcrumbsMenu();

	//if ($bc) return;

	$files = _skipNodeFiles(disk_scandir(NODEPATH));
	foreach ($files as $page) {
		//if (cannot_access($slug)) continue;
		$page_r = humanize($page);
		$page_r = $wrapTextInADiv ? '<div>' . $page_r . '</div>' : $page_r;
		//href="' . pageUrl(variable('node') . '/' . $page) . '" 

		$files = []; $tiss = false;
		$standalones = variableOr('standalone-pages', []);
		if (in_array($page, $standalones)) {
			variable('page_parameter1_safe', $page);
			$tiss = true;
			$menuFile = concatSlugs([variable('path'), variable('section'), variable('node'), $page, 'menu.php']);
			$files = disk_include($menuFile, ['callingFrom' => 'header-page-menu', 'limit' => 5]);
			if ($tsmn = variable(getSectionKey($page, MENUNAME)))
				$page_r = $tsmn;
		}

		echo '<li class="' . $itemClass . '"><a class="' . $anchorClass . '">' . $page_r . '</a>';

		if (disk_is_dir(NODEPATH . '/' . $page)) {
			menu('/' . variable('section') . '/' . variable('node') . '/' . $page . '/', [
				'link-to-home' => variable('link-to-site-home'),
				'files' => $files, 'this-is-standalone-section' => $tiss,
				'li-class' => $itemClass,
				'a-class' => $anchorClass,
				'ul-class' => $ulClass,
				'parent-slug' => $tiss ? '' : variable('node') . '/' . $page . '/',
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

	_menuULStart('page');
}

function renderBreadcrumbsMenu() {
	if (variable('dont-show-current-menu')) return; //TODO: high - rename setting

	extract(variable('menu-settings'));
	$items = _getBreadcrumbs();

	//peDie('menu', $items);
	if (count($items) == 0) return;

	extract(variable('menu-settings'));

	$section = variable('section');

	echo '<li class="' . $itemClass . '"><a class="' . $anchorClass . '"><i class="bi-asterisk"></i> Breadcrumbs</a>';
	echo NEWLINE . '<ul class="' . $ulClass . '">';

	foreach ($items as $relativeFol => $nodeSlug) {
		$menuName = humanize($nodeSlug);
		if ($wrapTextInADiv) $menuName = '<div>' . $menuName . '</div>';

		//echo NEWLINE . '<ul class="' . $ulClass . '">';

		echo '<li class="' . $itemClass . '"><a class="' . $anchorClass . '">' . $menuName . '</a>';

		menu('/' . $section . '/' . $relativeFol, [
			'a-class' => $anchorClass,
			'ul-class' => $ulClass . (true ? ' of-node node-' . $nodeSlug : ''),
			'link-to-home' => true,
			'parent-slug-for-home-link' => $relativeFol,
			'parent-slug' => $relativeFol,
		]);

		echo '</li>' . NEWLINE;
	}

	echo '</ul></li>' . NEWLINE;
}

function _getBreadcrumbs() {
	//TODO: if (cannot_access(variable('section'))) return;

	$breadcrumbs = variable('breadcrumbs');
	if (empty($breadcrumbs)) return [];

	$result = [];
	$section = variable('section');
	$node = variable('node');

	$base = $node . '/';

	foreach ($breadcrumbs as $item) {
		$base .= $item . '/';
		$result[$base] = $item;
	}

	return $result;
}
