<?php
$of = variable('directory_of');
$section = variable('section');

sectionId('directory', 'container');
function _sections($current) {
	contentBox('', 'toolbar text-align-left');
	echo 'Section: ' . variable('nl');
	foreach (variable('sections') as $item) {
		//TODO: reinstate - if (cannot_access($item)) continue;
		echo sprintf(variable('nl') . '<a class="btn btn-%s" href="%s">%s</a> ',
			$item == $current ? 'primary' : 'secondary',
			pageUrl($item),
			humanize($item)
		);
	}
	contentBox('end');
}

$folder = SITEPATH . '/' . $of . '/';
if (disk_file_exists($home = $folder . 'home.md')) {
	h2(humanize($section) . currentLevel(), 'amadeus-icon');
	contentBox('home');
	renderFile($home);
	contentBox('end');

	contentBox('nodes', 'after-content');
	_sections($section);
	runFeature('tables');
	add_table('sections-table', $folder . '_nodes.tsv', 'section-name, about, tags',
		'<tr><td><a href="%url%%name_urlized%">%name_humanized%</a></td><td>%about%</td><td>%tags%</td></tr>');
	contentBox('end');
}

section('end');
