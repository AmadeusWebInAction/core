<?php
addStyle('engage', 'app-static--common-assets');
addScript('engage', 'app-static--common-assets');

variable('_engageButtonFormat', '<a href="javascript: void(0);" class="btn btn-primary btn-%class% toggle-engage" data-engage-target="engage-%id%">%name%</a>');

function engageButton($id, $name, $class, $scroll = false) {
	if ($scroll) $class .= ' engage-scroll';
	$class .= ' btn-fill';
	return replaceItems(variable('_engageButtonFormat'), ['id' => $id, 'name' => $name, 'class' => $class], '%') . variable('nl');
}

//TODO: Make a toggle-more when the md contains <!--more-->
function _renderEngage($name, $raw, $open = false, $echo = true) {
	$id = variableOr('all_page_parameters', variable('node'));
	if (!$open) echo engageButton($id, $name, $class);

	$result = '	<div id="engage-' . $id . '" class="engage content-box" ' . ($open ? '' : 'style="display: none" ') .
		'data-to="' . ($email = variable('email')) . '" data-cc="' . variableOr('assistantEmail', variable('systemEmail')) . '" data-name="' . $name . '">' . variable('nl');

	$replaces = [];
	if (disk_file_exists($note = (AMADEUSCORE . 'data/engage-note.md'))) {
		$replaces['engage-note'] = renderMarkdown($note, ['echo' => false]);
		if (disk_file_exists($note2 = (AMADEUSCORE . 'data/engage-note-above.md')))
			$replaces['engage-note-above'] = renderMarkdown($note2, ['echo' => false]);
		$replaces['email'] = $email;
	}

	$result .= renderMarkdown($raw, ['replaces' => $replaces, 'echo' => false]);

	$result .= '</div>' . variable('nl');
	if (!$echo) return $result;
	echo $result;
}

function _runEngageFromSheet($pageName, $sheetName) {
	$pageName = humanize($pageName);
	$sheet = getSheet($sheetName);
	$contentIndex = $sheet->columns['content'];
	$introIndex = $sheet->columns['section-intro'];
	$introduction = valueIfSet($sheet->values, 'introduction', 'Welcome to <b>' . $pageName . '</b> page of <	b>' . variable('name') . '</b>.');

	//TODO: use faq by category like canvas' FAQ?
	//$items = []; //trying to make as pills in a later version
	$raw = ['<!--engage: SITE //engage--><!--render-processing-->', $introduction, ''];

	$firstSection = true;

	$raw[] = '%engage-note-above%';

	foreach ($sheet->group as $name => $rows) {
		$raw[] = '## ' . $name;
		$raw[] = '';

		$firstRow = true;
		foreach ($rows as $row) {
			if ($firstRow) {
				$raw[] = $row[$introIndex];
				$raw[] = '';
				$firstRow = false;
			}
	
			$line = $row[$contentIndex];
			$raw[] = '* '  . $line;
			//$content[] = ;
		}
	
		$raw[] = '';
	}

	$raw[] = '';
	$raw[] = '%engage-note%';
	
	//$raw = print_r($items, 1); //$raw = renderPills($items); //todo: LATER!
	sectionId('engage-' . urlize($pageName));
	_renderEngage($pageName, implode(variable('nl'), $raw), true);
	section('end');
}
