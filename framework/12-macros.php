<?php
variable('upiFormat', 'upi://pay?pa=%id%&amp;pn=%name%&amp;cu=INR');

function runAllMacros($html) {
	if (contains($html, '-snippet%'))
		$html = replaceSnippets($html);

	if (contains($html, '#upi') || contains($html, '%upi'))
		$html = replaceUPIs($html);

	if (contains($html, '%engage-btn'))
		$html = replaceEngageButtons($html);

	if (contains($html, '[youtube]'))
		$html = processYouTubeShortcode($html);

	return $html;
}

function getSnippet($name, $fol = false) {
	if (!$fol) $fol = SITEPATH . '/data/snippets/';
	$ext = disk_one_of_files_exist($fol . $name . '.', 'html, md');
	if (!$ext) return '';
	return replaceSnippets('%' . $name . '-snippet%', [$name . '.' . $ext], $fol);
}

function replaceSnippets($html, $files = false, $fol = false) {
	if (!$fol) $fol = SITEPATH . '/data/snippets/';
	if (!$files) $files = disk_scandir($fol);

	foreach ($files as $file) {
		if ($file[0] == '.') continue;

		$fwoe = replaceItems($file, ['.md' => '', '.html' => '']);
		$ext = disk_one_of_files_exist($fol . $fwoe . '.', 'html, md');
		$key = '%' . $fwoe .'-snippet%';

		if (!contains($html, $key)) continue;
		$op = renderMarkdown($fol . $file, [
			'echo' => false,
			'strip-paragraph-tag' => true,
			'raw' => $ext == 'html',
		]);

		$html = str_replace($key, $op, $html);
	}

	return $html;
}

function replaceEngageButtons($html) {
	$engage = subVariable('node-vars', 'engage');

	if (!$engage) {
		if (variable('local') || isset($_GET['debug'])) parameterError('Node Variable **engage** missing', ['html' => $html]);
		return $html;
	}

	foreach ($engage as $where => $array) {
		$class = $where == 'all' ? ENGAGENODE : ENGAGENODEITEM;
		foreach ($array as $id => $name) {
			$html = str_replace('%engage-btn-' . $id . '%', engageButton($id, $name, $class, true), $html);
		}
	}

	return $html;
}

function replaceUPIs($html) {
	$items = variableOr('upi', []);

	if (empty($items)) {
		if (variable('local') || isset($_GET['debug'])) parameterError('Amadeus Variable for **upi** missing', ['html' => $html]);
		return $html;
	}

	foreach ($items as $key => $item) {
		$replaces = ['id' => $item['id'], 'name' => urlencode($item['name'])];
		$html = replaceItems($html, [
			'#upi-' . $key => replaceVariables(variable('upiFormat'), $replaces),
			'%upi-' . $key . '%' => $item['id'],
			'%upi-' . $key . '-textbox%' => textBoxWithCopyOnClick('UPI ID for Indian Money Transfer (GPay / PhonePe etc):', $item['id']),
		]);
	}

	return $html;
}

function textBoxWithCopyOnClick($lineBefore, $value, $lineAfter = 'Text Copied!', $label = false) {
	$bits = [];
	$bits[] = '<div class="flash flash-green">' . ($label ? '<label>' : '') . $lineBefore . '<br />';

	$bits[] = '<textarea onfocus="this.select(); document.execCommand(\'copy\'); this.parentNode.parentNode.classList.add(\'flash-yellow\'); this.parentNode.nextElementSibling.style.display = \'block\'; " style="text-align: center; width: 90%;" rows="4" readonly>' . $value . '</textarea>';

	if ($label) $bits[] = '</label>';
	$bits[] = '<span style="display: none;">' . $lineAfter . '</span></div>';
	$bits[] = ''; $bits[] = ''; //extra blank lines

	return implode(variable('nl'), $bits);
}

function processYouTubeShortcode($html) {
	return replaceItems($html, [
		'[youtube]' => '<div class="video-container"><iframe width="560" height="315" src="https://www.youtube.com/embed/',
		'[/youtube]' => '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>',
	]);
}
