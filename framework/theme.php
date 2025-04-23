<?php
function getThemeTemplate($end = '-rich-page.php') {
	return getThemeFile(variable('sub-theme') . $end);
}

function getTemplateFrom($file) {
	$file = getThemeFile($file . '.html');
	$bits = explode('##content##', disk_file_get_contents($file));
	return ['header' => $bits[0], 'footer' => $bits[1]];
}

function getThemeBlock($name, $location = false) {
	$file = getThemeFile('blocks/' . $name . '.html', $location);
	$bits = explode('<!--part-separator-->', disk_file_get_contents($file));
	return ['start' => $bits[0], 'item' => $bits[1], 'end' => $bits[2]];
}

function getThemeSnippet($name, $location = false) {
	$file = getThemeFile('snippets/' . $name . '.html', $location);
	$html = renderAny($file, ['echo' => false, 'strip-paragraph-tag' => true]);
	$vars = [
		'##theme##' => getThemeBaseUrl(),
		'<br />' => '',
	];
	return NEWLINES2 . replaceItems($html, $vars) . NEWLINE;
}

function run_theme_part($what) {

	if (!($content = variable('theme-template'))) {
		$file = getThemeFile(variable('sub-theme') . '.html');
		$bits = explode('##content##', disk_file_get_contents($file));
		$content = ['header' => $bits[0], 'footer' => $bits[1]];
		$content['footer-widgets'] = disk_file_get_contents(getThemeFile('footer-widgets' . variableOr('footer-variation', '') . '.html'));
		variable('theme-template', $content);
	}

	$vars = [
		'theme' => getThemeBaseUrl(), //TODO: /version can be maintained on the individual file?
		'optional-slider' => '', //this could be a page title too
		'optional-right-button' => '',
		'header-align' => '', //an addon class needed if video page title has an image and wants content on right
		'search-url' => searchUrl(contains(variable('node'), 'search')),
		'app-static' => assetMeta('app-static')['location'],
	];

	if ($what == 'header') {
		$icon = replaceItems('<link rel="icon" href="%url%%safeName%-icon.png%version%" sizes="192x192">',
			['url' => variableOr('node-static', fileUrl()), 'safeName' => variableOr('nodeSafeName', variable('safeName')),
				'version' => assetMeta('site', 'version')], '%'); //TODO: simplify this version stuff?

		$vars['head-includes'] = '<title>' . title(true) . '</title>' . NEWLINE . '	' . $icon . NEWLINE . main::runAndReturn();
		$vars['seo'] = seo_tags(true);
		$vars['body-classes'] = body_classes(true);

		//TODO: icon link to node home, should have 2nd menu & back to home
		$baseUrl = hasVariable('nodeSafeName') ? pageUrl(variable('node')) : pageUrl();
		$logo2x = siteOrNetworkOrAppStatic(variableOr('nodeSafeName', variable('safeName')) . '-logo@2x.png');
		$vars['logo'] = concatSlugs(['<a href="', $baseUrl, '"><img src="', $logo2x, '" class="img-fluid img-max-',
			variableOr('footer-logo-max-width', '500'), '" alt="', variableOr('nodeSiteName', variable('name')), '"></a><br>'], '');

		$header = _substituteThemeVars($content, 'header', $vars);

		$bits = explode('##menu##', $header);

		echo _renderRaw($bits[0]);
		if (isset($bits[1])) {
			setMenuSettings();
			runFrameworkFile('header-menu');
			headerMenuFrom();
			echo _renderRaw($bits[1]);
		}
		if (variable('submenu-at-node')) {
			$menuFile = getThemeFile('page-menu.html');
			$menuContent = disk_file_get_contents($menuFile);

			$menuVars = [
				'menu-title' => variable('nodeSiteName'),
			];
			$menuContent = replaceItems($menuContent, $menuVars, '##');

			$menuBits = explode('##page-menu##', $menuContent);
			echo _renderRaw($menuBits[0]);
			setMenuSettings('page-menu');
			header2ndMenu();
			echo _renderRaw($menuBits[1]);
		}
		setMenuSettings(true);
	} else if ($what == 'footer') {
		if (!variable('footer-widgets-in-enrich')) {
			$logo2x = siteOrNetworkOrAppStatic(variable('safeName') . '-logo@2x.png', true);
			$logo = concatSlugs(['<a href="', pageUrl(), '"><img src="', $logo2x, '" style="border-radius: 20px;" class="img-fluid" alt="', variable('name'), '"></a><br>'], '');

			$message = !variable('footer-message') ? '' : '<span class="footer-message">' . renderSingleLineMarkdown(variable('footer-message'), ['echo' => false]) . '</span>' . NEWLINE;
			$loneMessage = contains($content['footer-widgets'], '##footer-message##');

			$contact = getSnippet('contact');
			$loneContact = contains($content['footer-widgets'], '##footer-contact##');

			$nodeName = hasVariable('nodeSiteName') ? '<span class="h5" style="margin-left: 15px;">&#10148; ' . variable('nodeSiteName') . '</span>' . NEWLINE : '';
			$fwVars = [
				'footer-logo' => $logo . '<h4 class="mt-sm-4">' . variable('name') . $nodeName . '</h4>'
					. (!$loneMessage ? $message : '') . (!$loneContact ? BRNL . BRNL . $contact : ''),
				'site-widgets' => siteWidgets(),
				'copyright' => _copyright(true),
				'credits' => _credits('', true),
			];

			if ($loneMessage) $fwVars['footer-message'] = '<h2 class="text-align-center p-3">' . $message . '</h2>';
			if ($loneContact) $fwVars['footer-contact'] = '<hr>' . $contact;

			$vars['footer-widgets'] = _substituteThemeVars($content, 'footer-widgets', $fwVars);
		}

		$footer = _substituteThemeVars($content, 'footer', $vars);

		$atBody = !contains($footer, '##footer-includes##');
		$bits = explode($atBody ? '</body>' : '##footer-includes##', $footer);

		if ($after = variable('after-wrapper')) {
			if (!contains($bits[0], $sep = '<!-- #wrapper end -->'))
				peDie('expected template to have a wrapper close comment!', $after);

			$wabbits = explode($sep, $bits[0]);
			echo _renderRaw($wabbits[0]);
			$tpl = getTemplateFrom($after['template']);
			echo _renderRaw($tpl['header']);
			autoRender($after['file']);
			echo _renderRaw($tpl['footer']);
			echo $sep . _renderRaw($wabbits[1]);
		} else {
			echo _renderRaw($bits[0]);
		}

		print_stats(); //returns if not needed
		styles_and_scripts();
		if (function_exists('after_footer_assets')) after_footer_assets();

		if ($atBody) echo '</body>';
		echo _renderRaw($bits[1]);
	}
}

