<?php
$where = variableOr('directory_of', variable('section'));
variable('omit-long-keywords', true);

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

$folder = SITEPATH . '/' . $where . '/';

if (disk_file_exists($home = $folder . 'home.md')) {
	if (!variable('in-node'))
		h2(humanize($where) . currentLevel(), 'amadeus-icon');

	if (variable('node') != 'index' && !variable('in-node')) {
		contentBox('home');
		renderFile($home);
		contentBox('end');
	}

	$breadcrumbs = variable('breadcrumbs');

	echo GOOGLEOFF;
	contentBox('nodes', 'after-content');

	if (!$breadcrumbs)
		_sections($where);

	variable('seo-handled', false);


	if ($breadcrumbs || variable('in-node')) {
		//TODO: develop asap!
		$sectionItems = [];
	} else {
		$sectionItems = [getFolderMeta($folder, false, $where)];
	}

	$files = disk_scandir($folder);
	natsort($files);
	$nodes = _skipNodeFiles($files);

	foreach ($nodes as $fol) {
		$sectionItems[] = getFolderMeta($folder, $fol);
	}

	$relativeUrl = $breadcrumbs ? variable('node') . '/' . implode($breadcrumbs) . '/' : '';
	runFeature('tables');

	add_table('sections-table', $sectionItems,
		$sectionItems ? ['site, about, tags', 'name_urlized'] : 'site-name, about, tags',
		'<tr><td><a href="%url%' . $relativeUrl . '%name_urlized%">%name_humanized%</a></td><td>%about%</td><td>%tags%</td></tr>');
	contentBox('end');

	echo GOOGLEON;
}

section('end');
