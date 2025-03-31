<?php
function renderNodeItem($path) {
	$node = variable('node');
	$data = $path . '/data/' . $node . '.tsv';

	$topic = true ? replaceHtml('%page-location%') : humanize(variable('page_parameter1'));
	if ($topic) {
		contentBox($node . '-' . $topic, 'container');
		h2($topic);
		renderMarkdown(__DIR__ . '/dummy-items.md');
		contentBox('end');
	}

	//from directory!
	contentBox('nodes', 'container after-content');
	h2(humanize($node));
	runFeature('tables');
	add_table('sections-table', $data, true ? 'resource-topic' : 'site-name, about, tags',
		'<tr><td><a href="%url%' . $node . '/%name_urlized%">%sno% &mdash; %name_humanized%</a></td><!--td>%about%</td><td>%tags%</td--></tr>');
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
		contentBox($section, 'container');
		renderMarkdown(__DIR__ . '/dummy-items.md');
		contentBox('end');
	}
}

contentBox('section-menu', 'container box-like-list after-content mt-6');
h2('<u>' . humanize($section) . '</u> Menu');
$limit = -1;
$items = include(__DIR__ . '/menu.php'); //using include as easier than sending context.
menu('/', [
	'this-is-standalone-section' => true,
	'files' => $items,
	'ul-class' => 'striped-list white-list reset-list',
]);
contentBox('end');
