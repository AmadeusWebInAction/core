<?php
function initializeNodeXt($flavour) {
	disk_include_once(__DIR__ . '/' . $flavour . '.php');
}

$nodeStaticUrl = variable('assets-url') . variable('section') . '/' . variable('node') . '/';
variables([
	'node-static-folder' => NODEPATH . '/assets/',
	'node-static' => $nodeStaticUrl . 'assets/',
]);
