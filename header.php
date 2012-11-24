<!DOCTYPE html>

<!--[if lt IE 7 ]> <html class="ie ie6 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]>    <html class="ie ie7 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>    <html class="ie ie8 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9 ]>    <html class="ie ie9 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" <?php language_attributes(); ?>><!--<![endif]-->
<!-- the "no-js" class is for Modernizr. -->

<?php function echo_title() {
  if (function_exists('is_tag') && is_tag()) {
      single_tag_title("Tag Archive for &quot;"); echo '&quot; - ';
  } elseif (is_archive()) {
      wp_title(''); echo ' Archive - ';
  } elseif (is_search()) {
      echo 'Search for &quot;' . wp_specialchars($s) . '&quot; - ';
  } elseif (!(is_404()) && (is_single()) || (is_page())) {
      $title = wp_title('', False);
      if ($title != '') {
          echo $title, ' - ';
      }
  } elseif (is_404()) {
      echo 'Not Found - ';
  }
  if (is_home()) {
      bloginfo('name'); echo ' - '; bloginfo('description');
  } else {
      bloginfo('name');
  }
  if ($paged > 1) {
      echo ' - page ' . $paged;
  }
} ?>

<head id="www-sitename-com" data-template-set="html5-reset-wordpress-theme">

	<meta charset="<?php bloginfo('charset'); ?>">

	<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<?php if (is_search()) { ?>
	<meta name="robots" content="noindex, nofollow" />
	<?php } ?>

	<title>
		   <?php echo_title(); ?>
	</title>

	<meta name="title" content="<?php echo_title(); ?>">
	<meta name="description" content="<?php bloginfo('description'); ?>">

	<meta name="google-site-verification" content="">
	<!-- Speaking of Google, don't forget to set your site up: http://google.com/webmasters -->

	<meta name="author" content="John Tobin">
	<meta name="Copyright" content="Copyright Ariane Tobin Jewellery 2012. All Rights Reserved.">

	<!-- Dublin Core Metadata : http://dublincore.org/ -->
	<meta name="dcterms.title" content="Ariane Tobin Jewellery">
	<meta name="dcterms.subject" content="Ariane Tobin Jewellery">
	<meta name="dcterms.creator" content="John Tobin">

	<!--  Mobile Viewport meta tag
	j.mp/mobileviewport & davidbcalhoun.com/2010/viewport-metatag
	device-width : Occupy full width of the screen in its current orientation
	initial-scale = 1.0 retains dimensions instead of zooming out if page height > device height
	maximum-scale = 1.0 retains dimensions instead of zooming in if page width < device width -->
	<!-- Uncomment to use; use thoughtfully!
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	-->

    <!-- XXX FAVICON. -->
	<link rel="shortcut icon" href="<?php bloginfo('template_directory'); ?>/_/img/favicon.ico">
	<!-- This is the traditional favicon.
		 - size: 16x16 or 32x32
		 - transparency is OK
		 - see wikipedia for info on browser support: http://mky.be/favicon/ -->

    <!-- XXX FAVICON. -->
	<link rel="apple-touch-icon" href="<?php bloginfo('template_directory'); ?>/_/img/apple-touch-icon.png">
	<!-- The is the icon for iOS's Web Clip.
		 - size: 57x57 for older iPhones, 72x72 for iPads, 114x114 for iPhone4's retina display (IMHO, just go ahead and use the biggest one)
		 - To prevent iOS from applying its styles to the icon name it thusly: apple-touch-icon-precomposed.png
		 - Transparency is not recommended (iOS will put a black BG behind the icon) -->

	<!-- CSS: screen, mobile & print are all in the same file -->
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>">

	<!-- all our JS is at the bottom of the page, except for Modernizr. -->
    <!-- XXX UPGRADE Modernizr. -->
	<script src="<?php bloginfo('template_directory'); ?>/_/js/modernizr-1.7.min.js"></script>

	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

	<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>

	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

	<div id="page-wrap"><!-- not needed? up to you: http://camendesign.com/code/developpeurs_sans_frontieres -->

		<header id="header">
            <table id="title">
                <tr>
                    <td>
                        <a href="<?php echo get_option('home'); ?>/">
                            <img width="123" height=150" src="<?php bloginfo('template_directory'); ?>/images/logo.jpg" alt="<?php bloginfo('name'); ?> logo" />
                        </a>
                    </td>
                    <td id="title-text">
                        <span>
                            <a href="<?php echo get_option('home'); ?>/">
                                <?php echo strtolower(get_bloginfo('name')); ?>
                            </a>
                        </span>
                    </td>
                </tr>
            </table>

            <div id="menubar">
                <span id="internal-links">
<?php
    /* make_link_bar: outputs a bar of links into the page.
     * Args:
     *   $links: an array of url -> link text.
     *   $padding: padding at the start of each line.
     *   $default_url: the URL to use if the current URL is not in $links.
     */
    function make_link_bar($links, $padding, $default_url) {
        $current_url = $_SERVER['REQUEST_URI'];
        if (! isset($links[$current_url])) {
            $current_url = $default_url;
        }
        if (is_404()) {
            # Don't highlight any link for error pages
            $current_url = '/qwertyasdf';
        }
        foreach ($links as $url => $text) {
            if ($url == $current_url) {
                echo $padding, '<a href="', $url, '" class="current-url">', $text, '</a>', "\n";
            } else {
                echo $padding, '<a href="', $url, '">', $text, '</a>', "\n";
            }
        }
    }

    $main_links = array(
        '/',               = > 'Home',
        '/jewellery/',     = > 'Jewellery',
        // '/buy-online/', = > 'Buy Online',
        '/blog/',          = > 'Blog',
        '/about/',         = > 'About',
        '/contact/',       = > 'Contact',
    );
    # Padding to make the HTML indent properly.
    $padding = '                    ';
    make_link_bar($main_links, $padding, '/blog/');
?>
                </span>

                <span id="external-links">
                    <!-- TODO: make the images greyed out until hovered over.  Notes: jQuery(selector).fadeTo(speed, opacity);  maybe a javascript trigger to do and undo it on hover?  -->
                    <a href="http://www.facebook.com/ArianeTobinJewellery"><img width="16" height="18" src="<?php bloginfo('template_directory'); ?>/images/facebook-icon.png" alt="Facebook icon" /></a>
                    <a href="https://twitter.com/#!/ArianeTobin"><img width="16" height="18" src="<?php bloginfo('template_directory'); ?>/images/twitter-icon.png" alt="Twitter icon" /></a>
                    <a href="https://plus.google.com/u/0/106979221491924017894/posts"><img width="16" height="16" src="<?php bloginfo('template_directory'); ?>/images/google-plus-icon.png" alt="Google Plus icon" /></a>
                    <a href="http://pinterest.com/arianetobin/"><img width="16" height="16" src="<?php bloginfo('template_directory'); ?>/images/pinterest-icon.png" alt="Pinterest icon" /></a>
                    <!-- <a href="ETSY"><img width="16" height="16" src="XXX" /></a> -->
                    <a href="<?php bloginfo('rss2_url'); ?>"><img width="16" height="16" src="<?php bloginfo('template_directory'); ?>/images/rss-icon.jpg" alt="RSS feed icon" /></a>
                </span>
            </div>
		</header>

