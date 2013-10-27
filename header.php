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
  <!-- <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> -->

<?php if (is_search()) { ?>
  <meta name="robots" content="noindex, nofollow" />
<?php } ?>

  <title>
       <?php echo_title(); ?>
  </title>

  <meta name="description" content="<?php bloginfo('description'); ?>">

  <meta name="google-site-verification" content="">
  <!-- Speaking of Google, don't forget to set your site up: http://google.com/webmasters -->

  <meta name="author" content="John Tobin">

  <!-- Dublin Core Metadata : http://dublincore.org/ -->
  <meta name="dcterms.title" content="Ariane Tobin Jewellery">
  <meta name="dcterms.subject" content="Ariane Tobin Jewellery">
  <meta name="dcterms.creator" content="John Tobin">
  <meta name="dcterms.rightsHolder" content="Ariane Tobin Jewellery">
  <meta name="dcterms.rights"
    content="Copyright Ariane Tobin Jewellery 2012-2014. All Rights Reserved.">
  <meta name="dcterms.dateCopyrighted" content="2012-2014">

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

  <?php if (is_singular()) {
    wp_enqueue_script('comment-reply');
  } ?>

  <?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

  <div id="page-wrap"><!-- not needed? up to you: http://camendesign.com/code/developpeurs_sans_frontieres -->

    <header id="header">
      <div id="title">
        <div id="title-text">
          <a href="/" id="title-pink">ariane tobin</a> <br/>
          <a href="/" id="title-grey">jewellery</a>
        </div>
      </div>

<?php
  /* get_current_url: returns the URL. */
  function get_current_url() {
    return $_SERVER['REQUEST_URI'];
  }
  /* is_jewellery_page: is the current page a jewellery page?  */
  function is_jewellery_page() {
    return (strpos(get_current_url(), '/jewellery') === 0);
  }

  /* make_link_bar: outputs a bar of links into the page.
   * Args:
   *   $initial_groups: an array(css-class -> array(url -> link-text)).
   *   $default_url: the URL to use if the current URL is not in $initial_groups.
   *                 Useful to make the blog link be highlighted for blog posts.
   */
  function make_link_bar($initial_groups, $default_url) {
    // Filter out invalid URLs.
    $groups = array();
    foreach ($initial_groups as $class => $links) {
      $new_links = array();
      foreach ($links as $url => $text) {
        if (strpos($url, '/') === 0 and $url != '/'
          and is_null(get_page_by_path($url))) {
          // Local page that doesn't exist.  Skip it.
        } else {
          $new_links[$url] = $text;
        }
      }
      if (count($new_links) > 0) {
        $groups[$class] = $new_links;
      }
    }

    // Find the URL to highlight.
    $current_url = get_current_url();
    $url_to_highlight = $default_url;
    foreach ($groups as $links) {
      foreach ($links as $url => $text) {
        $pattern = rtrim($url, '/');
        # This assumes that if the URLs overlap the most specific will be last.
        # We look for matches at the start of the string.
        if (($pattern != '' and strpos($current_url, $pattern) === 0)
            or $url == $current_url) {
          $url_to_highlight = $url;
        }
      }
    }
    if (is_404()) {
      # Don't highlight any link for error pages
      $url_to_highlight = '/qwertyasdf';
    }

    # Padding to make the HTML indent properly.
    $padding = str_repeat(' ', 12);
    $span_padding = $padding . str_repeat(' ', 4);
    $link_padding = $span_padding . str_repeat(' ', 4);
    echo $padding, '<div class="menubar">', "\n";
    // Create the menubar.
    foreach ($groups as $class => $links) {
      echo $span_padding, '<span class="', $class, '">', "\n";
      foreach ($links as $url => $text) {
        if ($url == $url_to_highlight) {
          echo $link_padding, '<a href="', $url, '" class="current-url">', strtolower($text), '</a>', "\n";
        } else {
          echo $link_padding, '<a href="', $url, '">', strtolower($text), '</a>', "\n";
        }
      }
      echo $span_padding, '</span>', "\n";
    }
    echo $padding, '</div>', "\n";
  }

  function make_icon_link($file, $alt, $width, $height) {
    return '<img class="greyscale"' .
      ' width="' . round($width * 1.414) . '"' .
      ' height="' . round($height * 1.414) . '"' .
      ' src="' .  get_bloginfo('template_directory') . '/images/' .  $file . '"' .
      ' alt="' . $alt . '" />';
  }

  # TODO: get this list automatically like we do for jewellery.
  # This assumes that arrays are ordered, which appears to be true.
  $main_links = array(
    '/'               => 'home',
    '/jewellery/'     => 'jewellery',
    // '/buy-online/' => 'Buy Online',
    '/news/'          => 'news',
    '/about/'         => 'about',
  );
  // TODO: make the images greyed out until hovered over.  Notes:
  // jQuery(selector).fadeTo(speed, opacity);  maybe a javascript trigger to
  // do and undo it on hover?
  $icon_links = array(
    'http://www.facebook.com/ArianeTobinJewellery'            => make_icon_link('facebook-icon.png',    'Facebook icon',    16, 16),
    'https://twitter.com/#!/ArianeTobin'                      => make_icon_link('twitter-icon.png',     'Twitter icon',     16, 16),
    // 'https://plus.google.com/u/0/106979221491924017894/posts' => make_icon_link('google-plus-icon.png', 'Google Plus icon', 16, 16),
    'http://pinterest.com/arianetobin/'                       => make_icon_link('pinterest-icon.png',   'Pinterest icon',   16, 16),
    // "ETSY"><img width="16" height="16" src="XXX" /></a>
    get_bloginfo('rss2_url')                                  => make_icon_link('rss-icon.jpg',         'RSS feed icon',    16, 16),
  );
  make_link_bar(array('page-links left-page-links' => $main_links,
            'right-links' => $icon_links),
         '/news/');

  if (is_jewellery_page()) {
    // These will be displayed on the right, and filtered out of the left
    // links.
    $special_jewellery_links = array(
      '/jewellery/solo/'        => 'Solo',
      '/jewellery/commissions/' => 'Commissions',
    );
    $main_jewellery_page = get_page_by_path('/jewellery/');
    $jewellery_query =
      array('child_of'    => $main_jewellery_page->ID,
         'post_type'   => 'page',
      );
    // Admin users can see every page, others can only see published pages.
    if (current_user_can('edit_pages')) {
      $jewellery_query['post_status'] = 'publish,draft,private';
    } else {
      $jewellery_query['post_status'] = 'publish';
    }
    $jewellery_pages = get_pages($jewellery_query);
    $jewellery_links = array();
    $special_jewellery_links_that_exist = array();
    foreach ($jewellery_pages as $page) {
      $url = '/' . get_page_uri($page->ID) . '/';
      if (array_key_exists($url, $special_jewellery_links)) {
        $special_jewellery_links_that_exist[$url] =
          $special_jewellery_links[$url];
      } else {
        $jewellery_links[$url] = $page->post_title;
      }
    }
    make_link_bar(array('page-links left-page-links' => $jewellery_links,
                        'right-links page-links right-page-links' => $special_jewellery_links_that_exist),
                  '/news/');
  }
?>
    </header>

