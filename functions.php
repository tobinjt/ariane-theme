<?php
  // Requires PHP 7.0 or greater.
  declare(strict_types=1);

  // TODO: when we're running PHP 7.1 or later use 'void' return type where
  // appropriate.

  require_once('src/Header.php');
  require_once('src/JewelleryGrid.php');
  require_once('src/JewelleryPage.php');
  require_once('src/MultipleImageSupport.php');
  require_once('src/StoreClosingTimes.php');
  require_once('src/Urls.php');
  require_once('src/WordpressConfiguration.php');

  // Send errors to browser on dev site for easier debugging.
  if (is_dev_website()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
  }

  /* StyleWrapShortcode: Wrap a div with a style around content.
   * This *must* be used in the enclosing form.
   * TODO: is this used?
   * Args (names are ugly but Wordpress-standard):
   *  $atts: an associative array of attributes, or an empty string if no
   *    attributes are given.
   *  $content: the enclosed content (if the shortcode is used in its enclosing
   *    form)
   *  $tag: the shortcode tag, useful for shared callback functions
   * Returns:
   *  string, the HTML to insert in the page (Wordpress does that
   *    automatically).
   */
  function StyleWrapShortcode(array $atts, string $content,
                              string $tag): string {
    $attrs = shortcode_atts(
      array(
        'class' => '',
        'id' => '',
      ),
      $atts);
    $div_parts = array('<div');
    if ($attrs['class'] != '') {
      $div_parts[] = 'class="' . $attrs['class'] . '"';
    }
    if ($attrs['id'] != '') {
      $div_parts[] = 'id="' . $attrs['id'] . '"';
    }
    $div_parts[] = '>';
    $full_div = implode(' ', $div_parts);

    // Wordpress will sometimes add a </p> after the shortcode.
    $stripped_content = preg_replace('/^\ *<\/p>/', '', $content);
    $expanded_content = do_shortcode($stripped_content);
    return <<<END_OF_DIV
{$full_div}
  {$expanded_content}
</div>
END_OF_DIV;
  }

  // Configure Wordpress.
  // Remove unnecessary resources that Wordpress or plugins include in every
  // page.

  // Disable comment feeds.  __return_false is a Wordpress function that returns
  // false to make filters easier.
  add_filter('feed_links_show_comments_feed', '__return_false');

  // Clean up the <head>
  function removeHeadLinks() {
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    // Remove automatically generated shortlink.
    remove_action('wp_head', 'wp_shortlink_wp_head');
  }
  add_action('init', 'removeHeadLinks');

  add_filter('script_loader_src', 'remove_script_version', 15, 1);
  add_filter('style_loader_src', 'remove_script_version', 15, 1);

  // Stop jquery-migrate being loaded.  jQuery depends on it, so the jQuery deps
  // need to be changed too.
  function blockJqueryMigrate(WP_Scripts $scripts) {
    $data = $scripts->query('jquery');
    if (!$data) {
      return;
    }
    $data->deps = array_diff($data->deps, array('jquery-migrate'));
  }
  add_action('wp_default_scripts', 'blockJqueryMigrate');

  // Stop wp-embed being loaded.  I don't know why this has to be triggered in
  // wp_footer.
  function blockWPEmbed() {
    wp_deregister_script('wp-embed');
  }
  add_action('wp_footer', 'blockWPEmbed');

  // Stop loading emoji stuff.
  remove_action('wp_head', 'print_emoji_detection_script', 7);
  remove_action('wp_print_styles', 'print_emoji_styles');
  add_filter('emoji_svg_url', '__return_false');

  // Stop linking wp-json stuff.
  remove_action('wp_head', 'rest_output_link_wp_head');
  remove_action('wp_head', 'wp_oembed_add_discovery_links');
  remove_action('template_redirect', 'rest_output_link_header');

  function MaybeRemoveCookieLawInfoFromHead() {
    if (!ShouldRemoveCookieLawInfo()) {
      return;
    }
    // Remove the Javascript and CSS.
    // To figure out the correct strings in future, add this at the end of
    // header.php in <pre> tags:
    //    global $wp_scripts;
    //    print_r($wp_scripts);
    // Then search the output for 'cookie-law-info' and look for "handle =
    // 'foo'", where 'foo' is the string you need.
    wp_dequeue_style('cookie-law-info');
    wp_deregister_style('cookie-law-info');
    wp_dequeue_style('cookie-law-info-gdpr');
    wp_deregister_style('cookie-law-info-gdpr');
    wp_dequeue_script('cookie-law-info');
    wp_deregister_script('cookie-law-info');
  }
  add_action('wp_enqueue_scripts', 'MaybeRemoveCookieLawInfoFromHead');

  // End removing unnecesary resources.

  // Add shortcodes.
  add_shortcode('jewellery_grid', 'JewelleryGridShortcode');
  add_shortcode('jewellery_page', 'JewelleryPageShortcode');
  add_shortcode('front_page_slider', 'FrontPageSliderSetupShortcode');
  add_shortcode('generic_slider', 'SliderSetupShortcode');
  add_shortcode('change_images', 'ChangeImagesSetupShortcode');
  add_shortcode('style_wrap', 'StyleWrapShortcode');

  // Enable extra image sizes.
  add_image_size('slider_large', 1024, 768);
  add_image_size('slider_small', 512, 384);
  // TODO: should this be larger?
  add_image_size('product_size', 520, 520);
  add_image_size('grid_size', 260, 260);
  // 'thumbnail' size is automatically generated by Wordpress and is used in
  // product pages.

  // Use my style sheet.
  add_editor_style('style.css');
  // Add RSS links to <head> section.
  add_theme_support('automatic-feed-links');

  // Configure opening and closing themes.
  set_closing_time('2018-12-17 18:30:00 Europe/Dublin');
  set_opening_time('2019-01-07 00:30:00 Europe/Dublin');
  set_last_delivery_outside_ireland('2018-12-11 18:30:00 Europe/Dublin');
  set_store_closing_message_display_date('2018-12-01 01:30:00 Europe/Dublin');
?>
