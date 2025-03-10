<?php
if (!am_var('google-analytics') || am_var('local')) return;
if (has_var('live') && am_var('live') === false) return;
//TODO: How to support array so that promotions can have advertiser's anaytics. Or shall we give readonly access to all via advertisers@yieldmore.org?
?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo am_var("google-analytics"); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?php echo am_var("google-analytics"); ?>');
</script>
