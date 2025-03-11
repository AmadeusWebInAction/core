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
	
	echo '<li class="' . $itemClass . ' ' . $subMenuClass . '"><a class="' . $anchorClass . '">' . $name . '</a>';
	menu('/' . $slug . $node . '/', [
		'a-class' => $anchorClass,
		'ul-class' => $ulClass,
		'list-only-folders' => true,
		'home-link-to-section' => true,
		'parent-slug-for-home-link' => $slug . '/',
	]);
	echo '</li>' . variable('nl');
}

//TODO: HIGH: cleanup and move to menu.php
function renderCurrentNodeMenu() {
	return; //TODO: bug!
	//TODO: if (cannot_access(variable('section'))) return;

	$folRelative = '/' . variable('section') . (variable('section') != variable('node') ? '/' . variable('node') : '') . '/';
	$folAbsolute = variable('path') . $folRelative;

	if (!disk_is_dir($folAbsolute)) return;
	renderHeaderMenu(variable('section'), '/' . variable('node'));
}

if (function_exists('after_menu')) after_menu();
if (function_exists('network_after_menu')) network_after_menu();
if (!$noOuterUl) echo '</ul> <!-- #end site -->' .variable('2nl');
