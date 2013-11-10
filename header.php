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
  echo make_link_bar(
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
      echo make_link_bar(
        array('page-links left-page-links' => $jewellery_links),
        '/news/');
    }
  }
?>
    </header>

