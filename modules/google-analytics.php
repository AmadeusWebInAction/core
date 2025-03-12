<?php
if (!variable('google-analytics') || variable('local')) return;
if (hasVariable('live') && variable('live') === false) return;
//TODO: How to support array so that promotions can have advertiser's anaytics. Or shall we give readonly access to all via advertisers@yieldmore.org?
?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo variable("google-analytics"); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?php echo variable("google-analytics"); ?>');
</script>
