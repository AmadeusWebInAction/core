<?php
variables([
	'special-folder-extensions' => $sfe = [
		'articles' => 'md',
		'in-memoriam' => 'md',
		'blurbs' => 'txt',
		'code' => 'php',
		'decks' => 'md',
		'dossiers' => 'tsv',
		'rich-pages' => 'tsv',
		'tables' => 'md',
	],
	'exclude-folders' => ['assets', 'data', 'engage', 'home', 'images', 'thumbnails'],
]);

function autoRender($file) {
	if (endsWith($file, '.php')) {
		renderAnyFile($file);
		pageMenu($file);
		return;
	}

	$raw = disk_file_exists($file) ? disk_file_get_contents($file) : '[RAW]';
	$embed = hasPageParameter('embed');
	$pageName = title('params-only');

	if (startsWith($raw, '|is-engage')) {
		if (!endsWith($file, '.tsv'))
			parameterError('ENGAGE SUPPORTS ONLY TSV IN BETA', $file, DOTRACE, DODIE);

		runFeature('engage');

		sectionId('special-form', 'container');
		_runEngageFromSheet(getPageName(), $file);
		section('end');

		pageMenu($file);
		return;
	}

	if (endsWith($file, '.md')) {
		sectionId('special-md', 'container');
		if (startsWith($raw, '<!--is-blurbs-->'))
			_renderedBlurbs($file);
		else if (startsWith($raw, '<!--is-deck-->'))
			_renderedDeck($file, $pageName);
		else
			renderAny($file, ['use-content-box' => true, 'heading' => $pageName]);

		section('end');
		pageMenu($file);
		return;
	}

	if (endsWith($file, '.tsv')) {
		if (!$embed) sectionId('special-table', 'container content-box');

		runFeature('tables');

		if (startsWith($raw, '|is-deck'))
			renderSheetAsDeck($file, variableOr('all_page_parameters', variable('node')) . '/');
		else if (startsWith($raw, '|is-rich-page'))
			renderRichPage($file);
		else if (startsWith($raw, '|is-table'))
			add_table(pathinfo($file, PATHINFO_FILENAME), $file, 'auto', disk_file_get_contents(dirname($file) . '/.template.html'));
		else
			parameterError('unsupported tsv file - see line 1 for type definition', $file);

		if (!$embed) section('end');
		pageMenu($file);
		return;
	}

	sectionId('file', 'container content-box');
	renderAny($file);
	section('end');
	pageMenu($file);
}

function pageMenu($file) {
	if (!(variable('section'))) return;
	$folder = dirname($file) . '/';
	$subPage = variable('page_parameter1');
	$subSubPage = variable('page_parameter2');
	if (!disk_file_exists($tsv = $folder . ($subPage ? '_subpages.tsv' : '_pages.tsv'))) {
		$params = str_replace(SITEPATH, '', $folder);
		contentBox('pages-menu', 'block-links container after-content'); //TODO: read meta and build table - do that if home.md has meta, expect for all
		$parentSlug = variable('all_page_parameters');
		//expects upto _subpages upto here
		if (variable('page_parameter3')) {
			$bits = explode('/', $parentSlug);
			array_pop($bits);
			$parentSlug = implode('/', $bits);
		}
		h2('Sub Site: ' . humanize(variable('page_parameter' . ($subSubPage ? '2' : '1'))));
		menu($params, ['parent-slug' => $parentSlug . '/', 'home-link-to-section' => true, 'ul-class' => 'block-links']);
		contentBox('end');
		return;
	}

	contentBox('pages', 'container after-content');
	h2($subPage ? 'Sub Site: ' . humanize($subPage) : 'Site: ' . humanize(variable('node')));
	runFeature('tables');

	$baseUrl = 'node-url';
	if ($subSubPage) $baseUrl = 'sub-page-url';
	else if ($subPage) $baseUrl = 'page-url';

	add_table('pages-table', $tsv, ($subPage ? 'sub-' : '') . 'page-name, about, tags',
		'<tr><td><a href="%' . $baseUrl . '%%name_urlized%">%name_humanized%</a></td><td>%about%</td><td>%tags%</td></tr>');
	contentBox('end');
}

