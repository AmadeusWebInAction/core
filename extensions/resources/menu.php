<?php
$sheetFile = $where . '/_section.tsv';
if (!disk_file_exists($sheetFile)) return ['item-1' => '1. Groups (Missing tsv file)'];

$result = [];
$sheet = getSheet($sheetFile, 'level');
$hasName = isset($sheet->columns['name']);
$topLevels = $sheet->group['1'];

if (!isset($limit)) $limit = 5;

foreach ($topLevels as $ix => $item) {
	if ($ix == $limit) { $result[$section] = '&raquo; ' . (count($topLevels) - $limit) . ' More &hellip;'; break; }
	$name = ''; $slug = $sheet->getValue($item, 'slug');
	if ($hasName)
		$name = $sheet->getValue($item, 'name');
	if (!$name)
		$name = humanize($slug);

	$result[$slug] = $sheet->getValue($item, 'sno') . '. ' . $name;
}

return $result;
