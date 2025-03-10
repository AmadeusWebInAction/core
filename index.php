<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('SITEPATH', __DIR__ . '/../web');
include_once 'framework/1-entry.php';

variables([
	'node-alias' => '_core',
	'sub-theme' => 'go',
]);

runFrameworkFile('site');
