<?php
/******
 * Amadeus' Table Feature
 * v2 - from amadeusweb/code (datatables) - Dec 2024
 * v2.5 - supoprting dossiers (tsv / auto)
 * Trying to support multiple in one page (not tested yet)
 */

//add_foot_hook(featurePath('tables/foot-hook.php'));

variable('calendar-cells', explode(',', '1-1,1-2,1-3,1-4,1-5,1-6,1-7,2-1,'
	. '2-2,2-3,2-4,2-5,2-6,2-7,3-1,3-2,3-3,3-4,3-5,3-6,3-7,'
	. '4-1,4-2,4-3,4-4,4-5,4-6,4-7,5-1,5-2,5-3,5-4,5-5,5-6,5-7'));

function getTableTemplate($name) {
	//return disk_file_get_contents(featurePath('tables/' . $name . '.html'));
}

function _table_row_values($item, $cols, $tsv) {
	//TODO: HIGH: use $tsv for sticking with old code path.. seems buggy
	if (!$tsv) { $r = []; foreach ($cols as $c) $r[$c] = !is_int($item) && $item[$c] ? $item[$c] : ''; return $r; }
	$r = [];
	//parameterError('Table Debugger', [$item, $cols], false); die();
	foreach ($cols as $key => $c) {
		if (is_numeric($key)) $key == $c;

		if (!$item[$c] || (startsWith($key, '__') && !(variable('allow-internal'))))
			$r[$key] = '';
		else if (endsWith($key, '_link') && $key != '_link')
			$r[$key] = _table_link($item, $c);
		else if (endsWith($key, '_md'))
			$r[$key] = renderSingleLineMarkdown($item[$c]);
		else
			$r[$key] = $item[$c];

		if (endsWith($key, '_urlized'))
			$r[str_replace('_urlized', '', $key) . '_humanized'] = humanize($item[$c]);
	}

	return $r;
}

function _table_link($item, $c) {
	$link = $item[$c];
	$text = 'open';

	if (contains($link, 'docs.google.com'))
		$text = 'document';
	else if (contains($link, '/folders/'))
		$text = 'folder';


	return makeLink($text, $link, 'external');
}

//TEST: http://localhost/ravee/aurrrah.com/vra/active/?internal=1
//TEST: http://localhost/amadeus/code/

function add_table($id, $dataFile, $columnList, $template) {
	variable('allow-internal', variable('local') || isset($_GET['internal']));
	$tsv = is_string($dataFile) && endsWith($dataFile, '.tsv');
	if ($columnList == 'auto' || is_string($columnList)) {
		if (!$tsv) { parameterError('TSV Expected', $dataFile, false); die(); }
		$sheet = getSheet($dataFile, false);
		$cols = $columnList == 'auto' ? array_keys($sheet->columns) : explode(', ', $columnList);

		$headingNames = [];
		foreach ($cols as $item) {
			if (startsWith($item, '_')) continue;
			$headingNames[] = humanize(explode('_', $item)[0]);
		}

		$rows = $sheet->rows;
		$columns = $sheet->columns;
	} else {
		//NOTE: Magic columnList can be array of csvs where 2nd is additional cols needed, but not the headers
		$headingNames = is_string($columnList) ? $columnList : $columnList[0];
		$headingNames = explode(', ', $headingNames);

		$columnNames = explode(', ', is_string($columnList) ? $columnList : implode(', ', $columnList));
		$columns = array_map('strtolower', $columnNames);

		$rows = is_string($dataFile) ? json_to_array($dataFile) : $dataFile;
	}
	$headings = implode('</th>' . variable('nl') . '			<th>', $headingNames);

	$isInList = variable('is-in-directory');
	$datatableClass = $isInList ? '' : 'amadeus-table ';

	echo '
	<table id="amadeus-table-' . $id . '" class="' . $datatableClass . 'table table-striped table-bordered" cellspacing="0" width="100%">
	<thead>
		<tr>
			<th>' . $headings . '</th>
		</tr>
	</thead>
	<tbody>
';
	foreach ($rows as $item) {
		$more = isset($item[0]) && $item[0] == '<!--more-->';
		if ($more) { if (variable('is-in-directory')) break; else continue; }
		echo replaceHtml(replaceItems($template, _table_row_values($item, $columns, $tsv), '%'));
	}
	echo '
	</tbody>
</table>
';
}

function _tableHeadingsOnLeft($id, $data) {
	$css = 'amadeus-plain-table headings-on-left table table-striped table-bordered';
	echo variable('nl') . '<table id="amadeus-table-' . $id . '" class="' . $css . '" cellspacing="0" width="100%">' . variable('nl');

	$header = '	<thead><tr class="header"><th width="50%">%th%</th><th class="left">%td%</th></tr></thead><tbody>' . variable('nl');
	$row = '	<tr><th>%th%</th><td>%td%</td></tr>' . variable('nl');

	foreach ($data as $th => $td)
		echo replaceItems(($hdg = startsWith($th, '+')) ? $header : $row, ['th' => $hdg ? substr($th, 1) : $th, 'td' => $td], '%');

	echo '</tbody></table>' . variable('2nl');
}
