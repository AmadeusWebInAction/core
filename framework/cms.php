<?php
function before_render() {
	addStyles('%app-common-assets%amadeusweb7');
	//TODO: high! read_seo_info();

	foreach (variable('sections') as $slug) {
		if ($slug == $node = variable('node')) {
			variable('directory_of', $node);
			variable('section', $slug);
			afterSectionSet();
			return;
		}

		$page1 = variable('page_parameter1') ? variable('page_parameter1') : 'home';
		$folUptoNode = variable('path') . '/' . $slug . '/' . $node;
		$level1 = [$folUptoNode . '/' . $page1 . '.', $folUptoNode . '.'];

		if (ifOneOfFilesExists($slug, $level1)) return;

		if (disk_is_dir($folInNode = $folUptoNode . '/')) {
			$in1 = variable('page_parameter1');
			$folInPage1 = $folInNode . $in1;
			$in2 = variable('page_parameter2');
			$folInPage2 = $folInPage1 .'/' . $in2;

			//do in reverse
			if (disk_is_dir($folInPage2)) {
				$page3 = variable('page_parameter3') ? variable('page_parameter3') : 'home';
				$level3 = [$folInPage2 . '/' . $page3 . '.', $folInPage2 . '.'];
				if (ifOneOfFilesExists($slug, $level3, $in1 . '/' . $in2, 2)) {
					variable('node-folder-item1', $in1);
					return;
				}
			}

			if (disk_is_dir($folInPage1)) {
				$page2 = variable('page_parameter2') ? variable('page_parameter2') : 'home';
				$level2 = [$folInPage1 . '/' . $page2 . '.', $folInPage1 . '.'];
				if (ifOneOfFilesExists($slug, $level2, $in1)) return;
			}
		}
	}
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
	if (function_exists('site_before_render')) site_before_render();
}

function did_render_page() {
	if (variable('directory_of')) {
		runFeature('directory');
		return true;
	}

	if ($file = variable('file')) {
		autoRender($file);
		return true;
	}
}

bootstrap([]);
