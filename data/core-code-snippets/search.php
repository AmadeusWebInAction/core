<?php
doToBuffering(1);
//variable('default-search', 'yieldmore');

//todo = whitelist & default
$engines = variableOr('searches', main::defaultSearches());

$id = variableOr('page_parameter1', $defaultSearchId = variableOr('default-search', 'amadeusweb'));
$engine = $engines[$id];

foreach ($engines as $slug => $item) {
	echo sprintf('<div class="mb-2 lh-2 white-bg d-inline-block"><a href="%s" class="btn btn-%s">%s%s</a></div>' . NEWLINE,
		searchUrl() . ($defaultSearchId != $slug ? $slug . '/' : ''),
		$slug == $id ? 'warning' : 'light', $item['name'], !$item['description'] ? '' : ' &mdash; ' . $item['description']);
}
echo BRNL . makeLink('edit google search engine', 'https://programmablesearchengine.google.com/controlpanel/overview?cx=' . $engine['code'], EXTERNALLINK);
?>

<style type="text/css">
.error404-wrap .error404 { opacity: .4; }
.gsc-results-wrapper-overlay { z-index: 100002; margin-top: 100px; }
.gsc-control-cse { background-color: transparent; border: none; }
.error404-wrap form { max-width: 100%; }
.gsc-input-box input, .gsc-search-button-v2 svg { line-height: 50px; height: 50px!important; font-size: 40px; }
.gsc-input-box { padding-top: 10px; }
.gsc-input-box input { color: var(--footer-widgets-link, #666); text-align: center; }
.gsc-control-wrapper-cse { background-color: #fff; padding: 20px; border-radius: 15px; }
</style>

<script async src="https://cse.google.com/cse.js?cx=<?php echo $engine['code']; ?>"></script>
<div class="gcse-searchbox"></div>
<div class="gcse-searchresults"></div>
<?php
$result = doToBuffering(2);
doToBuffering(3);
return $result;
