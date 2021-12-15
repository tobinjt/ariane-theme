<?php
declare(strict_types=1);

// Functions needed by header.php.

// Extras needed by PHPLint.
/*. require_module 'core'; .*/
/*. require_module 'wordpress'; .*/
require_once(__DIR__ . '/Cast.php');
require_once(__DIR__ . '/StoreClosingTimes.php');
require_once(__DIR__ . '/Urls.php');
$BANNER_MESSAGE = '';

// get_title(): return the appropriate title.
function get_title(): string {
  $result = '';
  if (is_404()) {
    $result = 'Not Found - ';
  } elseif (is_single() || is_page()) {
    // is_single() is true for blog posts.
    $title = wp_title('', false);
    if ($title !== '') {
      $result = $title . ' - ';
    } else {
      // No much else we can do here :(
      $result = get_bloginfo('name') . ' - ';
    }
  }

  $result .= get_bloginfo('name');
  return $result;
}

/* get_google_analytics_code: returns the Jvascript code for Google Analytics,
 * depending on the hostname.
 */
function get_google_analytics_code(): string {
  if (is_dev_website()) {
    return '';
  }
  $output = <<<'END_OF_JAVASCRIPT'
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-21043347-2"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-21043347-2');
</script>
END_OF_JAVASCRIPT;
  return $output;
}

/** links_to_html: converts an array of links into HTML with a tags.
 * Args:
 *  $links array of links mapping url to text.
 *  $url_to_highlight: the url to highlight as the current URL.
 *  $highlight_class: the value of the class attribute of the highlighted URL.
 *  $indent: the number of spaces to indent with.
 * Returns:
 *  string.
 */
function links_to_html(array $links, string $url_to_highlight,
                       string $highlight_class, int $indent): string {
  $spaces = str_repeat(' ', $indent);
  $links_cast = cast('array[string]string', $links);
  /*. array[int]string .*/ $output = array();
  foreach ($links_cast as $url => $text) {
    if ($url === $url_to_highlight) {
      $extra_class = ' class="' . $highlight_class . '"';
    } else {
      $extra_class = '';
    }
    $text = strtolower($text);
    $output[] = <<<END_OF_LINK
$spaces<a href="$url"$extra_class>$text</a>
END_OF_LINK;
  }
  return implode("\n", $output);
}

/* wrap_with_tag: wrap a tag around some html.
 * Note that the indentation of the HTML will not be correct, particularly if
 * you wrap more than once.
 * Args:
 *  $tag: the tag to wrap around the HTML.
 *  $cls: the CSS class for the tag.
 *  $html: the HTML to wrap the tag around.
 *  $indent: the number of spaces to indent with.
 * Returns:
 *  string.
 */
function wrap_with_tag(string $tag, string $cls, string $html,
  int $indent): string {
  $html = ltrim($html);
  $spaces = str_repeat(' ', $indent);
  return <<<END_OF_TAG
$spaces<$tag class="$cls">
$spaces  $html
$spaces</$tag>
END_OF_TAG;
}

/* Find the URL to highlight.
 * Args:
 *   $groups: an array(css-class -> array(url -> link-text)).
 *   $default_url: the URL to use if the current URL is not in $groups.
 *                 Useful to make the blog link be highlighted for blog posts.
 * Returns:
 *  string, URL to highlight.
 */
function pick_url_to_highlight(array $groups, string $default_url): string {
  if (is_404()) {
    // Don't highlight any link for error pages
    return '/qwertyasdf';
  }
  // Strip trailing slashes everywhere to make comparisons easier.
  $current_url = rtrim(get_current_url(), '/');
  $url_to_highlight = $default_url;
  foreach ($groups as $links) {
    $lnks = cast('array[string]string', $links);
    foreach ($lnks as $url => $text) {
      $text .= 'make the linter happy.';
      $pattern = rtrim($url, '/');
      if ($pattern === $current_url) {
        return $url;
      }
      if (is_store_page() and strpos($url, '/store') === 0) {
        // There are several pages under the store that should all have
        // 'basket' highlighted as the current link.
        return $url;
      }
      // This assumes that if the URLs overlap the most specific will be last.
      // We look for matches at the start of the string.
      // Using === rather than == is essential, otherwise the comparison fails.
      if ($pattern !== '' and strpos($current_url, $pattern) === 0) {
        $url_to_highlight = $url;
      }
    }
  }
  return $url_to_highlight;
}

