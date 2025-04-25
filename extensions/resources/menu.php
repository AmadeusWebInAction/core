<?php
$sheetFile = $where . '/_section.tsv';
if (!disk_file_exists($sheetFile)) return ['item-1' => '1. Groups (Missing tsv file)'];

$result = [];
$sheet = getSheet($sheetFile, 'level');
$hasName = isset($sheet->columns['name']);
$topLevels = $sheet->group['1'];

if (!isset($limit)) $limit = 5;

$wantName = !(in_array($section, variableOr('no-name-in-header-menu', []))
	&& isset($callingFrom) && $callingFrom == 'header-menu');

$node = variable('node');
$hasIntroduction = isset($sheet->columns['introduction']);

foreach ($topLevels as $ix => $item) {
	if ($ix == $limit) { $result[$section . '/#items-list'] = '&raquo; ' . (count($topLevels) - $limit) . ' More &hellip;'; break; }
	$name = ''; $slug = $sheet->getValue($item, 'slug');
	if ($wantName && $hasName)
		$name = $sheet->getValue($item, 'name');
	if (!$name)
		$name = humanize($slug);

	if ($slug == $node && $hasIntroduction)
		variable('top-level-introduction', $sheet->getValue($item, 'introduction'));

	$result[$slug] = $sheet->getValue($item, 'sno') . '. ' . $name;
}

return $result;
