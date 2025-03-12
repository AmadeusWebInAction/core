<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

		<title><?php echo title('params-only') . ' [' . title(true) . ']';?></title>
		<link href="<?php echo variable('url'); ?><?php echo variable('safeName'); ?>-icon.png" rel="icon" />

<?php $url = variable('app-static') . 'revealjs/'; ?>
		<link rel="stylesheet" href="<?php echo $url;?>dist/reset.css" />
		<link rel="stylesheet" href="<?php echo $url;?>dist/reveal.css" />
		<link rel="stylesheet" href="<?php echo $url;?>dist/theme/white.css" />

		<!-- Theme used for syntax highlighted code -->
		<link rel="stylesheet" href="<?php echo $url;?>plugin/highlight/monokai.css" />
		<?php cssTag(asset_url('%app-common-assets%presentation2.css') . version()); ?>
		<?php main::analytics(); ?>
	</head>
	<body id="<?php echo variable('all_page_params'); ?>">
		<!-- header thanks to: https://www.raymondcamden.com/2014/04/01/Adding-an-Absolutely-Positioned-Header-to-Revealjs -->
		<header id="topbar" class="box-shadow">
			<?php if (!isset($_GET['iframe'])) {
				echo concatSlugs(['<a id="home-link" href="', variable('url'), variable('all_page_parameters'), '/" class="standard-logo">',
					variable('nl'), '				<img src="', replaceVariables('%url%%safeName%-icon.png" ', 'url, safeName'),
					'alt="', variable('name'), '" height="30px" /></a>', variable('nl') ], '');
			}?>
			<span id="hash-id" style="color: #fff; font-size: 16px;">[slide-id]</span>
		</header>

		<div class="reveal">
			<div class="slides">
<section>
				<?php echo replaceItems(variable('deck'), ['<hr>' => variable('nl') . '</section><section>' . variable('nl')]); ?>
<section>

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
