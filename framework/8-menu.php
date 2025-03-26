<?php
variable('toggle-list', 'toggle-list-below');

function _handleSlashes($file, $handle, $useMDash) {
	if (!$handle || !contains($file, '/'))
		return $file;

	$test = humanize($file);
	if (!contains($test, '/'))
		return $test;

	$bits = explode('/', $file);
	return $useMDash ? join(' &mdash; ', $bits) : array_pop($bits);
}

function _skipExcludedFiles($files, $excludeNames = 'home', $excludeExtensions = 'jpg, png', $stripExtension = false) {
	$op = [];

	$excludeNames = explode(', ', $excludeNames);
	$checkNames = count($excludeNames) > 0;

	$excludeExtensions = explode(', ', $excludeExtensions);
	$checkExtensions = count($excludeExtensions) > 0;

	foreach($files as $item) {
		if ($item[0] == '.' OR $item[0] == '_')
			continue;

		if ($checkNames && in_array(stripExtension($item), $excludeNames))
			continue;

		if ($checkExtensions && in_array(getExtension($item), $excludeExtensions))
			continue;

		if ($stripExtension)
			$item = stripExtension($item);

		$op[] = $item;
	}

	return $op;
}

//TODO: remove? used in adesh/help.php
function get_page_menu_variables() {
	$menuOf = variable('node') . '/' . variable('page_parameter1') . '/';
	$menuIn = '/' . variable('section') . '/' . variable('node') . '/' . 'data/' . variable('page_parameter1') . '/';
	$menuAt = SITEPATH . $menuIn;
	$menu1 = variable('page_parameter2') ? variable('page_parameter2') : false;
	$menu2 = variable('page_parameter3') ? variable('page_parameter3') : false;
	return compact('menuOf', 'menuIn', 'menuAt', 'menu1', 'menu2');
}

function pageMenu($file) {
	if (!(variable('section')) || variable('no-page-menu')) return;
	$folder = concatSlugs([SITEPATH, variable('section'), variable('node')]) . '/';

	$subPage1 = variable('page_parameter1');
	$subPage2 = variable('page_parameter2');
	$subPage3 = variable('page_parameter3');

	$levels = []; $levelAbove = false;

	if ($subPage2)
		$levels[] = [ 'base' => 'sub-page-url', 'tsv' => $folder . $subPage1 . '/' . $subPage2 . '/_subpages.tsv' ];
	if ($subPage1)
		$levels[] = [ 'base' => 'page-url', 'tsv' => $folder . $subPage1 . '/_subpages.tsv' ];
	$levels[] = [ 'base' => 'node-url', 'tsv' => $folder . '_pages.tsv' ];

	//parameterError('levels', $levels, false);
	$levelFound = false;
	foreach ($levels as $item) {
		if (disk_file_exists($item['tsv'])) {
			$levelFound = $item;
			break;
		}
		$levelAbove = $item;
	}

	if ($levelFound && $levelAbove) {
		$folAbove = dirname($levelAbove['tsv']) . '/';
		if (disk_file_exists($folAbove . '/home.md')) { //has files but no tsv
			$levelFound = false;
			$folder = $folAbove;
		}
	}

	if (!$levelFound) {
		//TODO: read meta and build table - do that if home.md has meta, expect for all
		contentBox('pages-menu', 'block-links container after-content');


		$params = str_replace(SITEPATH, '', $folder);
		$parentSlug = variable('node') . '/'; //also known as site
		if ($subPage1) $parentSlug .= $subPage1 . '/';
		if ($subPage2) $parentSlug .= $subPage2 . '/';

		h2(humanize(variable('page_parameter' . ($subPage3 ? '2' : '1'))). currentLevel(true));
		menu($params, ['parent-slug' => $parentSlug, 'link-to-home' => true, 'ul-class' => 'block-links']);
		contentBox('end');
		return;
	}

	$tsv = $levelFound['tsv'];
	$bits = explode('/', variable('all_page_parameters'));
	$tail = end($bits);
	contentBox('pages', 'container after-content');
	h2(humanize($tail) . currentLevel(true));
	runFeature('tables');

	add_table('pages-table', $tsv, ($subPage1 ? 'sub-' : '') . 'page-name, about, tags',
		'<tr><td><a href="%' . $levelFound['base'] . '%%name_urlized%">%name_humanized%</a></td><td>%about%</td><td>%tags%</td></tr>');
	contentBox('end');
}

DEFINE('ABSOLUTEPATHPREFIX', 'ABSOLUTE=');

