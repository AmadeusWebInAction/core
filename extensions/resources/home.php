<?php
function renderNodeItem() {
	$node = variable('node');

	$topic = true ? replaceHtml('%page-location%') : humanize(variable('page_parameter1'));
	if (!$topic) return;

	contentBox($node . '-' . $topic, 'container');
	h2($topic);
	echo '<i>' . returnLine('---' . NEWLINE . variableOr('top-level-introduction', 'Intro Not Found')) . '</i>.';
	contentBox('end');
}

$nodeRoot = variable('node') == $section;
$level1 = variable('page_parameter1');
$level2 = variable('page_parameter2');
if ($nodeRoot) {
	if (function_exists('runNodeHome')) {
		runNodeHome();
	} else {
		contentBox($section, 'container');
		renderMarkdown(__DIR__ . '/home.md');
		contentBox('end');
	}
} else {
	if (function_exists('runNodePage')) {
		runNodePage();
	} else {
		renderNodeItem();
	}
}

$limit = -1;
$items = include(__DIR__ . '/menu.php'); //using include as easier than sending context.
$node = variable('node');
//print_r($items);
if ($section == 'general' && array_key_exists($node, $items)) {
	$level1 = $items[$node];

	$parent = explode('.', $level1);
	array_pop($parent);
	$parent = implode('.', $parent);

	$sheetFile = $where . '/_section.tsv';
	$bySlug = getSheet($sheetFile, 'parent');
	$subItems = [];

	foreach ($bySlug->group[$parent] as $item) {
		$slug = $bySlug->getValue($item, 'slug');
		//$name = $sheet->getValue($item, 'name');
		$name = humanize($slug);
		$subItems[$slug] = $sheet->getValue($item, 'sno') . '. ' . $name;
	}
	renderLevelMenu('subitems-list', $level1, $subItems, $node . '/');
}

renderLevelMenu('items-list', humanize($section), $items);

function renderLevelMenu($id, $title, $items, $parentSlug = false) {
	contentBox('section-menu', 'container box-like-list after-content mt-6');
	echo '<a href="javascript: void(0);" name="' . $id . '"></a>' . NEWLINE;
	h2('<u>' . $title . '</u> Menu');
	$limit = -1;
	//$items = include(__DIR__ . '/menu.php'); //using include as easier than sending context.
	menu('/', [
		'this-is-standalone-section' => true,
		'files' => $items,
		'ul-class' => 'striped-list white-list reset-list',
		'parent-slug' => $parentSlug,
	]);
	contentBox('end');
}