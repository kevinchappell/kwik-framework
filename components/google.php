<?php
namespace Kwik\Google;

/**
 * Output your analytics tracking code
 * @return [string] script for Google analytics tracking javascript
 * @uses echo \Kwik\Google\analytics(); after <body>
 */
function analytics() {
	$kf_options = get_option( KF_FUNC );
	$tracking_id = esc_js( $kf_options['analytics_id'] );
	if ( empty( $tracking_id ) ) {
		return;
	}

	$tracking_script = <<<EOD
<!-- Analytics Tracking Code -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '$tracking_id', 'auto');
  ga('send', 'pageview');
</script>
EOD;

	return $tracking_script;
}
