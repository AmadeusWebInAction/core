<?php
$param1 = variable('page_parameter1');
$aliases = variableOr('page-aliases', []);
if (array_key_exists($param1, $aliases))
	$param1 = $aliases[$param1];

variable('page_parameter1_safe', $param1);

$file = $param1 && in_array($param1, variable('standalone-pages'))
	? $param1 . '/home.php' : (($param1 ? $param1 : 'home'). '.md');

variables([
	'file' => NODEPATH . '/' . $file,
	'is-standalone-section' => true,
	'no-page-menu' => !!$param1,
]);

$nodeStaticUrl = variable('assets-url') . variable('section') . '/' . variable('node') . '/';
variables([
	'node-static-folder' => NODEPATH . '/assets/',
	'node-static' => $nodeStaticUrl . 'assets/',
]);

function standalone_menu_items($callingFrom) {
	$items = textToList(disk_file_get_contents(NODEPATH . '/' . variable('page_parameter1_safe') . '/_items.txt'));
	$r = []; $prefix = $callingFrom != 'section-check' ? variable('node') . '/' . variable('page_parameter1_safe') . '/' : '';
	foreach ($items as $ix => $item)
		$r[$prefix . urlize($item)] = ($ix + 1) . '. ' . $item;

	return $r;
}

function standalone_item_siblings($sheet, $param2) {
	$paramName = variable('standalone_parameter_name');
	$paramHeading = humanize($paramName);

	$byParam = arrayGroupBy($sheet->rows, $sheet->columns[$paramHeading]);
	$name = humanize($param2);
	$param = isset($byParam[$name]) ? $byParam[$name][0][$sheet->columns[$paramHeading]] : false;
	variable('this_' . $paramName, $param ? $param : $paramHeading . ' "' . $param2 . '" not found!');
	return $param ? $sheet->group[$param] : [];
}

function standalone_2ndlevel_menu() {
	$paramName = variable('standalone_parameter_name');
	$paramHeading = humanize($paramName);

	$param1 = variable('page_parameter1_safe');
	$param2 = variable('page_parameter2');

	if (!in_array($param1, variable('standalone-pages')) || !$param2) return;

	$sheet = collateNetworkSheets();
	$items = $param2 == 'all' ? $sheet->rows
		: ($param1 == $paramName ? $sheet->group[humanize($param2)] : standalone_item_siblings($sheet, $param2));
	$title = $param2 == 'all' ? 'All' : ($param1 == $paramName ? humanize($param2) : variable('this_' . $paramName));

	contentBox('node-items', 'box-like-list container');
	h2(variable('node-2ndLevel-title') . ' in "' . $title . ':"');
	echo '<ol>' . NEWLINE;
	foreach ($items as $item)
		echo '<li>' . getItemLink($item, $sheet) . '</li>' . NEWLINE;

	echo '</ol>' . NEWLINES2;
	contentBox('end');
}
