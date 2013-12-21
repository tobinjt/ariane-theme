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

  <link rel="shortcut icon"
    href="<?php bloginfo('template_directory'); ?>/_/img/favicon.ico">
  <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>">
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

  <title>
       <?php echo_title(); echo "\n"; ?>
  </title>

<?php
  if (is_single()) {
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
          <a href="/" id="title-name" class="pink">ariane tobin</a> <br/>
          <a href="/" id="title-craft" class="grey">jewellery</a>
        </div>
      </div>

<?php
  # This assumes that arrays are ordered, which appears to be true.
  $main_links = array(
    '/'               => 'home',
    '/jewellery/'     => 'jewellery',
    '/store/cart/'    => 'basket',
    '/news/'          => 'news',
    '/about/'         => 'about',
    '/care/'          => 'care',
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
  echo make_link_bar(
    array('largest-text left-page-links' => $main_links,
          'float-right' => $icon_links),
    '/news/');

  if (is_jewellery_page()) {
    $jewellery_links = array(
      '/jewellery/bangles'    => 'bangles',
      # '/jewellery/brooches'   => 'brooches',
      '/jewellery/earrings'   => 'earrings',
      '/jewellery/necklaces'  => 'necklaces',
      '/jewellery/rings'      => 'rings',
    );
    echo make_link_bar(
      array('largest-text left-page-links' => $jewellery_links),
      '/jewellery/');
  }

  $other_message = <<<OTHER_MESSAGE
    <p class="text-centered larger-text grey">
      Wishing you all a Merry Christmas and a Happy New Year!</p>
OTHER_MESSAGE;
  $jewellery_message = <<<JEWELLERY_MESSAGE
    <p class="text-centered larger-text pink">
      In-stock items will not ship until after the 6th of January.</p>
JEWELLERY_MESSAGE;
  $store_message = <<<STORE_MESSAGE
    <ul class="grey">
      <li>Free registered shipping to Ireland, EU, and USA on all orders over
          €50.</li>
      <li>Free unregistered shipping to Ireland on all orders under €50.</li>
      <li>All taxes and duties are the responsibility of the buyer.</li>
    </ul>
STORE_MESSAGE;

  if (is_store_page()) {
    echo $jewellery_message;
    echo $store_message;
  } elseif (is_jewellery_page()) {
    echo $jewellery_message;
  } else {
    echo $other_message;
  }
?>

    </header>
