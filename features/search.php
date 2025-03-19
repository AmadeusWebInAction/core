<?php
//variable('default-search', 'yieldmore');
$canvas = variable('theme') == 'canvas';
if ($canvas) {
	echo '<div class="error404" style="font-size: 12vw;">SEARCH</div>';
} else {
	contentBox('search', 'container');
	h2('Search from one of these Engines', 'amadeus-icon'); 
}

//todo = whitelist & default
$engines = variableOr('searches', main::defaultSearches());

$id = variableOr('page_parameter1', $defaultSearchId = variableOr('default-search', 'amadeusweb'));
$engine = $engines[$id];

foreach ($engines as $slug => $item) {
	echo sprintf('<div class="mb-2 lh-2"><a href="%s" class="btn btn-%s">%s</a>%s</div>' . NEWLINE,
		variable('page-url') . 'search/' . ($defaultSearchId != $slug ? $slug . '/' : ''), $slug == $id ? 'warning' : 'light', $item['name'],
		' &mdash; ' . $item['description']);
}

?>

<style type="text/css">
.error404-wrap .error404 { opacity: .4; }
.gsc-results-wrapper-overlay { z-index: 100002; margin-top: 100px; }
.gsc-control-cse { background-color: transparent; border: none; }
.error404-wrap form { max-width: 100%; padding-bottom: 30px; }
.gsc-input-box input, .gsc-search-button-v2 svg { line-height: 50px; height: 50px!important; font-size: 40px; }
.gsc-input-box { padding-top: 10px; }
.gsc-input-box input { color: var(--footer-widgets-link, #666); text-align: center; }
</style>

<script async src="https://cse.google.com/cse.js?cx=<?php echo $engine['code']; ?>"></script>
<div class="gcse-search"></div>

<?php if (!$canvas) contentBox('end'); ?>
