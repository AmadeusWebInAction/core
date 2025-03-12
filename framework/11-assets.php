<?php
function scriptTag($url) {
	echo PHP_EOL . '	<script src="' . $url . '" type="text/javascript"></script>';
}

function cssTag($url) {
	echo PHP_EOL . '	<link href="' . $url . '" rel="stylesheet" type="text/css" /> ';
}

function title($return = false) {
	if (variable('node-alias')) {
		$r = humanize(variable('name')) . ' | ' . variable('byline');
		if ($return) return $r;
		echo $r;
		return;
	}

	$page = variableOr('page-name', variable('node'));
	$siteRoot = $page == 'index' || variable('under-construction');

	if ($return === 'title-only') return $page;
	$r = [];

	if ($return !== 'params-only')
		$r[] = (!$siteRoot ? humanize($page) . ' - ' : '') . variable('name') . ($siteRoot ? ' | ' . variable('byline') : '');

	if ($return !== true) {
		$exclude = ['print', 'embed'];
		foreach(array_reverse(variableOr('page_parameters', [])) as $slug)
			if (!in_array($slug, $exclude)) $r[] = humanize($slug);
	}

	$r = implode(' <- ', $r);
	if ($return) return $r;
	echo $r;
}

function version($what = 'site') {
	$key = 'version_for_' . $what; //cache it to prevent long manipulations below
	if ($result = variable($key)) return $result;

	$ver = variable($what == 'site' ? 'version' : $what . 'Version');
	$result = $ver ? '?v=' . $ver['id'] . (false ? '&for=' . $what : '') . '&date=' . urlize($ver['date']) : '';
	variable($key, $result);
	return $result;
}

function asset_url($slug) {
	return strpos($slug, '%') !== false ? replaceVariables($slug) : ((startsWith($slug, 'http') || startsWith($slug, '//') ? '' : variable('url') . 'assets/') . $slug);
}

variables([
	'styles' => [],
	'scripts' => [],
]);

function addStyles($items) {
	_addAssets($items, 'styles');
}

function addScripts($items) {
	_addAssets($items, 'scripts');
}

function _addAssets($items, $type) {
	if (!is_array($items)) $items = [$items];
	$existing = variable($type);
	foreach ($items as $item) {
		if (in_array($item, $existing)) continue;
		$existing[] = $item;
	}
	variable($type, $existing);
}

function styles_and_scripts() {
	$ver = version();
	foreach (variable('styles') as $file)
			cssTag(asset_url($file) . '.css' . $ver);
	foreach (variable('scripts') as $file)
			scriptTag(asset_url($file) . '.js' . $ver);
}

function head_hooks() {
	if (variable('head_hooks')) foreach (variable('head_hooks') as $hook) disk_include_once($hook);
	main::analytics();
	main::chat();
}

function foot_hooks() {
	if (variable('foot_hooks')) foreach (variable('foot_hooks') as $hook) disk_include_once($hook);
}

function add_foot_hook($file) {
	variable('foot_hooks', array_merge(variableOr('foot_hooks', []), [$file]));
}
