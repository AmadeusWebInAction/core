<?php

testSnippets();

function testSnippets() {
	contentBox('snippets', 'container');
	$items = [
		'search-corecodesnippet',
		'search-form-coresnippet',
	];

	h2('core2!');
	foreach ($items as $item)
		echo returnLine('%' . $item . '%') . '<hr>';

	contentBox('end');

	contentBox('snippets', 'container');
	h2('links');
	echo _getSnippetPath(false, 'plain') . BRNL;
	echo _getSnippetPath(CORESNIPPET, 'plain') . BRNL;
	echo _getSnippetPath(false, 'code') . BRNL;
	echo _getSnippetPath(CORESNIPPET, 'code') . BRNL;
	contentBox('end');
}
