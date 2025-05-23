<?php
function read_seo($file = false) {
	if (variable('seo-handled') && !$file) return;

	$fileGiven = !!$file;
	if (!$file) $file = variable('file');
	if ($file && endsWith($file, '.md')) {
		$raw = disk_file_get_contents($file);
		$meta = parseMeta($raw);
		if (!$meta) return;

		$aboutFields = ['About', 'about'];
		$descriptionFields = ['Description', 'description'];

		if (variable('omit-long-keywords'))
			$keywordsFields = ['Primary Keyword', 'Related Keywords', 'Keywords', 'keywords'];
		else
			$keywordsFields = ['Primary Keyword', 'Related Keywords', 'Long-Tail Keywords', 'Localized Keywords', 'Keywords', 'keywords'];

		$about = false;
		$description = false; //if meta exists, this is mandatory (but only single)
		$customTitle = false;
		$keywords = []; //can be multiple

		foreach ($meta as $key => $value) {
			if (contains($value, '%siteName%'))
				$value = replaceItems($value, ['siteName' => variable('name')], '%');

			if (in_array($key, $aboutFields)) {
				$about = $value;
			} else if (in_array($key, $descriptionFields)) {
				$description = $value;
			} else if (in_array($key, $keywordsFields)) {
				$keywords[] = $value;
			} else if ($key == SINGLEFILECONTENT) {
				variable(SINGLEFILECONTENT, $value);
			} else if ($key == 'Custom Title') {
				$customTitle = $value;
			}
		}

		$keywords = count($keywords) ? implode(', ', $keywords) : '';

		if ($fileGiven) return compact('about', 'description', 'keywords', 'meta');

		if ($description) {
			variable('description', $description);
			variable('og:description', $description);
			if ($customTitle) variable('custom-title', $customTitle);
			variable('keywords', $keywords);
			variable('seo-handled', true);
			variable('meta_' . $file, $meta);
			//TODO: do we need to consume singlefilecontent in render? I think not
		}
	}
}

function print_seo() {
	if (variable('meta-rendered')) return;
	$file = variable('file');
	if (!$file || !endsWith($file, '.md')) return;

	$meta = variable('meta_' . $file);
	if (!$meta) return;

	$show = ['About', 'Description', 'Primary Keyword', 'Date', 'Prompted By', 'Meta Author', 'Page Author', 'Related Keywords', 'Long-Tail Keywords'];
	$info = [];

	foreach ($show as $col) {
		if (!isset($meta[$col])) return;
		$val = $meta[$col];
		$info[$col] = $val;
	}

	contentBox('meta', 'container');
	h2('About This Page / SEO Information');
	runFeature('tables');
	_tableHeadingsOnLeft(['id' => 'piece'], $info);
	contentBox('end');
}

function getFolderMeta($folder, $fol, $folName = false) {
	$home = $folder . ($fol ? $fol . '/' : ''). 'home.md';
	$about = 'No About Set';
	$tags = 'No Tags Set';

	if (disk_file_exists($home)) {
		$vars = read_seo($home);

		if ($vars) {
			if (isset($vars['about']))
				$about = pipeToBR($vars['about']);
			else if (isset($vars['description']))
				$about = pipeToBR($meta['description']);

			if (isset($vars['keywords']))
				$tags = csvToHashtags($vars['keywords']);
		}
	}

	return [
		'site' => '#unused',
		'name_urlized' => $folName ? $folName : $fol,
		'about' => $about,
		'tags' => $tags
	];

}

function seo_info() {
	$item = variable('current_page');
	if (!$item) return;

	echo '<section id="seo-info" class="container" style="padding-top: 30px;">' . NEWLINE;
	echo featureHeading('seo');

	$fmt = '<p><h4>%s</h4>%s</p>' . NEWLINE;

	$cols = ['about', 'description', 'keywords'];
	foreach ($cols as $col) {
		$field = isset($item[$col]) ? $item[$col] : false;
		if ($field) echo sprintf($fmt, ($col != 'about' ? 'SEO ' : '') . humanize($col), $field);
	}

	echo NEWLINE . '</section>' . NEWLINE;
}

function seo_tags($return = false) {
	$fmt = '	<meta name="%s" content="%s">';
	$ogFmt = '	<meta property="%s" content="%s">';

	variable('generator', 'Amadeus Web Builder / CMS at amadeusweb.com');
	$op = [];

	foreach (['generator', 'description', 'keywords', 'og:image', 'og:title', 'og:description', 'og:keywords', 'og:url', 'og:type', 'fb:app_id'] as $key)
		if ($val = variable($key)) $op[] = sprintf(startsWith($key, 'og:') || startsWith($key, 'fb:') ? $ogFmt : $fmt, $key, replaceVariables($val));

	$op = implode(NEWLINE, $op);
	if ($return) return $op;
	echo $op;
}
