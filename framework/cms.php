<?php
function before_render() {
	addStyles('%app-common-assets%amadeusweb7');
	//TODO: high! read_seo_info();

	foreach (variable('sections') as $slug) {
		if ($slug == $node = variable('node')) {
			variable('directory_of', $node);
			variable('section', $slug);
			return _callAndVoid();
		}

		$page = variable('page_parameter1') ? variable('page_parameter1') : 'home';
		$fwes = [
			variable('path') . '/' . $slug . '/' . $node . '/' . $page . '.',
			variable('path') . '/' . $slug . '/' . $node . '.',
		];

		foreach ($fwes as $fwe) {
			$ext = disk_one_of_files_exist($fwe, 'php, md, tsv, html');
			if ($ext) {
				variable('file', $fwe . $ext);
				variable('section', $slug);
				return _callAndVoid();
			}
		}

		if (disk_is_dir($fol = variable('path') . '/' . $slug . '/' . $node . '/')) {
			$ext = disk_one_of_files_exist($fwes[0], 'php, md, tsv, html');
			if ($ext) {
				variable('file', $fwes[0] . $ext);
				variable('section', $slug);
				return _callAndVoid();
			}
		}
	}
}

function _callAndVoid() {
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
