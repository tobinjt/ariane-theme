<?php
/* Functions needed by header.php.  */

/* get_title(): return the appropriate title.  */
function get_title() {
  $result = '';
  if (is_search()) {
    $result .= 'Search for &quot;' . get_search_query() . '&quot; - ';
  } elseif (is_404()) {
    $result .= 'Not Found - ';
  } elseif (is_single() || is_page()) {
   $title = wp_title('', False);
   if ($title != '') {
     $result .= $title . ' - ';
   }
  }

  $result .= get_bloginfo('name');
  if (is_home()) {
    $result .= ' - ';
    $result .= get_bloginfo('description');
  }
  return $result;
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

function make_icon_link(string $file, string $alt, string $width,
                        string $height): string {
  return '<img class="greyscale"' .
    ' width="' . $width . '"' .
    ' height="' . $height . '"' .
    ' src="' .  get_theme_image_path($file) . '"' .
    ' alt="' . $alt . '" />';
}

function make_full_menu_bar(): string {
  $output = '';
  # This assumes that arrays are ordered, which appears to be true.
  $main_links = array(
    '/'               => 'home',
    '/jewellery/'     => 'jewellery',
    '/care/'          => 'care',
    // '/news/'          => 'news',
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
  $output .= make_menu_bar([
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
    $output .= make_menu_bar([
      make_link_group(
        array('left-page-links' => $jewellery_types_links), '/jewellery/'),
      wrap_with_tag('span', 'float-right grey',
        'Free delivery on all orders to Ireland'),
      ],
      'larger-text bottom-margin');

    $jewellery_ranges_links = array(
      '/jewellery/amble/'      => 'amble',
      '/jewellery/botanical/'  => 'botanical',
      '/jewellery/carapace/'   => 'carapace',
      '/jewellery/cellule/'    => 'cellule',
      '/jewellery/confluence/' => 'confluence',
      '/jewellery/dabble/'     => 'dabble',
      '/jewellery/halo/'       => 'halo',
      '/jewellery/laria/'      => 'laria',
      '/jewellery/pod/'        => 'pod',
      '/jewellery/sentinel/'   => 'sentinel',
      '/jewellery/wave/'       => 'wave',
      // '/jewellery/singles/'    => 'singles',
      '/jewellery/archive/'    => 'archive',
    );
    $output .= make_menu_bar([
      make_link_group(
        array('left-page-links' => $jewellery_ranges_links), '/jewellery/'),
      ],
      'larger-text bottom-margin');
  }

  return $output;
}

/* get_messages_for_top_of_page: returns the messages to display at the top of
 * the page.  Not actually used in header.php, but maybe should be.
 * TODO: should this be used in header.php instead of page.php?
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

?>
