<?php
function seo_info() {
	$item = variable('current_page');
	if (!$item) return;

	echo '<section id="seo-info" class="container" style="padding-top: 30px;">' . variable('nl');
	echo featureHeading('seo');

	$fmt = '<p><h4>%s</h4>%s</p>' . variable('nl');

	$cols = ['about', 'description', 'keywords'];
	foreach ($cols as $col) {
		$field = isset($item[$col]) ? $item[$col] : false;
		if ($field) echo sprintf($fmt, ($col != 'about' ? 'SEO ' : '') . humanize($col), $field);
	}

	echo variable('nl') . '</section>' . variable('nl');
}

function seo_tags($return = false) {
	$fmt = '	<meta name="%s" content="%s">';
	$ogFmt = '	<meta property="%s" content="%s">';

	variable('generator', 'Amadeus Web Builder / CMS at amadeusweb.com');
	$op = [];

	foreach (['generator', 'description', 'keywords', 'og:image', 'og:title', 'og:description', 'og:keywords', 'og:url', 'og:type', 'fb:app_id'] as $key)
		if ($val = variable($key)) $op[] = sprintf(startsWith($key, 'og:') || startsWith($key, 'fb:') ? $ogFmt : $fmt, $key, replaceVariables($val));

	$op = implode(variable('nl'), $op);
	if ($return) return $op;
	echo $op;
}