function menu($folderRelative = false, $settings = []) {
	if (variable('under-construction')) return;
	if (is_array(variable('site-menu-settings'))) $settings = array_merge(variable('site-menu-settings'), $settings);

	$useSections = valueIfSetAndNotEmpty($settings, 'sections-not-list');
	$itemTag = $useSections ? 'section' : 'li';
	$noul = $useSections || isset($settings['no-ul']);

	$class_li = arrayIfSetAndNotEmpty($settings, 'li-class');
	$class_active = arrayIfSetAndNotEmpty($settings, 'li-active-class', 'selected');
	$class_link = arrayIfSetAndNotEmpty($settings, 'a-class');
	$class_ul = arrayIfSetAndNotEmpty($settings, 'ul-class');

	//NOTE: needed for can_access
	$what = valueIfSetAndNotEmpty($settings, 'what');
	$where = valueIfSetAndNotEmpty($settings, 'where', '');

	$backToHome = valueIfSet($settings, 'back-to-home', '');
	$menuLevel = valueIfSetAndNotEmpty($settings, 'menu-level', 1);

	$result = '';
	if (!$noul) $result .= variable('nl') . '		<ul' . cssClass($class_ul) . '>' . variable('nl');

	$isAbsolute = startsWith($folderRelative, ABSOLUTEPATHPREFIX);
	$folderPrefix = $isAbsolute ? '' : variable('path');
	if ($isAbsolute) $folderRelative = substr($folderRelative, strlen(ABSOLUTEPATHPREFIX));
	$folder = $folderPrefix. ($folderRelative ? $folderRelative : (variable('folder') ? '/' . variable('folder') : '/'));

	$filesGiven = false;
	$couldHaveSlashes = isset($settings['could-have-slashes']) && $settings['could-have-slashes'];
	$givenFiles = valueIfSetAndNotEmpty($settings, 'files');
	$inHeader = valueIfSetAndNotEmpty($settings, 'in-header');

	$namesOfFiles = false;
	if ($givenFiles) {
		$files = $givenFiles;
		$filesGiven = true;
	} else {
		if (disk_file_exists($itemsTsv = $folder . '_menu-items.tsv')) {
			$itemsSheet = getSheet($itemsTsv, 'slug');
			$files = [];

			$hasSNo = isset($itemsSheet->columns['sno']);
			if ($hasSNo) { $namesOfFiles = []; $snoIndex = $itemsSheet->columns['sno']; }
			//later we can make it sno and text as optional

			foreach ($itemsSheet->group as $thisFile => $thisItem) {
				$files[] = $thisFile;
				if ($hasSNo)
					$namesOfFiles[$thisFile] = $thisItem[0][$snoIndex] . '. ' . humanize($thisFile);
			}
		} else {
			$files = _skipExcludedFiles(disk_scandir($folder));
		}

		$config = getConfigValues($folder . '_menu-config-values.txt'); //for some reason, . in the filename doesnt work - does for .template.html though
		if($config) {
			if (isset($config['reverse']) && $config['reverse'] == 'yes')
				$files = array_reverse($files);

			if (isset($config['limit']))
				$files = getRange($files, intval($config['limit']));
		}
	}

	$exclude = valueIfSet($settings, 'exclude-files', []);
	$exclude = array_merge(variable('exclude-folders'), $exclude);
	$breaks = valueIfSetAndNotEmpty($settings, 'breaks', []); //NOTE: needed for immersive education node
	$prefix = isset($settings['prefix']) ? $settings['prefix'] . ' ' : '';
	$wrapInDiv = ($wrapInDivVO = valueIfSetAndNotEmpty($settings, 'wrap-text-in-a-div')) && $menuLevel != 1;
	$onlySlugForSectionMenu = valueIfSet($settings, 'humanize');

	//If neither specified, returns mixed.
	$onlyFiles = valueIfSet($settings, 'list-only-files');
	$onlyFolders = valueIfSet($settings, 'list-only-folders');

	$excludeExtensions = valueIfSet($settings, 'exclude-extensions', []);

	$base = valueIfSet($settings, 'parent-slug', '');
	$noLinks = valueIfSet($settings, 'no-links');
	$blogHeading = valueIfSet($settings, 'blog-heading');

	$section = explode('/', $folderRelative)[1];
	$last = false;

	if (isset($settings['link-to-home']) && $settings['link-to-home']) {
		$homeBase = $base;
		if ($homeBase == '' && isset($settings['parent-slug-for-home-link'])) $homeBase = $settings['parent-slug-for-home-link'];

		$mainNode = ($section == variable('node')) || startsWith($folderRelative, '/' . variable('section'));
		$result .= replaceItems(variable('nl') . '<li%li-classes%><a href="%url%"%style%%a-classes%><%wrap-in%>%text%</%wrap-in%></a>' . variable('nl'), [
			'li-classes' => cssClass(array_merge($class_li, $mainNode ? ['selected'] : [], ['home-link'])),
			'a-classes' => cssClass($class_link),
			'wrap-in' => $wrapInDivVO ? 'div' : 'u',
			'url' => pageUrl() . $homeBase,
			'style' => $mainNode ? ' style="background-color: var(--amw-home-link-color);"' : '',
			'text' => 'Home'
		], '%');
	}

	if ($append = valueIfSetAndNotEmpty($settings, 'files-to-append', []))
		$files = array_merge($files, $append);

	$files = isset($settings['reorderItems']) ? $settings['reorderItems']($files) : $files;

	foreach ($files as $file) {
		if ($file == 'index') continue; //scaffolded but not in menu

		//skip these checks when there is a whitelist
		if (!$filesGiven && !in_array($file, $append)) {
			if ($onlyFolders != $onlyFiles) {
				if ($onlyFolders && !is_dir($folder . $file)) continue;
				if ($onlyFiles && is_dir($folder . $file)) continue;
			}

			$info = pathinfo($file);
			$bits = [$info['filename']]; //TODO: move to files.php
			if (isset($info['extension'])) $bits[] = $info['extension'];

			$extension = getExtension($file);
			$file = stripExtension($file);
			$isDir = disk_is_dir($folder . $file);
			if ($isDir) $extension = '';

			if ($file && $file[0] != '~' && !$extension && !$isDir) {
				if (variable('local')) {
					parameterError('$settings', $settings);
					parameterError('file with no extension - skipping', [ 'folder' => $folder, 'file' => $file]);
				}
				continue;
			}

			if ($extension && in_array($extension, $excludeExtensions)) continue;
		} else {
			$extension = 'none';
		}

		$indented = '';
		if (startsWith($file, '~')) {
			if (variable('thisSection') && !$indented) { $result .= '<hr />'; variable('hadMenuSection', true); }
			$result .= variable('nl') . '	<' . $itemTag . ' class="menu-section">' . substr($file, 1) . '</' . $itemTag . '>';
			$indented = 'indented';
			continue;
		} else if ($file == '----') {
			$result .= variable('nl') . '	<' . $itemTag . ' class="menu-break"><hr /></' . $itemTag . '>' . PHP_EOL;
			continue;
		}

		if (!$filesGiven) {
			if (in_array($file, $exclude)) continue;
			$isNotValidFile = disk_is_dir($folder . $file) && !isset($bits[1]);
			if ($file == 'index' || substr($file, 0, 1) == '_' || $last == $file) continue;
		}

		if (isset($settings['visible']) && !$settings['visible']($file)) continue;
		$last = $file;

		//note removed the $extensions - guess used in archives for jpg linking to jpg or something..
		$url = pageUrl($base . $file); //new method will autoadd trailing slash

		$file = _handleSlashes($file, $filesGiven || $couldHaveSlashes, $couldHaveSlashes);
		/*
		TODO: when to reinstate?
		if ($what == 'page') { if (cannot_access_page($file)) continue; }
		else { if (cannot_access($file, 'page')) continue; }
		*/

		$text = $namesOfFiles && isset($namesOfFiles[$file]) ? $namesOfFiles[$file] : humanize($file, $onlySlugForSectionMenu);

		//TODO: HIGH: LOOK FOR USAGE:

		if (isset($settings['innerHtml'])) {
			$innerHtml = $settings['innerHtml']($file, compact('extension', 'url', 'folder'));
		} else {
			if ($wrapInDivVO) $text = '<div>' . $text . '</div>';
			$innerHtml = getLink($text, $url, cssClass(array_merge($class_link)));
		}

		if ($blogHeading) $innerHtml = blog_heading($file, variable('node'));

		if ($noLinks) {
			$result .= variable('nl') . '	<' . $itemTag . cssClass($class_li) . '>' . $innerHtml . '</' . $itemTag . '>' . variable('nl');
		} else {
			if ($inHeader) {
				$result .= '<hr />' . variable('2nl') . '<h2 class="' . variable('toggle-list') . '">' . humanize($file) .'</h2>' . variable('nl');
				$result .= menu($folderRelative . $file . '/', [
					'parent-slug' => variable('node') . '/',
					'menu-level' => $menuLevel + 1,
					'return' => true,
				]) . variable('2nl');
			} else {
				$thisClass = array_merge($class_li);
				if ($file == variable('node') || $file == variable('page_parameter1'))
					$thisClass = array_merge($thisClass, $class_active);

				if ($indented) $thisClass[] = $indented;
				$result .= variable('nl') . '	<' . $itemTag . cssClass($thisClass) . '>'
					. $innerHtml . '</' . $itemTag . '>' . variable('nl');
			}
		}

		if (in_array($file, $breaks))
			$result .= variable('nl') . '	<' . $itemTag . ' class="menu-break"><hr /></' . $itemTag . '>' . variable('nl');
	}

	if ($backToHome) {
		$thisClass = array_merge($class_li, ['back-to-home-link']);
		$thisAClass = array_merge($class_link);
		$result .= sprintf(PHP_EOL . '<li%s><a href="%s"%s>%s</a>',
			cssClass($thisClass),
			pageUrl(),
			cssClass($thisAClass),	
			'** Back to ' . variable('abbr'));
	}

	if (!$noul) $result .= '</ul>' . variable('2nl');

	$return = isset($settings['return']) ? $settings['return'] : false;
	if ($return) return $result;
	echo $result;
}
