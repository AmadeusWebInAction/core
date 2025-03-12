<?php
extract(variable('menu-settings'));

if (!isset($groupOuterUlClass)) $groupOuterUlClass = $outerUlClass;
if (!$noOuterUl) echo variable('nl') . '<ul class="' . $groupOuterUlClass . '">';

if (isset($diagonalSpacer)) echo $diagonalSpacer; //for anbagam!

$mainMenu = variable('siteMenuName') . $topLevelAngle;
if ($wrapTextInADiv) $mainMenu = '<div>' . $mainMenu . '</div>';

$homeText = 'Home';
if ($wrapTextInADiv) $homeText = '<div>' . $homeText . '</div>';
echo '	<li class="' . $itemClass . '"><a class="' . $anchorClass . '" href="' . variable('url') . '">' . $homeText . '</a></li>' . variable('nl');

echo '	<li class="' . $itemClass . '"><a class="' . $anchorClass . '" href="javascript: void();">' . $mainMenu . '</a>' . variable('nl');
$append = variable('scaffold') ? array_merge(['----'], variable('scaffold')) : false;
menu('/' . variable('folder'), [
	'home-link-to-section' => variable('home-link-to-section'),
	'files-to-append' => $append,
	'a-class' => $anchorClass,
	'ul-class' => $ulClass,
]);
echo '</li>' . variable('nl');

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

		if ($isGroup) echo '<li class="' . $itemClass . ' ' . $subMenuClass . '"><a class="' . $anchorClass . '">' . $name . '</a>' . variable('nl');
		if ($isGroup) echo '	<ul class="' . $ulClass . '">' . variable('nl');

		foreach ($items as $slug) {
			//if (cannot_access($slug)) continue;
			renderHeaderMenu($slug);
		}

		if ($isGroup) echo '	</ul>' . variable('2nl');
		if ($isGroup) echo '</li>' . variable('nl');
	}
} else {
	renderIfCurrentMenu();
	foreach (variable('sections') as $slug) {
		//if (cannot_access($slug)) continue;
		renderHeaderMenu($slug);
	}
}

function renderHeaderMenu($slug, $node = '') {
	$parentSlug = $node ? $node : $slug;

	if (contains($node, '/'))  { $bits = explode('/', $node); $name = humanize(array_pop($bits)) . ' (' . humanize(array_pop($bits)) . ')'; }
	else if ($node) { $name = humanize($node) . ' (' . humanize($slug) . ')'; }
	else { $name = humanize($parentSlug); }

	extract(variable('menu-settings'));
	if ($wrapTextInADiv) $name = '<div>' . $name . $topLevelAngle . '</div>';
	
	echo '<li class="' . $itemClass . ' ' . $subMenuClass . '"><a class="' . $anchorClass . '">' . $name . '</a>';

	if ($node) $slug .= '/' . $node;
	menu('/' . $slug . '/', [
		'a-class' => $anchorClass,
		'ul-class' => $ulClass . ($node ? ' of-node node-' . $node : ''),
		'list-only-folders' => $node == '',
		'home-link-to-section' => true,
		'parent-slug-for-home-link' => $parentSlug . '/',
		'parent-slug' => $node ? $node . '/' : '',
	]);
	echo '</li>' . variable('nl');
}

function renderIfCurrentMenu() {
	$items = getCurrentMenus();
	if (count($items) == 0) return;

	extract(variable('menu-settings'));
	echo '	<li class="' . $itemClass . '"><a class="' . $anchorClass . '" href="javascript: void();"><div>((Current Menu))</div></a>' . variable('nl');
	echo '		<ul' . cssClass([$ulClass]) . '>' . variable('nl');

	foreach ($items as $params) {
		renderHeaderMenu($params[0], $params[1]);
	}

	echo '		</ul>' . variable('nl');
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

if (function_exists('after_menu')) after_menu();
if (function_exists('network_after_menu')) network_after_menu();
if (!$noOuterUl) echo '</ul> <!-- #end site -->' .variable('2nl');
