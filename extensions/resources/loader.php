<?php
//works best in standalone mode
function runResource($what, $context) {
	//extract($context); //$section, $where
	$file = __DIR__ . '/' . $what . '.php';
	return disk_include($file, $context);
}

function isResourceNode($section, $context) {
	$file = __DIR__ . '/menu.php';
	$items = disk_include($file, $context);
	return isset($items[variable('node')]);
}
