<?php
if (!am_var('ChatraID') || am_var('local')) return;
if (has_var('live') && am_var('live') === false) return;
?>
<!-- Chatra {literal} -->
<script>
    (function(d, w, c) {
        w.ChatraID = '<?php echo am_var("ChatraID"); ?>';
        var s = d.createElement('script');
        w[c] = w[c] || function() {
            (w[c].q = w[c].q || []).push(arguments);
        };
        s.async = true;
        s.src = 'https://call.chatra.io/chatra.js';
        if (d.head) d.head.appendChild(s);
    })(document, window, 'Chatra');
</script>
<!-- /Chatra {/literal} -->
