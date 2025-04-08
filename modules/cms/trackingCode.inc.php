<?php

/*
 * Set Tracking code for Google-Analystics
 * mm@ssi.at 07.08.2011
 * update 12.09.2014 New Generation for GoogleAnalytics
 * update 20.10.2017 Add GoogleOptimize
 * update 14.10.2022 New Version for Anlaytics
 */
function call_analytics_old($TrackingCode, $OptimizeCode = false)
{
    if ($OptimizeCode) {
        $add_ga = "ga('require', '$OptimizeCode');";
        $add_optimize = "
		<style>.async-hide { opacity: 0 !important} </style>
		<script>(function(a,s,y,n,c,h,i,d,e){s.className+=' '+y;h.start=1*new Date;
		h.end=i=function(){s.className=s.className.replace(RegExp(' ?'+y),'')};
		(a[n]=a[n]||[]).hide=h;setTimeout(function(){i();h.end=null},c);h.timeout=c;
		})(window,document.documentElement,'async-hide','dataLayer',4000,
		{'$OptimizeCode':true});</script>";
    }
    return "
	$add_optimize
	<script type='text/javascript'>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
	
	ga('create', '$TrackingCode', 'auto');
	" . $add_ga . "
	ga('send', 'pageview');
	</script>
	";
}

function call_analytics($TrackingCode, $OptimizeCode = false)
{
    return "
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src='https://www.googletagmanager.com/ns.html?id=$TrackingCode'
    height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
        
	<!-- Google tag (gtag.js) -->
    <script async src='https://www.googletagmanager.com/gtag/js?id=$TrackingCode'></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '$TrackingCode');
    </script>
	";
}

// Function_gerate Google Tags
function call_googletag($trackingcode)
{
    $googletag['header'] = "
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','$trackingcode');</script>
    <!-- End Google Tag Manager -->
    ";

    $googletag['body'] = "
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id=$trackingcode\"
    height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->";

    return $googletag;
}


?>