<?php
$of = variable('directory_of');
$section = variable('section');

sectionId('directory', 'text-center');
h2('Directory Of');
boxDiv('', 'toolbar', true);
echo 'Navigate: ' . variable('nl');
foreach (variable('sections') as $item) {
	//TODO: reinstate - if (cannot_access($item)) continue;
	echo sprintf(variable('nl') . '<a class="btn btn-%s" href="%s">%s</a> ',
		$item == $section ? 'primary' : 'secondary',
		variable('url') . $item . '/',
		humanize($item)
	);
}
boxDiv('end');

$folder = SITEPATH . '/' . $of . '/';
if (disk_file_exists($home = $folder . 'home.md')) {
	boxDiv('home');
	renderFile($home);
	boxDiv('end');

	boxDiv('nodes');
	runFeature('tables');
	add_table('nodes-table', $folder . '_nodes.tsv', 'name, about, tags',
		'<tr><td><a href="%url%%name_urlized%/">%name_humanized%</a></td><td>%about%</td><td>%tags%</td></tr>');
	boxDiv('end');
}

echo '</section>' . variable('2nl');
