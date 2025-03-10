<?php
$cssFile = getFeatureUrl('assets/presentation.css' . version());
?>

<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

		<title><?php title();?> [deck]</title>
		<link href="<?php echo am_var('url'); ?><?php echo am_var('safeName'); ?>-icon.png" rel="icon" />

<?php $url = am_var('app-assets') . 'revealjs/'; ?>
		<link rel="stylesheet" href="<?php echo $url;?>dist/reset.css" />
		<link rel="stylesheet" href="<?php echo $url;?>dist/reveal.css" />
		<link rel="stylesheet" href="<?php echo $url;?>dist/theme/white.css" />

		<!-- Theme used for syntax highlighted code -->
		<link rel="stylesheet" href="<?php echo $url;?>plugin/highlight/monokai.css" />
		<link rel="stylesheet" href="<?php echo $cssFile; ?>" />
		<?php main::analytics(); ?>
	</head>
	<body id="<?php echo am_var('deck-name'); ?>">
		<!-- header thanks to: https://www.raymondcamden.com/2014/04/01/Adding-an-Absolutely-Positioned-Header-to-Revealjs -->
		<header id="topbar" style="position: absolute; top: 10px; z-index:500; line-height: 20px; font-size: 16px; text-align: center; width: 100%;">
			<?php if (!isset($_GET['iframe'])) {?><a id="home-link" href="<?php echo am_var('url'); ?>" class="standard-logo" style="height: 20px; display: inline-block; position: relative; top: 10px;">
				<img src="<?php echo am_var('url'); ?><?php echo am_var('safeName'); ?>-logo.png" alt="<?php echo am_var('name'); ?>" height="20px" style="valign: middle" />
			</a>
			<?php if (!am_var('no-detail-link')) {?><a id="details-link" href="../" style="height: 70px; display: inline-block; color: #fff;">details</a><?php } } ?>
			<span id="hash-id" style="color: #fff; font-size: 16px;">[slide-id]</span>
		</header>

		<div class="reveal">
			<div class="slides">
				<?php if (am_var('video')) {
					echo '<section id="video-invite">';
					echo replace_dictionary(am_var('video-template'), [ 'videoid' => am_var('video') ]);
					echo '</section>';
				}
				
				$op = renderMarkdown(am_var('deck'), [
					'replaces' => ['nodeLink' => am_var_or('nodeLink', am_var('url') . am_var('node') . '/')],
					'wrap-in-section' => true,
					'echo' => false,
				]);
				echo replaceItems($op, ['<hr>' => '</section><section>']);
				?>
			</div>
		</div>

		<script src="<?php echo $url;?>dist/reveal.js"></script>
		<script src="<?php echo $url;?>plugin/notes/notes.js"></script>
		<script src="<?php echo $url;?>plugin/markdown/markdown.js"></script>
		<script src="<?php echo $url;?>plugin/highlight/highlight.js"></script>
		<script>
			// More info about initialization & config:
			// - https://revealjs.com/initialization/
			// - https://revealjs.com/config/
			Reveal.addEventListener('slidechanged', setHashId);

			Reveal.initialize({
				hash: true,

				// Learn about plugins: https://revealjs.com/plugins/
				plugins: [ RevealMarkdown, RevealHighlight, RevealNotes ]
			}).then(setMissingHashIds);

			function setMissingHashIds(event) {
				let slideIndex = 0;
				const slides = Reveal.getSlides();

				slides.forEach(function (slide) {
					slideIndex++;
					if (slide.id) return;
					var hidden = slide.querySelectorAll('input[type=hidden]');
					if (hidden.length) slide.id = hidden[0].value;
					else slide.id = '00' + slideIndex; //abs count
				});

				setHashId(event);
			}

			function setHashId(event) {
				document.getElementById('hash-id').innerText = '# ' + event.currentSlide.id.replaceAll('-', ' ');
			}
		</script>
	</body>
</html>
