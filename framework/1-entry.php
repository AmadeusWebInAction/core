<?php
/**
 * This php framework is proprietary, Source-available software!
 * It is licensed for distribution at the sole discretion of it's owner Imran.
 * Copyright Oct 2019 -> 2025, AmadeusWeb.com, All Rights Reserved!
 * Author: Imran Ali Namazi <imran@amadeusweb.com>
 * Website: https://amadeusweb.com/
 * Source:  https://github.com/AmadeusWebInAction/amadeusweb
 * Note: AmadeusWeb v7.1 is based on 25 years of Imran's programming experience:
 *     * https://imran.yieldmore.org/career-past/
 */

DEFINE('AMADEUSROOT', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
DEFINE('AMADEUSFRAMEWORK', __DIR__ . DIRECTORY_SEPARATOR);
DEFINE('AMADEUSCORE', dirname(__DIR__) . DIRECTORY_SEPARATOR);
DEFINE('AMADEUSFEATURES', AMADEUSCORE . 'features/');
DEFINE('AMADEUSMODULES', AMADEUSCORE . 'modules/');
DEFINE('AMADEUSTHEMESFOLDER', AMADEUSROOT . 'themes/');

include_once AMADEUSFRAMEWORK . '2-stats.php'; //start time, needed to log disk load in files.php
include_once AMADEUSFRAMEWORK . '3-files.php'; //disk_calls, needed first to measure include times

function runFrameworkFile($name) {
	disk_include_once(AMADEUSFRAMEWORK . $name . '.php');
}

function runModule($name) {
	disk_include_once(AMADEUSMODULES . $name . '.php');
}

function runFeature($name) {
	disk_include_once(AMADEUSFEATURES . $name . '.php');
}

runFrameworkFile('4-array');
runFrameworkFile('5-vars');
runFrameworkFile('6-text'); //needs vars
runFrameworkFile('7-html');
runFrameworkFile('8-menu');

//New in 4.1
runFrameworkFile('9-render');
runFrameworkFile('10-seo-v2');
runFrameworkFile('11-assets');

//Fresh
runFrameworkFile('12-macros');

//v6.2 - renamed helper to special
runFrameworkFile('13-special');

//v6.5
runFrameworkFile('14-main');

function before_bootstrap() {
	$port = $_SERVER['SERVER_PORT'];
	variable('port', $port != 80 ? ':' . $port : '');
	variable('is_mobile_server', startsWith(__DIR__, '/storage/'));

	variable('local', $local = startsWith($_SERVER['HTTP_HOST'], 'localhost'));

	variable('app', $local && !variable('is_mobile_server')
		? replaceVariables('http://localhost%port%/amadeusweb/core/', 'port') : '//v7.amadeusweb.com/');

	if (DEFINED('AMADEUSURL')) variable('app', AMADEUSURL);

	variable('main', $local ? replaceVariables('http://localhost%port%/awe/web/', 'port') : '//amadeusweb.com/');
	variable('world', $local ? replaceVariables('http://localhost%port%/awe/world/', 'port') : '//amadeusweb.world/');

	variable('app-themes', $local ? replaceVariables('http://localhost%port%/awe/themes/', 'port') : '//themes.amadeusweb.com/');
	variable('app-static', $local ? replaceVariables('http://localhost%port%/awe/static/', 'port') : '//static.amadeusweb.com/');

	$php = contains($_SERVER['DOCUMENT_ROOT'], 'magique') || contains($_SERVER['DOCUMENT_ROOT'], 'Magique');
	variable('no_url_rewrite', $php);

	//v6.5 - no more allowing disable for these
	runModule('markdown');
	runModule('wordpress');
}

before_bootstrap();

//Now this only sets up the node and page parameters - rest moved to before_bootstrap()
function bootstrap($config) {
	variables($config);

	$php = variable('no_url_rewrite');
	if ($php) $node = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	else $node = isset($_GET['node']) && $_GET['node'] ? $_GET['node'] : '';

	if (endsWith($node, '/')) $node = substr($node, 0, strlen($node) - 1);
	if (startsWith($node, '/')) $node = substr($node, 1);

	if ($node == '') $node = 'index';

	if (variableOr('support_page_parameters', true) && strpos($node, '/') !== false) {
		variable('all_page_parameters', $node);
		$slugs = explode('/', $node);
		$node = array_shift($slugs);
		variable('page_parameters', $slugs);
		foreach ($slugs as $ix => $slug) variable('page_parameter' . ($ix + 1), $slugs[$ix]);
	}

	variable('node', variableOr('node-alias', $node));
}

function hasPageParameter($param) {
	return in_array($param, variableOr('page_parameters', [])) || isset($_GET[$param]);
}

function getThemeBaseUrl() {
	$themeName = variable('theme');
	$themeUrl = variable('app-themes') . "$themeName/";
	variable('themeUrl', $themeUrl);
	return $themeUrl;
}

function getThemeFile($file) {
	$themeName = variable('theme');
	return concatSlugs([AMADEUSTHEMESFOLDER, $themeName, $file]);
}

function renderThemeFile($file, $themeName = false) {
	if (!$themeName) $themeName = variable('theme');
	$variables = [
		'theme' => variable('app-themes') . "$themeName/",
		'themeFol' => $themeFol = concatSlugs([AMADEUSTHEMESFOLDER, $themeName, '']),
	];

	disk_include_once($themeFol . $file . '.php', $variables);
}

function render() {
	/*
	add_foot_hook(featurePath('asset-manager.php')); //NOTE: can this be here?
	if (isset($_GET['share'])) includeFeature('share');
	*/

	if (function_exists('before_render')) before_render();
	ob_start();

	$theme = variable('theme') ? variable('theme') : 'default';
	$embed = variable('embed');

	if (!$embed) {
		renderThemeFile('header', $theme);
		if (function_exists('before_file')) before_file();
	}

	$folder = variable('path') . '/' . (variable('folder') ? variable('folder') : '');
	$fwe =  $folder . variable('node');
	$rendered = renderAnyFile($fwe . '.', ['extensions' => 'core', 'return-on-first' => true]);

	if (isset($_GET['debug']) || isset($_GET['stats'])) {
		includeFeature('tables');
		variable('stats', true);
	}

	if (!$rendered) {
		if (function_exists('did_render_page') && did_render_page()) {
			//noop
		} else {
			//NOTE: Uses output buffering magic methods to delay sending of output until 404 header is sent 
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
			ob_flush();

			if (isset($_GET['debug'])) {
				echo 'NOTE: Turning on stats so you can additionally see what files are included! This appears below the footer' . variable('brnl') . variable('brnl');

				parameterError('FUNCTION EXISTS: did_render_page', function_exists('did_render_page') ? 'YES' : 'NO');

				$verbose = $_GET['debug'] == 'verbose';
				if ($verbose) {
					global $cscore;
					parameterError('ALL AMADEUS VARS - global $cscore;', $cscore);
				}
			}

			error('<h1 class="flash flash-red">Could NOT find file "' . variable('node') . '"</h1>in ' . $folder);
		}
	}

	ob_end_flush();

	if (!$embed) {
		if (function_exists('after_file')) after_file();
		renderThemeFile('footer', $theme);
		print_stats();
	}

	if (function_exists('after_render')) after_render();
}

function copyright_and_credits($separator = '<br />', $return = false) {
	$copy = _copyright(true);
	$cred = _credits('', true);
	$result = $copy . $separator . $cred;
	if ($return) return $result;
	echo $result;
}

function _copyright($return = false) {
	if (variable('dont_show_copyright')) return '';

	$year = date('Y');
	$start = variable('start_year');
	$from = ($start && ($start != $year)) ? $start . ' - ' : '';

	$before = variable('owned-by') ? '<strong>' . variable('name') . '</strong>, ' : '';
	$after = variable('owned-by') ? variable('owned-by') : variable('name');

	$result = '&copy; ' . $before . 'Copyright <strong><span>' . $after . '</span></strong>. ' . $from . $year . ' All Rights Reserved';
	if ($return) return $result; else echo $result;
}

function _credits($pre = '', $return = false) {
	if (variable('dont_show_amadeus_credits')) return '';

	$url = variable('main') . '?utm_content=site-credits&utm_referrer=' . variable('safeName');
	$result = $pre . sprintf('Powered by <a href="%s" target="_blank" class="amadeus-credits" style="display: inline-block;">' .
		variable('nl') . '			<img src="%s" height="50" alt="%s" style="vertical-align: middle;" /></a>',
		$url, variable('app-static') . 'amadeusweb/amadeusweb-logo@2x.png', 'Amadeus Web');

	if ($return) return $result; else echo $result;
}
