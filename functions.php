<?php
  // Translations can be filed in the /languages/ directory
  load_theme_textdomain( 'html5reset', TEMPLATEPATH . '/languages' );

  $locale = get_locale();
  $locale_file = TEMPLATEPATH . "/languages/$locale.php";
  if ( is_readable($locale_file) )
   require_once($locale_file);

  // Add RSS links to <head> section
  add_theme_support( 'automatic-feed-links' );

  // Load Javascript libraries.
  wp_enqueue_script('jquery');
  wp_register_script('modernizr',
     '//cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.min.js',
     false);
  wp_enqueue_script('modernizr');

  // Clean up the <head>
  function removeHeadLinks() {
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
  }
  add_action('init', 'removeHeadLinks');
  remove_action('wp_head', 'wp_generator');

  if (function_exists('register_sidebar')) {
    register_sidebar(array(
      'name' => __('Sidebar Widgets','html5reset' ),
      'id'   => 'sidebar-widgets',
      'description'   => __( 'These are widgets for the sidebar.','html5reset' ),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget'  => '</div>',
      'before_title'  => '<h2>',
      'after_title'   => '</h2>'
    ));
  }

  // Add 3.1 post format theme support.
  add_theme_support('post-formats',
    array('aside', 'gallery', 'link', 'image', 'quote', 'status', 'audio',
          'chat', 'video'));

  // Remove the version strings from CSS and Javascript.  Found by searching
  // for "wordpress remove query strings from static resources".
  function _remove_script_version($src){
    $parts = explode('?', $src);
    return $parts[0];
  }
  add_filter('script_loader_src', '_remove_script_version', 15, 1);
  add_filter('style_loader_src', '_remove_script_version', 15, 1);

?>
