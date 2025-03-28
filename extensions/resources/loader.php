<?php
//works best in standalone mode
function runResource($what, $context) {
	//extract($context); //$section, $where
	$file = __DIR__ . '/' . $what . '.php';
	return disk_include($file, $context);
}

function isResourceNode($section, $context) {
	return false; //todo
	$file = __DIR__ . '/menu.php';
	return disk_include($file, $context);
	return false;
}