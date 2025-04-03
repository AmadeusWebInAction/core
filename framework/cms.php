<?php
function before_render() {
	addStyle('amadeusweb7', 'app-static--common-assets');
	addStyle('amadeus-web-features', 'app-static--common-assets');
	addScript('amadeusweb7', 'app-static--common-assets');

	if (variable('use-site-static')) variable('site-static',
		assetMeta(variable('network') ? 'network-static' : 'site-static')['location']);
	//TODO: high! read_seo_info();

	if (hasSpecial()) { afterSectionSet(); return; }

	$hasFiles = variable('sections-have-files');
	$node = variable('node');
	foreach (variable('sections') as $slug) {
		if (disk_file_exists($incFile = variable('path') . '/' . $slug . '/' . $node . '/_include.php')) {
			variable('section', $slug);
			disk_include_once($incFile);
			if (hasVariable('is-standalone-section')) {
				afterSectionSet();
				return;
			}
		}

		if (function_exists('before_render_section')){
			if (before_render_section($slug)) {
				afterSectionSet();
				return;
			}
		}

		if (!$hasFiles && $slug == $node) {
			variable('directory_of', $node);
			variable('section', $slug);
			afterSectionSet();
			return;
		}

		if ($hasFiles && $slug == $node) {
			$level0 = [$slug == $node ? variable('path') . '/' . $slug . '/home.' :
				variable('path') . '/' . $slug . '/' . $node . '.'];
			if (ifOneOfFilesExists($slug, $level0)) return;
		}

		$page1 = variable('page_parameter1') ? variable('page_parameter1') : 'home';
		$folUptoNode = variable('path') . '/' . $slug . '/' . $node;
		$level1 = [$folUptoNode . '/' . $page1 . '.', $folUptoNode . '.'];

		if (ifOneOfFilesExists($slug, $level1)) return;

		if (disk_is_dir($folInNode = $folUptoNode . '/')) {
			$in1 = variable('page_parameter1');
			$folInPage1 = $folInNode . $in1;
			$in2 = variable('page_parameter2');
			$folInPage2 = $folInPage1 .'/' . $in2 .'/';

			//do in reverse
			if ($in2 && disk_is_dir($folInPage2)) {
				$page3 = variableOr('page_parameter3', 'home');
				$level3 = [$folInPage2 . '/' . $page3 . '.', $folInPage2 . '.'];
				if (ifOneOfFilesExists($slug, $level3, $in1 . '/' . $in2, 2)) {
					variable('node-folder-item1', $in1);
					return;
				}
			}

			if ($in1 && disk_is_dir($folInPage1)) {
				$page2 = variableOr('page_parameter2', 'home');
				$level2 = [$folInPage1 . '/' . $page2 . '.', $folInPage1 . '.'];
				if (ifOneOfFilesExists($slug, $level2, $in1)) return;
			}
		}
	}

	//lets make it a point to call before render here, assuming either its a "content" page or will throw an error
	afterSectionSet();
}

function ifOneOfFilesExists($section, $fwes, $nodeFolderItem = false, $nodeFolderLevel = 1) {
	foreach ($fwes as $fwe) {
		$ext = disk_one_of_files_exist($fwe, 'php, md, tsv, html');
		if (!$ext) continue;

		variable('file', $fwe . $ext);
		variable('section', $section);
		if ($nodeFolderItem) variable($key = 'node-folder-item' . $nodeFolderLevel, $nodeFolderItem);

		afterSectionSet();
		return true;
	}
	return false;
}

function afterSectionSet() {
	//TODO: include _folder.php on $file if it exists
	if (function_exists('site_before_render')) site_before_render();
}

function did_render_page() {
	if (renderedSpecial()) return true;

	if (variable('directory_of')) {
		runFeature('directory');
		return true;
	}

	if ($file = variable('file')) {
		autoRender($file);
		return true;
	}
}

function site_humanize($txt, $field = 'title', $how = false) {
	$pages = variableOr('siteHumanizeReplaces', []);
	if (array_key_exists($key = strtolower($txt), $pages))
		return $pages[$key];

	return $txt;
}

bootstrap([]);
