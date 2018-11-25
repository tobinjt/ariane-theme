<!DOCTYPE html>

<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php
  if (is_search()) {
      echo '<meta name="robots" content="noindex, nofollow" />';
  }
?>
  <meta name="description" content="<?php bloginfo('description'); ?>">
  <meta name="author" content="Ariane Tobin">
  <!-- Dublin Core Metadata : http://dublincore.org/ -->
  <meta name="dcterms.title" content="Ariane Tobin Jewellery">
  <meta name="dcterms.subject" content="Ariane Tobin Jewellery">
  <meta name="dcterms.creator" content="Ariane Tobin">
  <meta name="dcterms.rightsHolder" content="Ariane Tobin Jewellery">
  <meta name="dcterms.rights"
    content="Copyright Ariane Tobin Jewellery 2011-<?php echo date("Y");?>. All Rights Reserved.">
  <meta name="dcterms.dateCopyrighted" content="2011-<?php echo date("Y");?>">

  <link rel="shortcut icon" href="<?php echo get_image_path("favicons/favicon.ico") ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_image_path("favicons/apple-touch-icon.png") ?>">
  <link rel="icon" type="image/png" href="<?php echo get_image_path("favicons/favicon-32x32.png") ?>" sizes="32x32">
  <link rel="icon" type="image/png" href="<?php echo get_image_path("favicons/favicon-16x16.png") ?>" sizes="16x16">
  <link rel="manifest" href="<?php echo get_image_path("favicons/manifest.json") ?>">
  <link rel="mask-icon" href="<?php echo get_image_path("favicons/safari-pinned-tab.svg") ?>">
  <meta name="theme-color" content="#ffffff">

  <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>">
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

  <title><?php echo_title(); ?></title>

<?php
  if (is_single()) {
    wp_enqueue_script('comment-reply');
  }
  wp_head();
  echo MaybeHideCookieLawInfoInFooter();
?>
</head>

<body <?php body_class(); ?>>
<?php echo get_google_analytics_code(); ?>

  <div id="page-wrap">
    <header id="header">
      <div id="title">
        <a href="/" id="title-logo">
          <!-- When mod_pagespeed compresses the logo it creates shadowy boxes
               around the letters and it looks ugly, so disable it.
               The logo has been optimised by running:
               $ convert new-logo.jpg -sampling-factor 4:2:0 -strip \
                   new-logo-optimised.jpg -->
          <img src="<?php echo get_image_path('new-logo-optimised.jpg') ?>"
            class="block aligncenter"
            alt="Ariane Tobin Jewellery logo"
            data-pagespeed-no-transform />
        </a>
      </div>

      <nav>
<?php
  # This assumes that arrays are ordered, which appears to be true.
  $main_links = array(
    '/'               => 'home',
    '/jewellery/'     => 'jewellery',
    '/care/'          => 'care',
    '/news/'          => 'news',
    '/about/'         => 'about',
    '/store/cart/'    => 'basket',
  );
  $icon_links = array(
    'https://www.facebook.com/ArianeTobinJewellery'
      => make_icon_link('facebook.png',    'Facebook icon',    20, 20),
    'https://twitter.com/#!/ArianeTobin'
      => make_icon_link('twitter.png',     'Twitter icon',     20, 20),
    // 'https://plus.google.com/u/0/106979221491924017894/posts'
      // => make_icon_link('google-plus-icon.png', 'Google Plus icon', 20, 20),
    'https://pinterest.com/arianetobin/'
      => make_icon_link('pinterest.png',   'Pinterest icon',   20, 20),
    get_bloginfo('rss2_url')
      => make_icon_link('rss.png',         'RSS feed icon',    20, 20),
  );
  echo make_menu_bar([
    make_link_group(
      array('largest-text left-page-links' => $main_links,
            'float-right' => $icon_links),
      '/news/'),
      ],
      '');

  if (is_jewellery_page()) {
    $jewellery_types_links = array(
      '/jewellery/bangles/'    => 'bangles',
      # '/jewellery/brooches/'   => 'brooches',
      '/jewellery/earrings/'   => 'earrings',
      '/jewellery/necklaces/'  => 'necklaces',
      '/jewellery/rings/'      => 'rings',
    );
    echo make_menu_bar([
      make_link_group(
        array('left-page-links' => $jewellery_types_links), '/jewellery/'),
      wrap_with_tag('span', 'float-right grey',
        'Free delivery on all orders to Ireland'),
      ],
      'larger-text bottom-margin');

    $jewellery_ranges_links = array(
      '/jewellery/cellule/'    => 'cellule',
      '/jewellery/confluence/' => 'confluence',
      '/jewellery/halo/'       => 'halo',
      '/jewellery/laria/'      => 'laria',
      '/jewellery/sentinel/'   => 'sentinel',
      '/jewellery/wave/'       => 'wave',
      '/jewellery/singles/'    => 'singles',
      '/jewellery/archive/'    => 'archive',
    );
    echo make_menu_bar([
      make_link_group(
        array('left-page-links' => $jewellery_ranges_links), '/jewellery/'),
      ],
      'larger-text bottom-margin');
  }
?>

      </nav>
    </header>
