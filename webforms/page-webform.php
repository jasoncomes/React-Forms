<?php
/*
Template Name: Webform Template
*/
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>

    <title><?php wp_title('|', true, 'right'); ?> test serv</title>
    
    <script>
        (function(d) {
            var config = {
              kitId: 'hhv1tng',
              scriptTimeout: 3000,
              async: true
          },
          h=d.documentElement,t=setTimeout(function(){h.className=h.className.replace(/\bwf-loading\b/g,"")+" wf-inactive";},config.scriptTimeout),tk=d.createElement("script"),f=false,s=d.getElementsByTagName("script")[0],a;h.className+=" wf-loading";tk.src='https://use.typekit.net/'+config.kitId+'.js';tk.async=true;tk.onload=tk.onreadystatechange=function(){a=this.readyState;if(f||a&&a!="complete"&&a!="loaded")return;f=true;clearTimeout(t);try{Typekit.load(config)}catch(e){}};s.parentNode.insertBefore(tk,s)
        })(document);
    </script>
    <link type="text/css" rel="stylesheet" href="<?php bloginfo('template_url') ?>/style.css" />



    <?php wp_head(); ?>

    <!-- Google Code for Calls from Mobile Device Conversion Page In your html page, add the snippet and call goog_report_conversion when someone clicks on the phone number link or button. --> 
    <script type="text/javascript">
    /* <![CDATA[ */
        goog_snippet_vars = function() {
           var w = window;
           w.google_conversion_id = 967911433;
           w.google_conversion_label = "fBq1COji1FYQidDEzQM";
           w.google_remarketing_only = false;
        }
       // DO NOT CHANGE THE CODE BELOW.
       goog_report_conversion = function(url) {
           goog_snippet_vars();
           window.google_conversion_format = "3";
           var opt = new Object();
           opt.onload_callback = function() {
               if (typeof(url) != 'undefined') {
                    window.location = url;
                }
            }
            var conv_handler = window['google_trackConversion'];
            if (typeof(conv_handler) == 'function') {
                conv_handler(opt);
            }
       }
    /* ]]> */
    </script>
    <script type="text/javascript"
    src="//www.googleadservices.com/pagead/conversion_async.js">
    </script>

    <!-- Facebook Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
            document,'script','//connect.facebook.net/en_US/fbevents.js');

        fbq('init', '236918409989307');
        fbq('track', "PageView");
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=236918409989307&ev=PageView&noscript=1"/></noscript>
    <!-- End Facebook Pixel Code -->

    <script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"5440748"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script><noscript><img src="//bat.bing.com/action/0?ti=5440748&Ver=2" height="0" width="0" style="display:none; visibility: hidden;" /></noscript>

</head>

<body id="webform-page">

    <?php
    while ( have_posts() ) : the_post();   
        the_content();
    endwhile;
    ?>

</body>

<?php wp_footer(); ?>

<!-- START GA -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-35520770-1', 'auto');
  ga('send', 'pageview');

</script>
<!-- END GA -->

</html>
