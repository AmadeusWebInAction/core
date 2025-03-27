<?php
/*****
 * v2.1 - Links with Share - added referrer-tracking
 * Adapted from 2021's resources.php
 ***/

$to = variableOr('page_parameter1', false);

$sheet = getSheet('links', 'slug');
$cols = $sheet->columns;

$go = variable('node') == 'go';

if ($go && isset($sheet->sections[$to])) {
	$link = prepareLinks($sheet->sections[$to][0][$cols['goto']]);
	header('Location: ' . $link);
	exit;
}

if ($go) { renderMarkdown('No link defined, pls visit [our links page](%url%/links/).'); return; }

$cols = $sheet->columns;

$canvas = variable('theme') == 'canvas';
if ($canvas) {
	echo '<div class="error404" style="font-size: 12vw;">LINKS</div>';
} else {
	contentBox('links', 'container');
	h2('Choose one of these links', 'amadeus-icon'); 
}

echo '<ol style="background-color: #888; opacity: .9; font-size: 40px; line-height: 50px;">' . NEWLINE;

foreach ($sheet->rows as $item) {
	$text = $item[$cols['text']];

	if ($text == '----') {
		echo '	<li><hr></li>';
		continue;
	}

	$link = $item[$cols['goto']];
	echo prepareLinks(sprintf('	<li><a href="%s">%s</a> &mdash; ', $link, $text) . NEWLINE);

	$goTo = variable('page-url') . 'go/' . $item[$cols['slug']] . '/';
	echo sprintf('<a href="%s">%s</a>', $goTo, 'shortlink') . NEWLINE;

	$share = variable('page-url') . '?share=1&url=' . $goTo;
	$share .= isset($_GET['utm_content']) ? '&by=' . str_replace('referred-by-', '', $_GET['utm_content']) : '';
	echo sprintf(' &mdash; <a href="%s" target="_blank">%s</a></li>', $share, 'shared by me') . NEWLINE;
}

echo '</ol>';

if (!$canvas) contentBox('end');
