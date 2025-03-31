<div class="container">
<div class="content-box">
	<div class="heading-block border-bottom-0 my-4 text-center">
		<h3><?php echo $title ;?></h3>
	</div>
</div>

<?php
$refA = getSheet(SITEPATH . '/' . $sectionA . '/_section.tsv', 'sno');
$refB = getSheet(SITEPATH . '/' . $sectionB . '/_section.tsv', 'sno');
$sheet = getSheet(SITEPATH . '/' . $section . '/_section.tsv', false);
foreach ($sheet->rows as $item) {
	$slug = $sheet->getValue($item, 'slug');
	if ($singleItem && $slug != $singleItem) continue;

	contentBox($section . '-' . $slug, 'container');

	$sno = $sheet->getValue($item, 'sno');

	$as = explode(', ', str_replace($prefixA, '', $sheet->getValue($item, $sectionA)));
	$bs = explode(', ', str_replace($prefixB, '', $sheet->getValue($item, $sectionB)));
	$link = makeRelativeLink($sno . '.' . $sheet->getValue($item, 'name'), $slug);

	echo '<div style="margin-bottom: 20px;"><h3>' . $link . '</h3><hr>Relates To:' . BRTAG;

	linksOf($as, $refA, humanize($sectionA));
	linksOf($bs, $refB, humanize($sectionB));

	echo '</div>' . NEWLINES2;

	contentBox('end');
}

function linksOf($items, $sheet, $title) {
	echo '<h4>' . $title . ':</h4>' . NEWLINE;
	echo '<ol><li>' . NEWLINE;
	$op = [];

	$hasName = isset($sheet->columns['name']);

	foreach ($items as $sno) {
		$link = $sno;
		if (isset($sheet->group[$sno])) {
			$item = $sheet->group[$sno][0];

			$name = ''; $slug = $sheet->getValue($item, 'slug');
			if ($hasName)
				$name = $sheet->getValue($item, 'name');
			if (!$name)
				$name = humanize($slug);
		
			$link = makeRelativeLink($name, $slug);
		}
		$op[] = '	<h5>' . $link . '</h5>' . NEWLINE;
	}
	echo implode('</li>' . NEWLINE . '<li>', $op);
	echo '</li></ol>' . NEWLINE;
}
?>

</div><!-- .container -->
