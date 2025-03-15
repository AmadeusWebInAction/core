<?php
$usePreview = variableOr('use-preview', false);
$local = variable('local'); //this is now in before_bootstrap
if ($usePreview) variable('preview', $preview = contains($_SERVER['HTTP_HOST'], 'preview'));

//tests preview urls locally
//$local = false; $preview = true;

if ($usePreview) {
	variable('live', $liveFolder = contains(__DIR__, 'live'));

	if ($liveFolder)
		variable('site-url-key', ($local ? 'live-on-local' : 'live') . '-url');
	else
		variable('site-url-key', ($preview ? 'preview' : 'local') . '-url');

	//until green
	if (variable('live-is-empty') && $liveFolder && !$local) {
		echo '<!--silence-->';
		exit;
	}
} else {
	variable('site-url-key', ($local ? 'local' : 'live') . '-url');
}

function __testSiteVars($array) {
	return; //comment to test
	print_r($array);
}

$sheet = getSheet('site', false);
$cols = $sheet->columns;

$siteVars = [];
foreach ($sheet->rows as $row) {
	$key = $row[$cols['key']];
	if (!$key || $key[0] == '|') continue;
	$siteVars[$key] = $row[$cols['value']];
}

variable('site-vars', $siteVars);

if (contains($url = $siteVars[variable('site-url-key')], 'localhost')) {
	$url = replaceItems($url, ['localhost' => 'localhost' . variable('port')]);
	__testSiteVars(['url-for-localhost' => $url]);
}

function parseSectionsAndGroups($siteVars, $return = false, $forNetwork = false) {
	if (variable('sections') && !$forNetwork) return;
	$sections = isset($siteVars['sections']) ? $siteVars['sections'] : false;
	if (!$sections) {
		$sections = [];
		if (!$forNetwork) variable('sections', $sections);
		__testSiteVars(['sections' => $sections]);
		return $sections;
	}

	$vars = [];
	//Eg.: research, causes, solutions, us: programs+members+blog
	if (contains($sections, ':')) {
		$swgs = explode(', ', $sections); //sections wtih groups
		$items = []; $groups = [];

		foreach ($swgs as $item) {
			if (contains($item, ':')) {
				$bits = explode(': ', $item, 2);
				$subItems = explode('+', $bits[1]);
				$groups[$bits[0]] = $subItems;
				$items = array_merge($items, $subItems);
			} else {
				$items[] = $item;
				$groups[] = $item;
			}
		}

		$vars['sections'] = $items;
		$vars['section-groups'] = $groups;
	} else {
		$vars['sections'] = explode(', ', $sections);
	}

	if ($return) return $vars;

	__testSiteVars($vars);
	variables($vars);
}

parseSectionsAndGroups($siteVars);

//valueIfSetAndNotEmpty
function _visane($siteVars) {
	$possibles = [
		['site-home-in-menu', false, 'bool'],
		['use-menu-files', false, 'bool'],
		['large-menu', false, 'bool'],
		['large-menus-for', [], 'array'],
		['home-link-to-section', false, 'bool'],
		['ChatraID', '--use-amadeusweb'],
		['google-analytics', '--use-amadeusweb'],

		['email', 'imran@amadeusweb.com'],
		['phone', '+91-9841223313'],
		['whatsapp', '919841223313'],
		['address', 'Chennai, India'],

		['description', false],
		['network', false], //string will be returned by default if set
	];

	if (!hasVariable('theme')) {
		$possibles[] = ['theme', 'canvas'];
		$possibles[] = ['sub-theme', variableOr('sub-theme', 'business')];
	}

	$op = [];
	foreach ($possibles as $cfg)
		$op[$cfg[0]] = valueIfSetAndNotEmpty($siteVars, $cfg[0], $cfg[1], isset($cfg[2]) ? $cfg[2] : 'no-change');

	__testSiteVars($op);
	variables($op);
}

function _always($siteVars) {
	$op = [];
	$always = [
		'name',
		'byline',
		'safeName',
		'footer-message',
		'siteMenuName',
	];
	foreach ($always as $item)
		$op[$item] = $siteVars[$item];

	$op['start_year'] = $siteVars['year'];

	__testSiteVars($op);
	variables($op);
}

_visane($siteVars);
_always($siteVars);

$safeName = $siteVars['safeName'];
$network = variable('network');

//TODO: impl needed if using static?! if (disk_file_exists(SITEPATH . '/assets/site.css')) addStyle('site', 'site');

