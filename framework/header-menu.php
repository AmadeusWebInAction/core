<?php
extract(variable('menu-settings'));

if (!$noOuterUl) echo '<ul class="' . $ulClass . '">';

if (isset($diagonalSpacer)) echo $diagonalSpacer; //for anbagam!

$mainMenu = variable('siteMenuName') . $topLevelAngle;
if ($wrapTextInADiv) $mainMenu = '<div>' . $mainMenu . '</div>';

echo '<li class="' . (variable('theme') == 'biz-land' ? '' : $itemClass) . '"><a class="' . $anchorClass . '" href="' . variable('url') . '">Home</a></li>';

echo '<li class="' . $itemClass . '"><a class="' . $anchorClass . '" href="javascript: void();">' . $mainMenu . '</a>';
$append = variable('scaffold') ? array_merge(['----'], variable('scaffold')) : false;
menu('/' . variable('folder'), [
	'home-link-to-section' => variable('home-link-to-section'),
	'files-to-append' => $append,
]);
echo '</li>' . variable('nl');

if ($groups = variable('section-groups')) {
	if (!isset($groupOuterUlClass)) $groupOuterUlClass = $ulClass;
	foreach ($groups as $group => $items) {
		$isGroup = true;
		if (is_string($items)) {
			$group = $items;
			$items = [$items];
			$isGroup = false;
		}

		$name = humanize($group);
		if ($wrapTextInADiv) $name = '<div>' . $name . $topLevelAngle . '</div>';

		if ($isGroup) echo '<li class="' . $itemClass . '"><a class="' . $anchorClass . '">' . $name . '</a>';
		if ($isGroup) echo '<ul class="' . $groupOuterUlClass . '">';

		foreach ($items as $slug) {
			//if (cannot_access($slug)) continue;
			renderHeaderMenu($slug);
		}

		if ($isGroup) echo '</li>';
		if ($isGroup) echo '</ul>' . variable('nl');
	}
	renderCurrentNodeMenu();
} else {
	foreach (variable('sections') as $slug) {
		//if (cannot_access($slug)) continue;
		renderHeaderMenu($slug);
	}
	renderCurrentNodeMenu();
}

function renderHeaderMenu($slug, $node = '') {
	$name = humanize($slug);

	extract(variable('menu-settings'));
	if ($wrapTextInADiv) $name = '<div>' . $name . $topLevelAngle . '</div>';
	$parent = array_search($slug, ['gallery']) !== false ? $slug . '/' : '';
	
	echo '<li class="' . $itemClass . '"><a class="' . $anchorClass . '">' . $name . '</a>';
	if ($slug)
	menu('/' . $slug . $node . '/', [
		'a-class' => $anchorClass,
		'list-only-folders' => true,
		'home-link-to-section' => true,
		'parent-slug-for-home-link' => $slug . '/',
	]);
	echo '</li>' . variable('nl');
}

//TODO: HIGH: cleanup and move to menu.php
function renderCurrentNodeMenu() {
	//TODO: if (cannot_access(variable('section'))) return;

	$folRelative = '/' . variable('section') . (variable('section') != variable('node') ? '/' . variable('node') : '') . '/';
	$folAbsolute = variable('path') . $folRelative;

	if (!disk_is_dir($folAbsolute)) return;
	renderHeaderMenu(variable('section'), '/' . variable('node'));
}

if (function_exists('after_menu')) after_menu();
if (function_exists('network_after_menu')) network_after_menu();
if (!$noOuterUl) echo '</ul> <!-- #end site -->' .variable('2nl');
