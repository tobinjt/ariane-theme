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

  <link rel="shortcut icon" href="<?php echo get_theme_image_path("favicons/favicon.ico") ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_theme_image_path("favicons/apple-touch-icon.png") ?>">
  <link rel="icon" type="image/png" href="<?php echo get_theme_image_path("favicons/favicon-32x32.png") ?>" sizes="32x32">
  <link rel="icon" type="image/png" href="<?php echo get_theme_image_path("favicons/favicon-16x16.png") ?>" sizes="16x16">
  <link rel="manifest" href="<?php echo get_theme_image_path("favicons/manifest.json") ?>">
  <link rel="mask-icon" href="<?php echo get_theme_image_path("favicons/safari-pinned-tab.svg") ?>">
  <meta name="theme-color" content="#ffffff">

  <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>">
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

  <title><?php echo get_title(); ?></title>

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
          <img src="<?php echo get_theme_image_path('new-logo-optimised.jpg') ?>"
            class="block aligncenter"
            alt="Ariane Tobin Jewellery logo"
            data-pagespeed-no-transform />
        </a>
      </div>

      <nav>
<?php
  echo make_full_menu_bar();
?>
      </nav>
    </header>