function renderedSpecial() {
	if (variable('site-lock')) { doSiteLock(); return true; }
	$node = variable('node');
	if ($node == 'gallery') { includeFeature('gallery'); return true; }
	if (_renderedLink($node)) return true;
	if (_renderedScaffold($node)) return true;

	$special = variable('special-folder');
	if (!$special) return false;

	$file = variable('file');

	if ($special == 'blurbs') {
		_renderedBlurbs($file);
	} else if ($special == 'code') {
		_renderedCode($file);
	} else if ($special == 'decks') {
		_renderedDeck($file);
	} else if ($special == 'dossiers') {
		_renderedDossiers($file);
	} else if ($special == 'rich-pages') {
		variable('home', getSheet($file));
		renderThemeFile('home');
	}

	return true;
}

// ************************************ Region: Private (Internal) Functions

function _setupBlurbs($fwe, $page) {
	$blurb = $fwe . '.txt';
	variable('blurb-file', $blurb);
	if (hasPageParameter('embed'))
		variable('embed', true);
}

function _renderedBlurbs($blurb, $name = false) {
	if (!$name) $name = variable('special-filename');

	if (hasPageParameter('embed')) {
		includeFeature('blurbs');
		return;
	}

	$url = currentUrl();
	$embedUrl = $url . '?embed=1';
	echo '<section class="blurb-container" style="text-align: center;">BLURBS: '
		. makeLink($name, $embedUrl, false) . ' (opens in new tab)<hr />' . variable('nl');
	echo '<iframe style="height: 80vh; width: 100%; border-radius: 30px;" src="' . $embedUrl . '"></iframe>' . variable('nl');
	echo '</section>' . variable('2nl');
}

function _setupCode($fwe, $name) {
	variable('file', $fwe . '.php');
}

function _renderedCode($code) {
	disk_include_once($code);
}

function _setupDeck($fwe, $name) {
	if (hasPageParameter('expanded')) return false;

	$file = $fwe . '.md';

	variable('deck-name', $name);
	variable('file', $file);

	if (!hasPageParameter('embed')) {
		return false;
	}

	variable('no-permanent-link', true);
	variable('no-detail-link', true);
	variable('embed', true);
}

function renderInPageDeck($section, $node, $name) {
	$deck = concatSlugs([variable('path'), $section, $node, 'decks', $name . '.md']);
	$params = [ 'relativeUrl' => concatSlugs([$node, $name, '']),
		'title' => humanize($node) . ' &raquo; ' . $name]; //todo - bring to 7.1 convention
	_renderedDeck($deck, $params);
}

function renderSheetAsDeck($deck, $link) {
	$title = title('params-only');
	if (!hasPageParameter('embed') && !hasPageParameter('expanded')) {
		_renderedDeck($deck, $title);
		return;
	}

	$sheet = getSheet($deck, false);
	$op = [];
	foreach ($sheet->rows as $item) {
		$type = $item[$sheet->columns['type']];
		$text = $item[$sheet->columns['text']];
	
		if ($type == 'slide') {
			if (count($op)) { $op[] = ''; $op[] = '----'; $op[] = ''; }
			$op[] = '<input type="hidden" value="' . $text . '" />';
			$op[] = '';
		} else if ($type == 'heading') {
			$op[] = '## ' . $text;
			$op[] = '';
		} else if ($type == 'sub-heading') {
			$op[] = '### ' . $text;
			$op[] = '';
		} else if ($type == 'paragraph') {
			$op[] = $text;
			$op[] = '';
		} else if ($type == 'item') {
			if (end($op) != '') $op[] = '';
			$op[] = '* ' . $text;
		}
	}

	variable('nodeLink', $link);
	$op = implode(variable('nl'), $op);
	_renderedDeck($op, $title);
}

