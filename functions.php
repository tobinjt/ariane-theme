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

  /* echo_title(): outputs the appropriate title.  */
  function echo_title() {
    if (is_tag()) {
      single_tag_title("Tag Archive for &quot;"); echo '&quot; - ';
    } elseif (is_archive()) {
      wp_title(''); echo ' Archive - ';
    } elseif (is_search()) {
      echo 'Search for &quot;' . get_search_query() . '&quot; - ';
    } elseif (is_404()) {
      echo 'Not Found - ';
    } elseif (is_single() || is_page()) {
     $title = wp_title('', False);
     if ($title != '') {
       echo $title, ' - ';
     }
    }
    if (is_home()) {
      bloginfo('name'); echo ' - '; bloginfo('description');
    } else {
      bloginfo('name');
    }
    $paged = get_query_var('paged');
    if ($paged > 1) {
      echo ' - page ' . $paged;
    }
  }

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
?>
