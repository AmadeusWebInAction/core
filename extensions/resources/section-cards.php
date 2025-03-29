<div class="container content-box">
	<div class="heading-block border-bottom-0 my-4 text-center">
		<h3><?php echo $title ;?></h3>
	</div>

	<!-- Categories
	============================================= -->
	<div class="row course-categories mb-4">

<?php
addStyle('course', 'app-themes--canvas/demos/course');

//name_urlized	about	tags	sno	uid	icon-class	color
$tpl = '
		<div class="col-lg-4 col-sm-3 col-6 mt-4">
			<div class="card hover-effect">
				<img class="card-img" style="border-radius: 15px;" src="%network-url%resource-images/%slug%.jpg" alt="%name%">
				<a href="%url%%slug%/" class="card-img-overlay rounded p-0" style="background-color: rgba(216,216,216,0.6);">
					<span style="text-shadow: 2px 2px #41716A; font-size: 18px; font-weight: bold;"><i style="font-size: 46px; color: cyan;" class="%icon-class%"></i>%name%</span>
				</a>
			</div>
		</div>
';

$sheet = getSheet(SITEPATH . '/' . $section . '/_section.tsv', false);

foreach ($sheet->rows as $item) {
	$replace = [
		'url' => variable('page-url'),
		'network-url' => getHtmlVariable('network-url'),
		'slug' => $slug = $sheet->getValue($item, 'slug'),
		'name' => humanize($slug),
		'icon-class' => $sheet->getValue($item, 'icon_class'),
	];
	echo replaceItems($tpl, $replace, '%');
}
?>
	</div>
</div>