function _renderedDeck($deck, $title) {
	function __parseDeck($deck) {
		if (endsWith($deck, '.md'))
			$deck = renderMarkdown($deck, [ 'echo' => false ]);
		return $deck;
	}

	if (hasPageParameter('embed')) {
		$deck = __parseDeck($deck);
		variable('deck', $deck);
		runModule('revealjs');
		return true;
	}

	$expanded = hasPageParameter('expanded');
	$url = currentUrl();

	$embedUrl = $url .'?embed=1';

	sectionId('deck-toolbar', 'text-center');
	h2($title . currentLevel(), 'amadeus-icon', true);
	contentBox('deck', 'toolbar');
	echo 'PRESENTATION: ' . variable('nl');
	$links = [];

	//TODO: UI FIX: if (!$expanded) $links[] = '<a class="toggle-deck-fullscreen" href="javascript: $(\'.deck-container\').show();"><span class="text">maximize</span> ' . getIconSpan('expand', 'normal') . '</a>';
	if ($expanded) $links[] = makeLink('open deck page', $url, false);
	$links[] = makeLink('open deck fully', $embedUrl, false);
	$links[] = $expanded ? 'expanded deck below' : makeLink('open deck expanded', $url . '?expanded=1', false);
	//TODO: get this working and support multi decks
	//$(this).closest(\'.deck-toolbar\').next(\'.deck-container\').toggle();
	if (!$expanded) $links[] = makeLink('toggle deck below', 'javascript: $(\'.deck-container\').toggle();', false);

	echo implode(' &nbsp;&nbsp;&mdash;&nbsp;&nbsp; ' . variable('nl'), $links);
	contentBox('end');
	section('end');

	if ($expanded) {
		$deck = __parseDeck($deck);
		$deck = cbWrapAndReplaceHr($deck); //in revealjs we will use plain sections
		echo $deck;
	} else {
		echo sprintf('<section class="deck-container">'
			. '<iframe src="%s&iframe=1"></iframe></section>', $embedUrl);
		addScript('presentation-toolbar', 'app-static--common-assets');
	}
}

function _setupDossiers($fwe, $name) {
	$data = $fwe . '.tsv';
	if (!disk_file_exists($data)) return false;

	$data = dirname($fwe) . '/' . $name . '.tsv';

	$folder = SITEPATH . '/data/dossier-templates/';
	$node = variable('node');

	$templates = [
		'node-item' => $folder . $node . '-' . $name . '.html',
		'node' => $folder . $node . '.html',
		'default' => $folder . 'default.html',
	];

	foreach($templates as $type => $item) {
		if (disk_file_exists($item)) {
			variables([
				'file' => $data,
				'template' => $item,
				'template-type' => $type,
			]);
			return true;
		};
	}

	parameterError('Dossier Template Resolver', ['found-data' => $data, 'searched-templates' => $templates], false);
	die(); //this is before render and violating the contract with the isSpecial which calls it

	return false;
}

function _renderedDossiers($data) {
	$page = variable('special-filename-websafe');
	$html = variable('template');
	$type = variable('template-type');

	sectionId($page . '-intro', 'feature-table'); //NOTE: dbc heads up: section nesting will be a problem when using html in dossiers!
	h2('Dossiers or Records');

	//later this can be resolved from multiple filenames as needed
	echo replaceItems(getSnippet('dossier'), [
		'pageName' => humanize($page),
		'nodeName' => humanize(variable('node')),
		'sectionName' => humanize(variable('section')),
		'siteName' => variable('name'),
	], '%');

	section('end');

	add_table($page, false, $data, 'auto', disk_file_get_contents($html));
}

