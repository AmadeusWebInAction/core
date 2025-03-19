<?php
/****
 * SHARER (AmadeusWeb.com feature) - version 2.1 - Mar 2025
 * 
 * TODO:
 *  	* pass the utm params through and have a way of posting to self
 *  	* ui for inclusion in canvas theme
 * Based on archives.yieldmore.org/go/share/
 ****/

if (isset($_GET['share'])) {
	//read_seo_info();
	$for = $url = $_GET['url'];
	$url .= '?utm_source=%source%';
	$url .= isset($_GET['campaign']) && $_GET['campaign'] ? '&utm_campaign=' . $_GET['campaign'] : '';
	$url .= isset($_GET['by']) && $_GET['by'] ? '&utm_content=referred-by-' . strtolower($_GET['by']) : '';

	contentBox('share', 'after-content col-12');

	$logo2x = siteOrNetworkOrAppStatic(variable('safeName') . '-logo@2x.png');
	$home = concatSlugs(['<a href="', pageUrl(), '"><img src="', $logo2x, '" class="img-fluid img-max-',
		variableOr('footer-logo-max-width', '500'), '" alt="', variable('name'), '" /></a><br />'], '');
	echo $home . BRNL;

	h2('Hotlinks for Analytics Tracking');
	echo 'Click any label / textbox to copy it\'s link<br />and <b>share on that platform</b> (email / whatsapp / linkedin etc):';
	echo textBoxWithCopyOnClick('tracker without source', $for, 'copied plain link (no tracker)', true) . '<hr />';

	$sources = ['whatsapp', 'instagram', 'facebook', 'email', 'linkedin'];
	foreach ($sources as $source) echo textBoxWithCopyOnClick($source, str_replace('%source%', $source, $url), 'copied ' . $source . ' link!', true) . '<hr />';

	_credits();
	contentBox('end');
} else { ?>
<section id="amadeus-share" class="container amadeus-silent-feature" style="text-align: center; padding-top: 30px;">
	<?php echo featureHeading('share-form');?>
	<form action="<?php echo variable('url') ?>" target="_blank">
		<input type="hidden" name="share" value="1" />
		<input type="hidden" name="url" value="<?php echo $_SERVER['SERVER_NAME'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0]; ?>" />
		<input style="width: 100%; margin-bottom: 10px;" type="text" name="campaign" placeholder="campaign (if known)" value="<?php echo isset($_GET['utm_campaign']) ? $_GET['utm_campaign'] : ''; ?>" /><br />
		<input style="width: 100%; margin-bottom: 10px;" type="text" name="by" placeholder="your name" value="<?php echo isset($_GET['utm_content']) ? str_replace('referred-by-', '', $_GET['utm_content']) : ''; ?>" /><br />
		<input style="width: 100%;" type="submit" value="Share This Page" />
	</form>
</section>
<?php } ?>