/* make_link_group: returns a bar of links.
 * Args:
 *   $groups: an array(css-class -> array(url -> link-text)).
 *   $default_url: the URL to use if the current URL is not in $groups.
 *                 Useful to make the blog link be highlighted for blog posts.
 * Returns:
 *  string.
 */
function make_link_group(array $groups, string $default_url): string {
  $url_to_highlight = pick_url_to_highlight($groups, $default_url);
  $groups_cast = cast('array[string][string]string', $groups);
  /*. array[int]string .*/ $output = array();
  foreach ($groups_cast as $cls => $links) {
    $html_links = links_to_html($links, $url_to_highlight, 'highlight', 8);
    $output[] = wrap_with_tag('span', $cls, $html_links, 6);
  }
  return implode("\n", $output);
}

/* make_menu_bar: returns a menu bar.
 * Args:
 *   $menu_chunks: an array of HTML strings.
 *   $css_tags: a string of CSS tags to be added to the containing div.
 *       'menubar' will always be present in the tags.
 * Returns:
 *  string.
 */
function make_menu_bar(array $menu_chunks, string $css_tags): string {
  $html = wrap_with_tag(
    'div',
    'menubar ' . $css_tags,
    implode("\n      ", cast('array[int]string', $menu_chunks)),
    4);
  return $html . "\n";
}

function make_icon_link(string $file, string $alt, int $width,
                        int $height): string {
  return '<img class="greyscale"' .
    ' width="' . $width . '"' .
    ' height="' . $height . '"' .
    ' src="' . get_theme_image_path($file) . '"' .
    ' alt="' . $alt . '" />';
}

function make_full_menu_bar(): string {
  $output = '';
  // This assumes that arrays are ordered, which appears to be true.
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
      => make_icon_link('facebook.png', 'Facebook icon', 20, 20),
    'https://www.instagram.com/arianetobin/'
      => make_icon_link('instagram-icon.jpg', 'Instagram icon', 20, 20),
  );
  $output .= make_menu_bar([
    make_link_group(
      array('largest-text left-page-links' => $main_links,
            'float-right' => $icon_links),
      '/news/'),
      ],
      '');

  if (is_jewellery_page()) {
    $output .= "\n";
    $jewellery_types_links = array(
      '/jewellery/bangles/'    => 'bangles',
      // '/jewellery/brooches/'   => 'brooches',
      '/jewellery/earrings/'   => 'earrings',
      '/jewellery/necklaces/'  => 'necklaces',
      '/jewellery/rings/'      => 'rings',
    );
    $output .= make_menu_bar([
      make_link_group(
        array('left-page-links' => $jewellery_types_links), '/jewellery/'),
      ltrim(wrap_with_tag('span', 'float-right grey',
        'Free delivery on all orders to Ireland', 6)),
      ],
      'larger-text');
    $output .= "\n";

    $jewellery_ranges_links = array(
      '/jewellery/amble/'      => 'amble',
      '/jewellery/botanical/'  => 'botanical',
      '/jewellery/carapace/'   => 'carapace',
      '/jewellery/cellule/'    => 'cellule',
      '/jewellery/confluence/' => 'confluence',
      '/jewellery/dabble/'     => 'dabble',
      '/jewellery/ellipse/'    => 'ellipse',
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
      'larger-text');
  }

  return $output;
}

/* get_banner_message: return the message to display about the banner, or an
 * empty string if it's not the right time of year.
 * Returns:
 *  string.
 */
function get_banner_message(): string {
  if (is_time_between(start_displaying_banner_message(),
    stop_displaying_banner_message())) {
      global $BANNER_MESSAGE;
      return <<<BANNER_MESSAGE
      <p class="text-centered larger-text grey">
        $BANNER_MESSAGE
      </p>

BANNER_MESSAGE;
  }
  return '';
}

