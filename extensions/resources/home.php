<?php
contentBox('listings', 'container');
$nodeRoot = variable('node') == variable('section');
$level1 = variable('page_parameter1');
$level2 = variable('page_parameter2');
if ($nodeRoot) {
	renderMarkdown(__DIR__ . '/home.md');
} else {
	renderMarkdown(__DIR__ . '/dummy-items.md');
}
contentBox('end');

contentBox('section-menu', 'container after-content mt-6');
h2('<u>' . humanize(variable('section')) . '</u> Menu');
$items = include(__DIR__ . '/menu.php'); //using include as easier than sending context.
menu('/', [
	'this-is-standalone-section' => true,
	'files' => $items,
]);
contentBox('end');
