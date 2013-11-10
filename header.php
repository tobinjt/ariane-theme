<!DOCTYPE html>

<!--[if lt IE 7 ]> <html class="ie ie6 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]>    <html class="ie ie7 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>    <html class="ie ie8 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9 ]>    <html class="ie ie9 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" <?php language_attributes(); ?>><!--<![endif]-->
<!-- the "no-js" class is for Modernizr. -->

<head id="www-sitename-com" data-template-set="html5-reset-wordpress-theme">
  <meta charset="<?php bloginfo('charset'); ?>">
<?php
  if (is_search()) {
      echo '<meta name="robots" content="noindex, nofollow" />';
  }
?>
  <title>
       <?php echo_title(); ?>
  </title>

  <meta name="description" content="<?php bloginfo('description'); ?>">
  <meta name="author" content="John Tobin">

  <!-- Dublin Core Metadata : http://dublincore.org/ -->
  <meta name="dcterms.title" content="Ariane Tobin Jewellery">
  <meta name="dcterms.subject" content="Ariane Tobin Jewellery">
  <meta name="dcterms.creator" content="John Tobin">
  <meta name="dcterms.rightsHolder" content="Ariane Tobin Jewellery">
  <meta name="dcterms.rights"
    content="Copyright Ariane Tobin Jewellery 2011-<?php echo date("Y");?>. All Rights Reserved.">
  <meta name="dcterms.dateCopyrighted" content="2011-<?php echo date("Y");?>">

  <!-- TODO(johntobin): FAVICON. -->
  <link rel="shortcut icon"
    href="<?php bloginfo('template_directory'); ?>/_/img/favicon.ico">
  <!-- This is the traditional favicon.
     - size: 16x16 or 32x32
     - transparency is OK
     - see wikipedia for info on browser support: http://mky.be/favicon/ -->

  <!-- TODO(johntobin): FAVICON. -->
  <link rel="apple-touch-icon"
    href="<?php bloginfo('template_directory'); ?>/_/img/apple-touch-icon.png">
  <!-- The is the icon for iOS's Web Clip.
     - size: 57x57 for older iPhones, 72x72 for iPads, 114x114 for iPhone4's retina display (IMHO, just go ahead and use the biggest one)
     - To prevent iOS from applying its styles to the icon name it thusly: apple-touch-icon-precomposed.png
     - Transparency is not recommended (iOS will put a black BG behind the icon) -->

  <!-- CSS: screen, mobile & print are all in the same file -->
  <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>">
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<?php
  if (is_singular()) {
    wp_enqueue_script('comment-reply');
  }
  wp_head();
?>
</head>

<body <?php body_class(); ?>>
  <div id="page-wrap">
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
      $skipped_links = array();
      foreach ($links as $url => $text) {
        if (strpos($url, '/') === 0 and $url != '/'
          and is_null(get_page_by_path($url))) {
          // Local page that doesn't exist.  Skip it.
          $skipped_links[$url] = $text;
        } else {
          $new_links[$url] = $text;
        }
      }
      if (count($skipped_links) > 0) {
        error_log('Skipped some non-existent links: '
          . print_r($skipped_links, true));
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
    $padding = str_repeat(' ', 6);
    $span_padding = $padding . str_repeat(' ', 2);
    $link_padding = $span_padding . str_repeat(' ', 2);
    echo $padding, '<div class="menubar">', "\n";
    // Create the menubar.
    foreach ($groups as $class => $links) {
      echo $span_padding, '<span class="', $class, '">', "\n";
      foreach ($links as $url => $text) {
        if ($url == $url_to_highlight) {
          echo $link_padding, '<a href="', $url, '" class="current-url">',
            strtolower($text), '</a>', "\n";
        } else {
          echo $link_padding, '<a href="', $url, '">',
            strtolower($text), '</a>', "\n";
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

  # This assumes that arrays are ordered, which appears to be true.
  $main_links = array(
    '/'               => 'home',
    '/jewellery/'     => 'jewellery',
    '/news/'          => 'news',
    '/about/'         => 'about',
  );
  $icon_links = array(
    'http://www.facebook.com/ArianeTobinJewellery'
      => make_icon_link('facebook-icon.png',    'Facebook icon',    16, 16),
    'https://twitter.com/#!/ArianeTobin'
      => make_icon_link('twitter-icon.png',     'Twitter icon',     16, 16),
    // 'https://plus.google.com/u/0/106979221491924017894/posts'
      // => make_icon_link('google-plus-icon.png', 'Google Plus icon', 16, 16),
    'http://pinterest.com/arianetobin/'
      => make_icon_link('pinterest-icon.png',   'Pinterest icon',   16, 16),
    get_bloginfo('rss2_url')
      => make_icon_link('rss-icon.jpg',         'RSS feed icon',    16, 16),
  );
  make_link_bar(
    array('page-links left-page-links' => $main_links,
          'right-links' => $icon_links),
    '/news/');

  if (is_jewellery_page()) {
    // TODO(johntobin): remove when jewellery is ready.
    if (current_user_can('edit_pages')) {
      $jewellery_links = array(
        '/jewellery/bangles'    => 'bangles',
        '/jewellery/brooches'   => 'brooches',
        '/jewellery/earrings'   => 'earrings',
        '/jewellery/neckpieces' => 'neckpieces',
        '/jewellery/rings'      => 'rings',
      );
      make_link_bar(
        array('page-links left-page-links' => $jewellery_links),
        '/news/');
    }
  }
?>
    </header>

