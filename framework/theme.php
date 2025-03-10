<?php
function getThemeTemplate($end = '-rich-page.php') {
	return getThemeFile(variable('sub-theme') . $end);
}

function run_theme_part($what) {

	if (!($content = variable('theme-template'))) {
		$file = getThemeFile(variable('sub-theme') . '.html');
		$bits = explode('##content##', disk_file_get_contents($file));
		$content = ['header' => $bits[0], 'footer' => $bits[1]];
		variable('theme-template', $content);
	}
	$vars = [
		'theme' => getThemeBaseUrl(),
		'optional-slider' => '',
	];

	if ($what == 'header') {
		$vars['title'] = title(true);
		$vars['icon'] = replaceItems('<link rel="icon" href="%url%%safeName%-icon.png%version%" sizes="192x192" />',
			['url' => variable('url'), 'safeName' => variable('safeName'), 'version' => version()], '%');
		$vars['seo'] = seo_tags(true);
		$vars['body-classes'] = body_classes(true);

		$vars['logo'] = concatSlugs(['<a href="', variable('url'), '"><img src="', variable('app-static'), variable('safeName') . '/', variable('safeName') . '-logo@2x.png" class="img-fluid img-max-',
		variableOr('footer-logo-max-width', '500'), '" alt="', variable('name'), '" /></a><br />'], '');

		$header = _substituteThemeVars($content, 'header', $vars);

		$bits = explode('##menu##', $header);

		echo _renderRaw($bits[0]);
		setMenuSettings();
		if (function_exists('header_menu')) header_menu(); else menu();
		setMenuSettings(true);
		echo _renderRaw($bits[1]);
	} else if ($what == 'footer') {
		$suffix = !variable('footer-message') ? '' : ' &mdash; ' . renderSingleLineMarkdown(variable('footer-message'), ['echo' => false]) . '<hr />' . variable('nl');
		$vars['footer-logo'] = '<u>' . variable('name') . '</u>' . $suffix . variable('nl');
		$vars['footer-widgets'] = footerWidgets();
		$vars['copyright'] = copyright_and_credits(BRTAG, true);
		$vars['social-icons'] = socialWidgets();

		$footer = _substituteThemeVars($content, 'footer', $vars);

		$atBody = !contains($footer, '##footer-includes##');
		$bits = explode($atBody ? '</body>' : '##footer-includes##', $footer);

		echo _renderRaw($bits[0]);
		foot_hooks();
		styles_and_scripts();
		if ($atBody) echo '</body>';
		echo _renderRaw($bits[1]);
	}
}

function _substituteThemeVars($content, $what, $vars) {
	if (function_exists('enrichThemeVars'))
		$vars = enrichThemeVars($vars, $what);

	return replaceItems($content[$what], $vars, '##');
}

function _renderRaw($html) {
	return renderAny($html, ['raw' => true, 'echo' => false]);
}

function setMenuSettings($after = false) {
	if ($after) {
		variable('site-menu-settings', false);
		return;
	}

	//same as non-profit header
	variable('site-menu-settings', [
		'group-outer-ul-class' => 'sub-menu-container',
		'outer-ul-class' => 'menu-container',
		'ul-class' => 'sub-menu-container',
		'li-class' => 'menu-item',
		'li-active-class' => 'current',
		'a-class' => 'menu-link',
		'wrap-text-in-a-div' => true,
		'top-level-angle' => '<i class="icon-angle-down"></i>',
	]);
}

function footerWidgets() {
	$sites = variable('network-site-configs');
	if (!$sites || variable('node-alias')) return '';

	$result = '';
	foreach ($sites as $site) {
		$h3Class = ' site-' . $site['safeName'] . '-bgd';
		$result .= renderNetworkPanel($site, 'footer', ['h3-class' => $h3Class, 'return' => true]);
	}

	return $result;
}

function socialWidgets() {
	$social = variable('social');
	if (!$social) return '';

	$op = ['<div class="normal-social-icons contrasting-bg-color">'];
	foreach($social as $item) {
		$op[] = '<a target="_blank" href="' . $item['link'] . '" class="social-icon si-mini rounded-circle border-0 text-light bg-' . $item['type'] . '">';
		$op[] = '	<i class="fa-brands fa-' . $item['type'] . '">' . contact_r($item['link']) . '</i></a>';
		$op[] = '';
	}
	$op[] =	'<hr style="clear: both; margin: 0;" /></div>';
	return implode(variable('nl'), $op);
}
