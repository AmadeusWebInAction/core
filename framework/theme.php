<?php
function getThemeTemplate($end = '-rich-page.php') {
	return getThemeFile(variable('sub-theme') . $end);
}

function run_theme_part($what) {

	if (!($content = variable('theme-template'))) {
		$file = getThemeFile(variable('sub-theme') . '.html');
		$bits = explode('##content##', disk_file_get_contents($file));
		$content = ['header' => $bits[0], 'footer' => $bits[1]];
		$content['footer-widgets'] = disk_file_get_contents(getThemeFile('footer-widgets.html'));
		variable('theme-template', $content);
	}

	$vars = [
		'theme' => getThemeBaseUrl(), //TODO: /version can be maintained on the individual file?
		'optional-slider' => '',
	];

	if ($what == 'header') {
		$icon = replaceItems('<link rel="icon" href="%url%%safeName%-icon.png%version%" sizes="192x192" />',
			['url' => fileUrl(), 'safeName' => variable('safeName'),
				'version' => assetMeta('site', 'version')], '%'); //TODO: simplify this version stuff?

		$vars['head-includes'] = '<title>' . title(true) . '</title>' . NEWLINES2 . '	' . $icon . NEWLINES2 . main::runAndReturn();
		$vars['seo'] = seo_tags(true);
		$vars['body-classes'] = body_classes(true);

		//TODO: if network exists, else if node is sub site else yada-yada-yada
		$imgUrl = assetUrl(variable('safeName') . '/' . variable('safeName') . '-logo@2x.png', 'app-static');
		$vars['logo'] = concatSlugs(['<a href="', pageUrl(), '"><img src="', $imgUrl, '" class="img-fluid img-max-',
		variableOr('footer-logo-max-width', '500'), '" alt="', variable('name'), '" /></a><br />'], '');

		$header = _substituteThemeVars($content, 'header', $vars);

		$bits = explode('##menu##', $header);

		echo _renderRaw($bits[0]);
		if (isset($bits[1])) {
			setMenuSettings();
			runFrameworkFile('header-menu');
			setMenuSettings(true);
			echo _renderRaw($bits[1]);
		}
	} else if ($what == 'footer') {
		$logo = concatSlugs(['<a href="', pageUrl(), '"><img src="', variable('app-static'), variable('safeName') . '/', variable('safeName') . '-logo@2x.png" class="img-fluid" alt="', variable('name'), '" /></a><br />'], '');
		$suffix = !variable('footer-message') ? '' : ' &mdash; ' . renderSingleLineMarkdown(variable('footer-message'), ['echo' => false]) . variable('nl');
		$fwVars = [
			'footer-logo' => $logo . '<u>' . variable('name') . '</u>' . $suffix . variable('nl'),
			'site-widgets' => siteWidgets(),
			'copyright' => _copyright(true),
			'credits' => _credits('', true),
			//TODO: 'social-icons' now removed -> use footer-widgets in all templates!
		];

		$vars['footer-widgets'] = _substituteThemeVars($content, 'footer-widgets', $fwVars);

		$footer = _substituteThemeVars($content, 'footer', $vars);

		$atBody = !contains($footer, '##footer-includes##');
		$bits = explode($atBody ? '</body>' : '##footer-includes##', $footer);

		echo _renderRaw($bits[0]);
		styles_and_scripts();
		if ($atBody) echo '</body>';
		echo _renderRaw($bits[1]);
	}
}

function _substituteThemeVars($content, $what, $vars) {
	if (function_exists('enrichThemeVars'))
		$vars = enrichThemeVars($vars, $what);

	if ($what == 'header') {
		if ($vars['optional-slider'] == '')
			$vars['body-classes'] = $vars['body-classes'] . ' no-slider';
	}
	return replaceItems($content[$what], $vars, '##');
}

function _renderRaw($html) {
	return renderAny($html, ['raw' => true, 'echo' => false]);
}

function setMenuSettings($after = false) {
	if ($after) {
		variable('menu-settings', false);
		return;
	}

	//same as non-profit header
	variable('menu-settings', [
		'noOuterUl' => false,
		'groupOuterUlClass' => 'menu-container',
		'outerUlClass' => 'menu-container',
		'ulClass' => 'sub-menu-container',
		'itemClass' => 'menu-item',
		'subMenuClass' => 'sub-menu',
		'itemActiveClass' => 'current',
		'anchorClass' => 'menu-link',
		'wrapTextInADiv' => true,
		'topLevelAngle' => '<i class="icon-angle-down"></i>',
	]);
}

function siteWidgets() {
	$start = '<div class="col-md-4 row">' . variable('nl');
	if (variable('node-alias')) return '';

	//TODO: Showcase + Misc
	$op = [];

	if (count($sections = variableOr('sections', []))) {
		$op[] = $start;
		$op[] = '<u>Sections</u>';
		foreach ($sections as $slug)
			$op[] = makeRelativeLink(humanize($slug), $slug);
		$op[] = '</div>'; $op[] = '';
	}

	$sites;
	if ($sites = variable('network-sites')) {
		$op[] = $start;
		$op[] = '<u>Network</u>';
		foreach ($sites as $site)
		$op[] =  getLink($site['name'], $site['url']);
		$op[] = '</div>'; $op[] = '';
	}

	if ($social = variableOr('social', main::defaultSocial())) {
		$op[] = $start;
		$op[] = '<u>Social</u>';
		foreach($social as $item) {
			$op[] = '<a target="_blank" href="' . $item['link'] . '" class="mt-2">';
			$op[] = '	<i class="social-icon text-light si-mini rounded-circle fa-brands fa-' . $item['type'] . ' bg-' . $item['type'] . '"></i> ' . $item['name'] . '</a>';
			$op[] = '';
		}
	}

	return implode(variable('nl'), $op);
}
