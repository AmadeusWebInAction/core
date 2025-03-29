<div class="container content-box">
					<div class="heading-block border-bottom-0 my-4 text-center">
						<h3><?php echo $title ;?></h3>
					</div>
<?php
$refA = getSheet(SITEPATH . '/' . $sectionA . '/_section.tsv', 'sno');
$refB = getSheet(SITEPATH . '/' . $sectionB . '/_section.tsv', 'sno');
$sheet = getSheet(SITEPATH . '/' . $section . '/_section.tsv', false);
echo '<ol>' . NEWLINE;
foreach ($sheet->rows as $item) {
	$as = explode(', ', str_replace($prefixA, '', $sheet->getValue($item, $sectionA)));
	$bs = explode(', ', str_replace($prefixB, '', $sheet->getValue($item, $sectionB)));
	$link = makeRelativeLink($sheet->getValue($item, 'name'), $sheet->getValue($item, 'slug'));
	echo '<li class="content-box after-content" style="margin-bottom: 20px;">' . $link . '<hr>Relates To:' . BRTAG;
	linksOf($as, $refA, humanize($sectionA));
	linksOf($bs, $refB, humanize($sectionB));
	echo BRTAG . BRTAG . '</li>' . NEWLINES2;
}
echo '</ol>' . NEWLINE;

function linksOf($items, $sheet, $title) {
	echo BRTAG . '<strong>' . $title . ':</strong>' . NEWLINE;
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
		$op[] = '	' . $link . NEWLINE;
	}
	echo implode('</li>' . NEWLINE . '<li>', $op);
	echo '</li></ol>' . NEWLINE;
}

echo '</ol>' . NEWLINES2;
?>
				</div>
			</div>
