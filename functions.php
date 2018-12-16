<?php
  // Requires PHP 7.0 or greater.
  declare(strict_types=1);

  // TODO: when we're running PHP 7.1 or later use 'void' return type where
  // appropriate.

  require_once('src/Urls.php');
  require_once('src/StoreClosingTimes.php');
  require_once('src/JewelleryGrid.php');
  require_once('src/JewelleryPage.php');
  require_once('src/MultipleImageSupport.php');

  // Send errors to browser on dev site for easier debugging.
  if (is_dev_website()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
  }

  // Used to collect slider configs and set them up.  Maps ID => JSON-encoded
  // image info.
  global $SLIDER_IMAGES;
  $SLIDER_IMAGES = array();
  // Used to collect change_images configs and set them up.  Maps ID => raw
  // image info.
  global $CHANGE_IMAGES;
  $CHANGE_IMAGES = array();

  // Define most of our functions first; some small functions will be defined
  // inline when configuring Wordpress.
  /* echo_title(): outputs the appropriate title.  */
  // TODO: return a string rather than outputting it.
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

  /* get_google_analytics_code: returns the Jvascript code for Google Analytics,
   * depending on the hostname.
   */
  function get_google_analytics_code(): string {
    if (is_dev_website()) {
      return '';
    }
    $output = <<<END_OF_JAVASCRIPT
  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-21043347-2', 'auto');
    ga('send', 'pageview');

  </script>
END_OF_JAVASCRIPT;
    return $output;
  }

  /* links_to_html: converts an array of links into HTML with a tags.
   * Args:
   *  $links: array(url => text).
   *  $url_to_highlight: the url to highlight as the current URL.
   *  $highlight_class: the value of the class attribute of the highlighted URL.
   * Returns:
   *  string.
   */
  function links_to_html(array $links, string $url_to_highlight,
                         string $highlight_class): string {
    $output = array();
    foreach ($links as $url => $text) {
      if ($url == $url_to_highlight) {
        $extra_class = ' class="' . $highlight_class . '"';
      } else {
        $extra_class = '';
      }
      $output[] = <<<END_OF_LINK
          <a href="{$url}"{$extra_class}>{$text}</a>
END_OF_LINK;
    }
    return implode("\n", $output);
  }

  /* wrap_with_tag: wrap a tag around some html.
   * Note that the indentation of the HTML will not be correct, particularly if
   * you wrap more than once.
   * Args:
   *  $tag: the tag to wrap around the HTML.
   *  $class: the CSS class for the tag.
   *  $html: the HTML to wrap the tag around.
   * Returns:
   *  string.
   */
  function wrap_with_tag(string $tag, string $class, string $html): string {
    $html = ltrim($html);
    return <<<END_OF_TAG
      <{$tag} class="{$class}">
        {$html}
      </{$tag}>
END_OF_TAG;
  }

  /* make_link_group: returns a bar of links.
   * Args:
   *   $initial_groups: an array(css-class -> array(url -> link-text)).
   *   $default_url: the URL to use if the current URL is not in $initial_groups.
   *                 Useful to make the blog link be highlighted for blog posts.
   * Returns:
   *  string.
   */
  function make_link_group(array $initial_groups, string $default_url): string {
    // Filter out invalid URLs.
    $groups = array();
    foreach ($initial_groups as $class => $links) {
      $new_links = array();
      $skipped_links = array();
      foreach ($links as $url => $text) {
        // Remove false if necessary, but usually the links are good so we don't
        // need to hit the database checking them every time.
        if (false and strpos($url, '/') === 0 and $url != '/'
          and is_null(get_page_by_path($url))) {
          // Local page that doesn't exist.  Skip it.
          $skipped_links[$url] = $text;
        } else {
          $new_links[$url] = strtolower($text);
        }
      }
      // Remove false for helpful logging.
      if (false and count($skipped_links) > 0) {
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
        # Using === rather than == is essential, otherwise the comparison fails.
        if ($pattern != '' and strpos($current_url, $pattern) === 0) {
          $url_to_highlight = $url;
        }
        if ($url == $current_url) {
          # I think this will only happen for the home page.
          $url_to_highlight = $url;
        }
        if (strpos($url, '/store') === 0 and is_store_page()) {
          # There are several pages under the store that should all have
          # 'basket' highlighted as the current link.
          $url_to_highlight = $url;
        }
      }
    }
    if (is_404()) {
      # Don't highlight any link for error pages
      $url_to_highlight = '/qwertyasdf';
    }

    $output = array();
    foreach ($groups as $class => $links) {
      $html_links = links_to_html($links, $url_to_highlight, 'highlight');
      $output[] = wrap_with_tag('span', $class, $html_links);
    }
    return implode("\n", $output) . "\n";
  }

  /* make_menu_bar: returns a menu bar.
   * Args:
   *   $menu_chunks: an array of strings.
   *   $css_tags: a string of CSS tags to be added to the containing div.
   *       'menubar' will always be present in the tags.
   * Returns:
   *  string.
   */
  function make_menu_bar(array $menu_chunks, string $css_tags): string {
    $html = wrap_with_tag(
      'div',
      'menubar ' . $css_tags,
      implode("\n", $menu_chunks));
    return $html . "\n";
  }

  function get_image_path(string $file): string {
    return get_bloginfo('template_directory') . '/images/' .  $file;
  }

  function make_icon_link(string $file, string $alt, string $width,
                          string $height): string {
    return '<img class="greyscale"' .
      ' width="' . $width . '"' .
      ' height="' . $height . '"' .
      ' src="' .  get_image_path($file) . '"' .
      ' alt="' . $alt . '" />';
  }

  /* get_messages_for_top_of_page: returns the messages to display at the top of
   * the page.
   * Returns:
   *  string.
   */
  function get_messages_for_top_of_page(): string {
    if (is_time_before('2018-12-09')) {
      $all_message = <<<ALL_MESSAGE
        <p class="text-centered larger-text grey">
          Ariane will be at <a class="external-link"
          href="http://www.giftedfair.ie/">Gifted - The Contemporary Craft &
          Design Fair</a> from Wednesday 5th December to Sunday 9th December.
          Please visit us at stand B15 on the Balcony, we'd love to see you!
          </p>
ALL_MESSAGE;
    } else {
      $all_message = '';
    }
    $other_message = <<<OTHER_MESSAGE
      <p class="text-centered larger-text grey">
        </p>
OTHER_MESSAGE;
    if (is_store_closed()) {
      $store_opening_time_human = store_opening_time_human();
      $jewellery_message = <<<JEWELLERY_MESSAGE
        <p class="text-centered larger-text grey">
          The store is now closed, and Ariane will return to the workshop
          {$store_opening_time_human}.
          </p>
JEWELLERY_MESSAGE;
    } elseif (is_time_between(store_closing_message_display_date(),
        store_closing_time())) {
      $jewellery_message = <<<JEWELLERY_MESSAGE
        <p class="text-centered larger-text grey">
JEWELLERY_MESSAGE;
      if (is_time_after(last_day_for_delivery_outside_ireland())) {
        $jewellery_message .= <<<JEWELLERY_MESSAGE
          Delivery outside Ireland before December 25th cannot be guaranteed for
          orders placed now.
JEWELLERY_MESSAGE;
      } else {
        $last = last_day_for_delivery_outside_ireland_human();
        $jewellery_message .= <<<JEWELLERY_MESSAGE
          Delivery outside Ireland before December 25th cannot be guaranteed for
          orders placed after {$last}.
JEWELLERY_MESSAGE;
      }
      $store_closing_time_human = store_closing_time_human();
      $store_opening_time_human = store_opening_time_human();
      $jewellery_message .= <<<JEWELLERY_MESSAGE
          The store will be closing on {$store_closing_time_human}.
          Ariane will return to the workshop on {$store_opening_time_human}.
          </p>
JEWELLERY_MESSAGE;
    } else {
      $jewellery_message = '';
    }

    $store_message = <<<STORE_MESSAGE
      <div id="store_message">
        <ul class="grey">
          <li>Each piece of jewellery is handmade by Ariane in her studio in
              Carlow, as a result there is normally a two week lead time on all
              orders.</li>
          <li>Free registered shipping to Ireland, EU, and USA on all orders over
              €50.</li>
          <li>Free unregistered shipping to Ireland on all orders under €50.</li>
          <li>All taxes and duties are the responsibility of the buyer.</li>
        </ul>
      </div>
STORE_MESSAGE;

    $checkout_message = '';
    if (is_current_url('/store/cart/')) {
      $checkout_message = <<<CHECKOUT_MESSAGE
        To continue, press the <em>Checkout</em> button at the bottom right of the
        page.
CHECKOUT_MESSAGE;
    }
    if (is_current_url('/store/checkout/')) {
      $checkout_message = <<<CHECKOUT_MESSAGE
        To continue, press the <em>PayPal</em> button at the bottom right of the
        page.
CHECKOUT_MESSAGE;
    }
    if (is_current_url('/store/express/')) {
      $checkout_message = <<<CHECKOUT_MESSAGE
        To complete your order you <em>must</em> press the <em>Complete Order</em>
        button at the bottom left of the page.
CHECKOUT_MESSAGE;
    }
    if ($checkout_message !== '') {
      $checkout_message = <<<CHECKOUT_MESSAGE
      <div class="largest-text highlight bold top-bottom-margin">
        {$checkout_message}
      </div>
CHECKOUT_MESSAGE;
    }

    $messages = array();
    $messages[] = $all_message;
    if (is_store_page()) {
      $messages[] = $jewellery_message;
      $messages[] = $store_message;
      $messages[] = $checkout_message;
    } elseif (is_jewellery_page()) {
      $messages[] = $jewellery_message;
    } else {
      $messages[] = $other_message;
    }
    return implode("\n", $messages);
  }

  /* StyleWrapShortcode: Wrap a div with a style around content.
   * This *must* be used in the enclosing form.
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
  // Add RSS links to <head> section.
  add_theme_support('automatic-feed-links');

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

  // Remove the version strings from CSS and Javascript to improve browser
  // caching.  Found by searching for "wordpress remove query strings from
  // static resources".
  function _remove_script_version(string $src): string {
    $parts = explode('?', $src);
    return $parts[0];
  }
  add_filter('script_loader_src', '_remove_script_version', 15, 1);
  add_filter('style_loader_src', '_remove_script_version', 15, 1);

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
  // wp_deregister_script('jquery-migrate');

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
  remove_action('template_redirect', 'rest_output_link_header'); //, 11, 0);

  // If the Cookie Law Info cookie already exists, remove the Javascript and CSS
  // it wants to load.  Output from MaybeHideCookieLawInfoInFooter() needs to be
  // inserted in <head> to hide the text added to the footer.
  function ShouldRemoveCookieLawInfo(): bool {
    $hide = false;
    if (isset($_COOKIE['viewed_cookie_policy'])) {
      $hide = true;
    }
    // Page Speed doesn't set the cookie, so fake the typical user experience.
    // TODO: These useer-agent strings need to be verified.
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (is_null($user_agent)) {
      $user_agent = 'fake user agent';
    }
    if (strpos($user_agent, 'Google Page Speed Insights')) {
      $hide = true;
    }
    if (strpos($user_agent, 'Chrome-Lighthouse')) {
      $hide = true;
    }
    return $hide;
  }

  function MaybeHideCookieLawInfoInFooter(): string {
    if (!ShouldRemoveCookieLawInfo()) {
      return "";
    }
    return <<<END_OF_CSS

  <style>
    #cookie-law-info-bar {
      display: none;
    }
  </style>

END_OF_CSS;
  }

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

  set_closing_time('2018-12-17 18:30:00 Europe/Dublin');
  set_opening_time('2019-01-07 00:30:00 Europe/Dublin');
  set_last_delivery_outside_ireland('2018-12-11 18:30:00 Europe/Dublin');
  set_store_closing_message_display_date('2018-12-01 01:30:00 Europe/Dublin');
?>