variables($op = [
	//version will be done with txt file if needed (see 11-assets.php)
	'folder' => 'content/',
	//sections also done above in parseSectionsAndGroups
	'image-in-logo' => disk_file_exists(SITEPATH . '/' . $safeName . '-logo.png') ? '-logo.png' : false,
	'siteHumanizeReplaces' => siteHumanize(),

	'home-link-to-section' => true, //directory will show these
	'sections-have-files' => true,

	'scaffold' => isset($siteVars['scaffold']) ? explode(', ', $siteVars['scaffold']) : [],

	'path' => SITEPATH,
	'assets-url' => $url,
	'page-url' => scriptSafeUrl($url),
]);

__testSiteVars($op);

//TODO: add_foot_hook(AMADEUSTHEMEFOLDER . 'media-kit.php');

if ($network) setupNetworkLinks($network);

function setupNetworkLinks($network) {
	disk_include_once(siteRealPath('/../network.php')); //TODO: make a conditional check and remove from site.tsv
	$data = siteRealPath('/../sites.tsv');
	//if (!disk_file_exists($data)) return; //NOTE: Design By Contract - let it throw

	$sitesSheet = getSheet($data, false);

	$op = [];
	$newTab = false ? 'target="_blank" ' : '';
	$sites = [];

	$imgIndex = isset($sitesSheet->columns['img']) ? $sitesSheet->columns['img'] : '';
	$themeIndex = isset($sitesSheet->columns['theme']) ? $sitesSheet->columns['theme'] : false;
	$groupIndex = isset($sitesSheet->columns['group']) ? $sitesSheet->columns['group'] : false;

	function __setupNetworkVars() {
		$networkSheetFile = siteRealPath('/../network.tsv');
		$networkSheet = getSheet($networkSheetFile, 'key');
		$networkVal = $networkSheet->columns['value'];

		$networkItem = $networkSheet->group;
		if (isset($networkItem[$networkKey = 'network-' . variable('site-url-key')]))
			variable('network-url', $networkUrl = $networkItem[$networkKey][0][$networkVal]);
		
		//NOTE: expects url to be set above, version not needed as we will implement network-static
		if (isset($networkItem['network-version'])) assetMeta('network', $networkMeta = [
			'version' => $networkItem['network-version'][0][$networkVal],
			'baseurl' => $networkUrl . 'assets/',
		]);

		$networkVars = [
			'url' => $networkUrl,
			//'meta' => $networkMeta,
			'name' => $networkItem['network-name'][0][$networkVal],
			'safeName' => $networkItem['network-safeName'][0][$networkVal],
			'byline' => $networkItem['network-byline'][0][$networkVal],
			'message' => $networkItem['network-message'][0][$networkVal],
		];

		variable('network', $networkVars);
		variable('is-network-site', variable('safeName') == $networkVars['safeName']);
	
		if (disk_file_exists(siteRealPath('/../assets/network.css'))) //TODO: !!!
			addStyle('network', 'network');
	}

	__setupNetworkVars();

	foreach ($sitesSheet->rows as $row) {
		$site = $row[$sitesSheet->columns['slug']];

		$sheetFile = siteRealPath('/../' . $site . '/data/site.tsv');
		if (!sheetExists($sheetFile)) { continue; }

		$sheet = getSheet($sheetFile, 'key');
		$val = $sheet->columns['value'];

		$img = $imgIndex ? $row[$imgIndex] : '';
		$theme = $themeIndex === false ? false : $row[$themeIndex];
		$group = $groupIndex === false ? false : $row[$sitesSheet->columns['group']];

		$item = $sheet->group;

		if (contains($url = $item[variable('site-url-key')][0][$val], 'localhost'))
			$url = replaceItems($url, ['localhost' => 'localhost' . variable('port')]);

		$op[] = sprintf('<a href="%s" %stitle="%s &mdash; %s">%s</a>',
			$url, $newTab, $name = $item['name'][0][$val], $byline = $item['byline'][0][$val], $item['name'][0][$val], variable('nl'));

		$sites[$site] = [
			'name' => $name, 'byline' => $byline,
			'safeName' => $item['safeName'][0][$val],
			'vars' => parseSectionsAndGroups(['sections' => $item['sections'][0][$val]], true, true),
			'img' => $img, 'url' => $url,
			'link' => end($op),
			'item' => $item, //dont want to recreate the tsv path
			'valueIndex' => $val,
			'group' => $group,
		];
	}
	
	variable('network-sites', $sites);
}

runFrameworkFile('cms');

if (disk_file_exists($cms = SITEPATH . '/cms.php'))
	disk_include_once($cms);

if (hasPageParameter('embed')) variable('embed', true);

render();
