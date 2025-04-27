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
	$nodeItems = false;
	$sectionItems = [];
	if (!disk_file_exists($folder . '_nodes.tsv')) {
		$nodes = _skipNodeFiles(disk_scandir($folder));
		variable('seo-handled', false);
		//print_r($nodes);
		foreach ($nodes as $fol) {
			$home = $folder . $fol . '/home.md';
			$about = 'No About Set';
			$tags = 'No Tags Set';
			if (disk_file_exists($home)) {
				$meta = read_seo($home);
				if ($meta && $meta['about'])
					$about = $meta['about'];
				else if ($meta && $meta['description'])
					$about = $meta['description'];

				if ($meta && $meta['keywords'])
					$tags = $meta['keywords'];
			}

			$sectionItems[] = [
				'site' => '#unused',
				'name_urlized' => $fol,
				'about' => $about,
				'tags' => $tags
			];
		}
	}
	runFeature('tables');
	add_table('sections-table', $sectionItems ? $sectionItems : ($folder . '_nodes.tsv'),
		$sectionItems ? ['site, about, tags', 'name_urlized'] : 'site-name, about, tags',
		'<tr><td><a href="%url%%name_urlized%">%name_humanized%</a></td><td>%about%</td><td>%tags%</td></tr>');
	contentBox('end');
}

section('end');
