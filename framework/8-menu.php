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

function _skipExcludedFiles($files, $exclude = [], $stripExtension = false) {
	$op = [];
	$checkExclusions = count($exclude) > 0;
	foreach($files as $item) {
		if ($item[0] == '.' OR $item[0] == '_')
			continue;

		if ($checkExclusions && in_array($item, $exclude))
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
	if (!$noul) $result .= '<ul' . cssClass($class_ul) . '>' . variable('nl');

	$isAbsolute = startsWith($folderRelative, ABSOLUTEPATHPREFIX);
	$folderPrefix = $isAbsolute ? '' : variable('path');
	if ($isAbsolute) $folderRelative = substr($folderRelative, strlen(ABSOLUTEPATHPREFIX));
	$folder = $folderPrefix. ($folderRelative ? $folderRelative : (variable('folder') ? '/' . variable('folder') : '/'));

	$filesGiven = false;
	$couldHaveSlashes = isset($settings['could-have-slashes']) && $settings['could-have-slashes'];
	$givenFiles = valueIfSetAndNotEmpty($settings, 'files');
	$inHeader = valueIfSetAndNotEmpty($settings, 'in-header');

	if ($givenFiles) {
		$files = $givenFiles;
		$filesGiven = true;
	} else {
		$files = _skipExcludedFiles(disk_scandir($folder), ['home.php', 'home.html', 'home.md', 'home.tsv']);

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

	//If neigher specified, returns mixed.
	$onlyFiles = valueIfSet($settings, 'list-only-files');
	$onlyFolders = valueIfSet($settings, 'list-only-folders');

	$extensions = valueIfSet($settings, 'add-extension');
	$excludeExtensions = valueIfSet($settings, 'exclude-extensions', []);

	$base = valueIfSet($settings, 'parent-slug', '');
	$noLinks = valueIfSet($settings, 'no-links');
	$blogHeading = valueIfSet($settings, 'blog-heading');

	$section = explode('/', $folderRelative)[1];
	$last = false;

	if (isset($settings['home-link-to-section']) && $settings['home-link-to-section']) {
		$homeBase = $base;
		if ($homeBase == '' && isset($settings['parent-slug-for-home-link'])) $homeBase = $settings['parent-slug-for-home-link'];

		$mainNode = ($section == variable('node')) || startsWith($folderRelative, '/' . variable('section'));
		$result .= replaceItems(variable('nl') . '<li%li-classes%><a href="%url%"%style%%a-classes%><%wrap-in%>%text%</%wrap-in%></a>' . variable('nl'), [
			'li-classes' => cssClass(array_merge($class_li, $mainNode ? ['selected'] : [], ['home-link'])),
			'a-classes' => cssClass($class_link),
			'wrap-in' => $wrapInDivVO ? 'div' : 'u',
			'url' => am_page_url('no-rewrite-safe') . $homeBase,
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
			if ($onlyFolders && !is_dir($folder . $file)) continue;
			if ($onlyFiles && is_dir($folder . $file)) continue;

			$info = pathinfo($file);
			$bits = [$info['filename']]; //TODO: move to files.php
			if (isset($info['extension'])) $bits[] = $info['extension'];

			$file = $bits[0];
			$isDir = false;

			if ($file && $file[0] != '~' && !isset($bits[1]) && !($isDir = disk_is_dir($folder . $file))) {
				if (variable('local')) {
					parameterError('$settings', $settings);
					parameterError('$folder, $file & $bits', [ 'folder' => $folder, 'file' => $file, 'bits' => $bits]);
				}
				continue;
			}

			if ($isDir) {
				$extension = '';
			} else {
				$extension = $extensions ? '.' . $bits[1] : '';
				if (in_array($extension, $excludeExtensions)) continue;
			}
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

		$link = $file; //TODO: affects global peace index - str_replace('index', '', $file);
		if ($filesGiven) {
			$url = variable('url') . $base . $link . '/';
		} else {
			$url = $extensions
				? variable('url') . $base . $link . $extension . '" target="_blank'
				: am_page_url($base . $link) . ($link == '' ? '' : '/');
		}

		$file = _handleSlashes($file, $filesGiven || $couldHaveSlashes, $couldHaveSlashes);
		/*
		TODO: when to reinstate?
		if ($what == 'page') { if (cannot_access_page($file)) continue; }
		else { if (cannot_access($file, 'page')) continue; }
		*/

		$text = humanize($file, $onlySlugForSectionMenu);

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
			am_page_url('no-rewrite-safe'),
			cssClass($thisAClass),	
			'** Back to ' . variable('abbr'));
	}

	if (!$noul) $result .= '</ul>' . variable('2nl');

	$return = isset($settings['return']) ? $settings['return'] : false;
	if ($return) return $result;
	echo $result;
}