function set_banner_message(string $banner_message): void {
  global $BANNER_MESSAGE;
  $BANNER_MESSAGE = $banner_message;
}

// Get the message for the top of jewellery pages.
function get_jewellery_page_message(): string {
  if (! show_store_closing_message()) {
    return '';
  }

  if (is_store_closed()) {
    $store_opening_time_human = store_opening_time_human();
    return <<<JEWELLERY_MESSAGE
      <p class="text-centered larger-text grey">
        The store is now closed, and Ariane will return to the workshop
        $store_opening_time_human.  <br />Wishing everyone a Merry Christmas
        and a Happy New Year!
      </p>

JEWELLERY_MESSAGE;
  }

  if (!is_time_between(store_closing_message_display_date(),
      store_closing_time())) {
    return '';
  }

  $last = last_day_for_delivery_outside_ireland_human();
  $store_closing_time_human = store_closing_time_human();
  $store_opening_time_human = store_opening_time_human();
  return <<<JEWELLERY_MESSAGE
    <p class="text-centered larger-text grey">
      <!-- Delivery outside Ireland before December 25th cannot be guaranteed for
      orders placed after $last. -->
      The store will be closing on $store_closing_time_human to ensure delivery
      of all orders before Christmas.
      <!-- Ariane will return to the workshop on $store_opening_time_human. -->
    </p>
JEWELLERY_MESSAGE;
}

// Get the message for the top of store pages.
function get_store_page_message(): string {
  $checkout_message = '';
  if (is_current_url('/store/cart/')) {
    $checkout_message = <<<'CHECKOUT_MESSAGE'
      To continue, press the <em>Checkout</em> button at the bottom right of the
      page.
CHECKOUT_MESSAGE;
  }
  if (is_current_url('/store/checkout/')) {
    $checkout_message = <<<'CHECKOUT_MESSAGE'
      To continue, press the <em>PayPal</em> button at the bottom right of the
      page.
CHECKOUT_MESSAGE;
  }
  if (is_current_url('/store/express/')) {
    $checkout_message = <<<'CHECKOUT_MESSAGE'
      To complete your order you <em>must</em> press the <em>Complete Order</em>
      button at the bottom left of the page.
CHECKOUT_MESSAGE;
  }
  if ($checkout_message !== '') {
    $checkout_message = ltrim($checkout_message);
    $checkout_message = <<<CHECKOUT_MESSAGE
    <strong class="largest-text highlight top-bottom-margin">
      $checkout_message
    </strong>

CHECKOUT_MESSAGE;
  }

  $checkout_message .= <<<'CHECKOUT_MESSAGE'
    <div class="store_message">
      <ul class="grey">
        <li>Each piece of jewellery is handmade by Ariane in her studio in
            Carlow, as a result there is normally a two week lead time on all
            orders.</li>
        <li>Free registered shipping on all orders.</li>
        <li>Given the difficulties of international shipping during the Covid-19
            pandemic we are not shipping outside Ireland at this time.</li>
        <!-- <li>Free registered shipping to Ireland, EU, and USA on all orders over -->
            <!-- €50.</li> -->
        <!-- <li>Free unregistered shipping to Ireland on all orders under €50.</li> -->
        <!-- <li>All taxes and duties are the responsibility of the buyer.</li> -->
      </ul>
    </div>

CHECKOUT_MESSAGE;

  return $checkout_message;
}

/* get_messages_for_top_of_page: returns the messages to display at the top of
 * the page.  Not actually used in header.php, but maybe should be.
 * Returns:
 *  string.
 */
function get_messages_for_top_of_page(): string {
  /*. array[int]string .*/ $messages = array();
  $messages[] = get_banner_message();
  if (is_store_page()) {
    $messages[] = get_jewellery_page_message();
    $messages[] = get_store_page_message();
  } elseif (is_jewellery_page()) {
    $messages[] = get_jewellery_page_message();
  }
  return implode("\n", $messages);
}
