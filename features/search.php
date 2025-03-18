<?php
//variable('default-search', 'yieldmore');
contentBox('search', 'container');
h2('Search from one of these Engines', 'amadeus-icon'); 

//todo = whitelist & default
$engines = variableOr('searches', main::defaultSearches());

$id = variableOr('page_parameter1', $defaultSearchId = variableOr('default-search', 'amadeusweb'));
$engine = $engines[$id];

foreach ($engines as $slug => $item) {
	echo sprintf('<div class="mb-2 lh-2"><a href="%s" class="btn btn-%s">%s</a>%s</div>' . NEWLINE,
		variable('page-url') . 'search/' . ($defaultSearchId != $slug ? $slug . '/' : ''), $slug == $id ? 'success' : 'primary', $item['name'],
		' &mdash; ' . $item['description']);
}

?>

<script async src="https://cse.google.com/cse.js?cx=<?php echo $engine['code']; ?>"></script>
<div class="gcse-search"></div>

<?php contentBox('end'); ?>