//TODO: Refactor and move away? or make it a kind of special section
function did_wiki_topic_humanize($txt, $field, $sheetName = 'wiki') {
	$sheetName = trim($sheetName);
	$txt = str_replace(' ', '-', strtolower($txt));
	$sheet = getSheet($sheetName, 'slug'); //NOTE: Its cached automatically by the framework

	if (isset($sheet->group[$txt]))
		return $sheet->group[$txt][0][$sheet->columns['no']] . ' &mdash; ' . humanize($txt, 'no-site');
	else
		return false;
}

function wiki_pages_after_file($sheetName = 'wiki', $wikiSlug = 'wiki') {
	$sheetName = trim($sheetName);
	$sheet = getSheet($sheetName, 'slug');
	$page = variable('page_parameter1');
	if (!$page || !isset($sheet->group[$page])) return;

	$row = $sheet->group[$page][0];
	$no = $row[$sheet->columns['no']];

	$sheet = getSheet($sheetName, 'parent');
	$items = $sheet->group[$no];

	foreach ($items as $item) {
		$content = $item[$sheet->columns['has_content']];
		$itemNo = $item[$sheet->columns['no']];
		$itemSlug = $item[$sheet->columns['slug']];

		$html = renderMarkdown($content == 'N'
			? SITEPATH . '/' . $wikiSlug . '/' . variable('node') . '/_pages/' . $itemSlug . '.md'
			: $item[$sheet->columns['content']], ['echo' => false, 'strip-paragraph-tag'=> true]);

		$html = str_replace('|',variable('brnl'), $html);

		section('end');
		section();

		$heading = str_pad('#', substr_count($itemNo, '.') + 1);
		echo renderMarkdown($heading . ' ' . $itemNo . ' &mdash; ' . humanize($itemSlug, 'no-site'));
		echo $html;
	}
}

function _isLinks($node) {
	if ($node == 'go')
	includeFeature('links'); //will just do a redirect

	return $node == 'links';
}

function _renderedLink($node) {
	if ($node != 'links') return false;

	includeFeature('links'); //will list them
	return true;
}

function before_section_or_file($section) {
	$node = variable('node');

	if ($node == $section) {
		variable('section', $section);
		return true;
	}

	$fol = SITEPATH . '/' . $section . '/';
	$files = disk_scandir($fol);

	foreach ($files as $fil) {
		if ($fil[0] == '.') continue;
		if ($node == $fil) {
			variable('fwk-section', $section);
			return true;
		} else if (disk_is_dir($fol . $node . '/')) {
			variable('fwk-section', $section);
			variable('fwk-folder', $section . '/' . $node . '/');
			return true;
		} else if ($ext = disk_one_of_files_exist($fwe = $fol . $fil . '.','txt, md')) {
			variable('fwk-section', $section);
			variable('fwk-file', $fwe . $ext);
			return true;
		}
	}

	return false;
}

function did_render_section_or_file() {
	$section = variable('fwk-section');
	$dir = variable('fwk-folder');
	$file = variable('fwk-file');

	if ($file) {
		renderAny($file);
		return true;
	} else if ($section || $dir) {
		includeFeature('blog'); //TODO: merge this with directory and use section type if not blog/wiki/sitemap
		return true;
	}

	return false;
}

function _isScaffold() {
	$node = variable('node');
	$scaffold = variableOr('scaffold', []);
	//NOTE: sitemap always needed
	$always = variable('local') && $node == 'sitemap';
	if (!$always && !in_array($node, $scaffold))
		return false;

	if (hasPageParameter('embed')) variable('embed', true);
	variable('scaffoldCode', 'scaffold/' . $node);
	return true;
}

function _renderedScaffold() {
	$code = variable('scaffoldCode');
	if (!$code) return false;

	includeFeature($code, false);
	return true;
}

//scaffolded features
function do_updates() {
	if (!sheetExists('updates') || variable('no-updates')) return;

	includeFeature('updates');
}