function _substituteThemeVars($content, $what, $vars) {
	if (function_exists('enrichThemeVars'))
		$vars = enrichThemeVars($vars, $what);

	if ($what == 'header') {
		//if ($vars['optional-slider'] == '')
			$vars['body-classes'] = $vars['body-classes'] . ' no-slider';
	}
	return replaceItems($content[$what], $vars, '##');
}

function _renderRaw($html) {
	return renderAny($html, ['raw' => true, 'echo' => false]);
}

function setMenuSettings($after = false) {
	if ($after === true) {
		variable('menu-settings', false);
		return;
	}

	$pm = $after == 'page-menu';
	$prefix = $pm ? 'page-' : '';
	//same as non-profit header
	variable('menu-settings', [
		'isPageMenu' => $pm,
		'noOuterUl' => false,
		'groupOuterUlClass' => $prefix . 'menu-container',
		'outerUlClass' => 'menu-container',
		'ulClass' => $pm ? 'page-menu-sub-menu' : 'sub-menu-container',
		'itemClass' => $prefix . 'menu-item',
		'subMenuClass' => $pm ? 'page-menu-sub-menu' : 'sub-menu',
		'itemActiveClass' => 'current',
		'anchorClass' => $pm ? '' : 'menu-link',
		'wrapTextInADiv' => true,
		'topLevelAngle' => $pm ? '<i class="sub-menu-indicator fa-solid fa-caret-down"></i>' : '<i class="icon-angle-down"></i>',
	]);
}

function siteWidgets() {
	//Do Better - if (variable('node-alias')) return '';

	$colsInUse = 0;

	$showSections = variable('link-to-section-home') && !variable('no-sections-in-footer');
	if ($showSections) $showSections = count($sections = variableOr('sections', []));
	if ($showSections) $colsInUse += 1;

	$showNetwork = !variable('no-network-in-footer');
	if ($showNetwork) $showNetwork = count($sites = variableOr('network-sites', main::defaultNetwork()));
	if ($showNetwork) $colsInUse += 1;

	$showSocial = !variable('no-social-in-footer');
	if ($showSocial) $showSocial = count($social = variableOr('social', main::defaultSocial()));
	if ($showSocial) $colsInUse += 1;

	if ($colsInUse == 0) return '';

	//adjust
	$grid = [1 => 12, 2 => 6, 3 => 4];
	$colspan = $grid[$colsInUse];

	$start = sprintf('<div id="footer-[WHAT]" class="col-md-%s mt-sm-%s pt-xs-3"><hr class="d-sm-none">', $colspan, $colspan) . NEWLINE;

	//TODO: Showcase + Misc
	$op = [];

	if ($showSections) {
		$op[] = str_replace('[WHAT]', 'sections', $start);
		$op[] = '<h4>Sections</h4>';
		foreach ($sections as $slug)
			$op[] = makeRelativeLink(humanize($slug), $slug) . BRNL;
		$op[] = '</div>'; $op[] = '';
	}

	if ($showNetwork) {
		$op[] = str_replace('[WHAT]', 'network', $start);
		$op[] = '<h4>Network</h4>';
		foreach ($sites as $site)
			$op[] =  getLink($site['name'], $site['url'], ' class = "icon site-' . $site['icon'] . '"') . BRNL;
		$op[] = '</div>'; $op[] = '';
	}

	if ($showSocial) {
		$op[] = str_replace('[WHAT]', 'social', $start);
		$op[] = '<h4>Social</h4>';
		foreach($social as $item) {
			$op[] = '<a target="_blank" href="' . $item['url'] . '" class="social-link me-4 mt-2">';
			$op[] = '	<i class="social-icon text-light si-mini rounded-circle ' . (contains($item['type'], ' ')
				? $item['type'] : 'fa-brands fa-'. $item['type'] . ' bg-' . $item['type']) . '"></i> ' . $item['name'] . '</a>';
			$op[] = '';
		}
		$op[] = '</div>'; $op[] = '';
	}

	return implode(variable('nl'), $op);
}

function getBreadcrumbs($items) {
	$op = [];
	foreach ($items as $slug => $text)
		$op[] = '<li class="breadcrumb-item">' . getLink($text, replaceHtml($slug)) . '</li>';
	return implode(NEWLINE . '			', $op);
}
