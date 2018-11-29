<?php
  /* get_hostname: returns the hostname.
   */
  function get_hostname() {
    return $_SERVER['SERVER_NAME'];
  }

  // Send errors to browser on dev site for easier debugging.
  if (get_hostname() == 'dev.arianetobin.ie') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
  }

  // Define most of our functions first; some small functions will be defined
  // inline when configuring Wordpress.
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

  /* get_current_url: returns the local portion of the URL, i.e. no hostname,
   * but it does include the query string.
   */
  function get_current_url() {
    return $_SERVER['REQUEST_URI'];
  }

  /* get_google_analytics_code: returns the Jvascript code for Google Analytics,
   * depending on the hostname.
   */
  function get_google_analytics_code() {
    $hostname = get_hostname();
    if ($hostname != 'www.arianetobin.ie') {
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

  /* is_jewellery_page: is the current page a jewellery page?  */
  function is_jewellery_page() {
    return (strpos(get_current_url(), '/jewellery') === 0);
  }

  /* is_store_page: is the current page a store page?  */
  function is_store_page() {
    return (strpos(get_current_url(), '/store') === 0);
  }

  /* is_url: is the current page === $url?  The query string is stripped. */
  function is_url($url) {
    $current_url = parse_url(get_current_url(), PHP_URL_PATH);
    return ($current_url === $url);
  }

  /* is_archive_page: is the current page a archive page?  */
  function is_archive_page() {
    return (strpos(get_current_url(), '/jewellery/archive') === 0);
  }

  /* is_time_after: is the current time after the specified time and date?
   * Args:
   *  $time_string: a time and date string parsable by strtotime().
   * Returns:
   *  Boolean.
   */
  function is_time_after($time_string) {
    return time() > strtotime($time_string);
  }

  /* is_time_before: is the current time before the specified time and date?
   * Args:
   *  $time_string: a time and date string parsable by strtotime().
   * Returns:
   *  Boolean.
   */
  function is_time_before($time_string) {
    return time() < strtotime($time_string);
  }

  /* is_time_between: is the current time between the specified times and dates?
   * Args:
   *  $start_time_string: a time and date string parsable by strtotime().
   *  $end_time_string: a time and date string parsable by strtotime().
   * Returns:
   *  Boolean.
   */
  function is_time_between($start_time_string, $end_time_string) {
    return (is_time_after($start_time_string)
            && is_time_before($end_time_string));
  }

  /* store_closing_time_human: human readable time for the store to close.
   * Must be manually kept in sync with store_closing_time().
   */
  function store_closing_time_human() {
    return 'Friday 11th May';
  }

  /* store_closing_time: when the store closes next. */
  function store_closing_time() {
    return '2018-05-11 00:30:00 Europe/Dublin';
  }

  /* store_opening_time_human: human readable time for the store to open.
   * Must be manually kept in sync with store_opening_time().
   */
  function store_opening_time_human() {
    return 'Monday 28th May';
  }

  /* store_opening_time: when the store opens next. */
  function store_opening_time() {
    return '2018-05-28 00:30:00 Europe/Dublin';
  }

  /* is_store_closed: is the store currently closed?  Uses store_closing_time()
   * and store_opening_time().
   */
  function is_store_closed() {
    return is_time_between(store_closing_time(), store_opening_time());
  }

  /* links_to_html: converts an array of links into HTML with a tags.
   * Args:
   *  $links: array(url => text).
   *  $url_to_highlight: the url to highlight as the current URL.
   *  $highlight_class: the value of the class attribute of the highlighted URL.
   * Returns:
   *  string.
   */
  function links_to_html($links, $url_to_highlight, $highlight_class) {
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
  function wrap_with_tag($tag, $class, $html) {
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
  function make_link_group($initial_groups, $default_url) {
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
  function make_menu_bar($menu_chunks, $css_tags) {
    $html = wrap_with_tag(
      'div',
      'menubar ' . $css_tags,
      implode("\n", $menu_chunks));
    return $html . "\n";
  }

  function get_image_path($file) {
    return get_bloginfo('template_directory') . '/images/' .  $file;
  }

  function make_icon_link($file, $alt, $width, $height) {
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
  function get_messages_for_top_of_page() {
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
    } elseif (is_time_before(store_closing_time())) {
      $store_closing_time_human = store_closing_time_human();
      $store_opening_time_human = store_opening_time_human();
      $jewellery_message = <<<JEWELLERY_MESSAGE
        <p class="text-centered larger-text grey">
          The store will be closing {$store_closing_time_human}.
          Ariane will return to the workshop {$store_opening_time_human}.
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
    if (is_url('/store/cart/')) {
      $checkout_message = <<<CHECKOUT_MESSAGE
        To continue, press the <em>Checkout</em> button at the bottom right of the
        page.
CHECKOUT_MESSAGE;
    }
    if (is_url('/store/checkout/')) {
      $checkout_message = <<<CHECKOUT_MESSAGE
        To continue, press the <em>PayPal</em> button at the bottom right of the
        page.
CHECKOUT_MESSAGE;
    }
    if (is_url('/store/express/')) {
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

  /* ParseJewelleryGridContents: turn the CSV from page contents into a data
   * structure.
   * Args:
   *  $page_contents: string, the contents of the page.  First line (CSV header)
   *    will be removed.  Blank lines will be skipped.  <br /> will be stripped
   *    from the end of each line.
   * Returns:
   *  array, data structure to process.
   */
  function ParseJewelleryGridContents($page_contents) {
    $lines = str_getcsv($page_contents, "\n");
    $ranges = array();
    foreach ($lines as $line) {
      $line = trim($line);
      // Wordpress puts <br /> and </p> and other shite at the end of some
      // lines, so remove all tags from the start and end of each line.
      $line = preg_replace('/^<[^<]+>/', '', $line);
      $line = preg_replace('/<[^<]+>$/', '', $line);
      $line = trim($line);
      if (strpos($line, '#') === 0) {
        continue;
      }
      # Awful hack to work around wordpress turning 276x300 into 276!300, where
      # ! is actually some weird unicode x - this breaks image urls. ARGH.
      # TODO(johntobin): figure this shit out properly.
      $line = preg_replace('/&#215;/', 'x', $line);
      $csv_data = str_getcsv($line, '|');
      // Skip blank lines.  The CSV parser will return an array with a single
      // element when given a blank line.
      if (count($csv_data) == 1) {
        continue;
      }
      # Line format:
      # * Short description|Image description|Image ID|Link to page|Product ID
      # * The top-level jewellery page links to ranges rather than products, so
      #   we can't include purchasing.  We use -1 to indicate that there isn't a
      #   product to offer, and that's checked for later.
      if (count($csv_data) < 5) {
        $csv_data[] = -1;
      }
      $data = array(
        'range'      => $csv_data[0],
        'alt'        => $csv_data[1],
        'image_id'   => $csv_data[2],
        'link'       => $csv_data[3],
        'product_id' => $csv_data[4],
        'width'      => 0,
        'height'     => 0,
      );
      if (substr($data['link'], -1) != '/') {
        $data['link'] .= '/';
      }
      $image_info = wp_get_attachment_image_src(
        intval($data['image_id']), 'grid_size');
      $data['image'] = $image_info[0];
      $data['width'] = $image_info[1];
      $data['height'] = $image_info[2];
      $ranges[] = $data;
    }
    return $ranges;
  }

  /* MakeBuyButtonForJewelleryGrid: make a buy botton or a message or whatever
   * is appropriate for the product in the jewellery grid.
   * Args:
   *  $product_id: id of the product in Cart66.
   * Returns:
   *  string, HTML to insert in page.
   */
  function MakeBuyButtonForJewelleryGrid($product_id) {
    # -1 means there isn't a product to sell, and that happens on the main
    # jewellery page.
    # Skip showing cart buttons for everything that's been archived.
    if ($product_id == -1 || is_archive_page()) {
      return <<<END_OF_NO_PRODUCT_OR_ARCHIVE
      <!-- This creates some space underneath. -->
END_OF_NO_PRODUCT_OR_ARCHIVE;
    }

    $product = new Cart66Product($product_id);
    if (Cart66Product::checkInventoryLevelForProduct($product_id) > 0) {
      $price = intval($product->price);
      $content = <<<END_OF_PRICE
      <div class="larger-text">
        €{$price}
END_OF_PRICE;
      if (!is_store_closed()) {
        $content .= <<<END_OF_BUY
        [add_to_cart item="{$product_id}" showprice="no" ajax="yes"
           text="Add to basket" style="display: inline;"]
END_OF_BUY;
      } else {
        $content .= <<<END_OF_CLOSED
        (store closed)
END_OF_CLOSED;
      }
      $content .= <<<END_OF_DIV
      </div>
END_OF_DIV;
      return $content;
    }

    if ($product->max_quantity == 1) {
      return <<<END_OF_SOLD
      Sold
END_OF_SOLD;
    }

    return <<<END_OF_OUT_OF_STOCK
      This piece is out of stock, please contact Ariane as it's possible this
      item could be made to order.
END_OF_OUT_OF_STOCK;
  }

  /* MakeJewelleryGrid: create a table from CSV content.
   * Args:
   *  $page_contents: string, the contents of the page.  First line (CSV header)
   *    will be removed.  Blank lines will be skipped.  <br /> will be stripped
   *    from the end of each line.
   *  $description: string, the description to display at the top of the page.
   *    If the string is empty nothing will be added.
   * Returns:
   *  string, HTML to insert in the page.
   */
  function MakeJewelleryGrid($page_contents, $description) {
    $ranges = ParseJewelleryGridContents($page_contents);
    # Turn the data structure into <divs>s.
    $divs = array();
    foreach ($ranges as $_ => $data) {
      $div = <<<END_OF_IMAGE_AND_RANGE
  <div class="aligncenter jewellery-block">
    <div class="jewellery-picture-container">
      <a href="{$data['link']}">
        <img src="{$data['image']}" alt="{$data['alt']}"
          width="{$data['width']}" height="{$data['height']}"
          class="aligncenter block" />
      </a>
    </div>
    <div class="larger-text text-centered left-right-margin grey">
      <a href="{$data['link']}">{$data['range']}</a>
    </div>
END_OF_IMAGE_AND_RANGE;
      $div .= <<<END_OF_OPEN_BUY_DIV
    <div class="text-centered left-right-margin top-bottom-margin grey
      jewellery-text-container">
END_OF_OPEN_BUY_DIV;
      $div .= MakeBuyButtonForJewelleryGrid($data['product_id']);
      $div .= <<<END_OF_DIV
    </div>
  </div>
END_OF_DIV;
      $divs[] = $div;
    }

    $html = array();
    $html[] = <<<END_OF_HTML
          <div id="jewellery-grid">
END_OF_HTML;
    if ($description != '') {
      $html[] = <<<END_OF_DESCRIPTION
            <div>
              <p class="grey large-text text-centered">{$description}</p>
            </div>
END_OF_DESCRIPTION;
    }
    $html[] = <<<END_OF_HTML
            <div id="jewellery-grid-inner" class="flexboxrow">
END_OF_HTML;
    $html = array_merge($html, $divs);
    $html[] = <<<END_OF_HTML
            </div>
          </div>
END_OF_HTML;
    return do_shortcode(implode("\n", $html));
  }

  /* JewelleryGridShortcode: wrap MakeJewelleryGrid to provide a shortcode.
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
  function JewelleryGridShortcode($atts, $content=null, $tag) {
    if (is_null($content)) {
      return '<h1>jewellery_grid: no content to display!</h1>' . "\n";
    }
    $attrs = shortcode_atts(
      array(
        'description' => '',
      ),
      $atts);
    return MakeJewelleryGrid($content, $attrs['description']);
  }

  /* MakeBuyButtonForJewelleryPage: make a buy botton or a message or whatever
   * is appropriate for the product in the jewellery page.
   * Args:
   *  $attrs: the attributes of the product.
   * Returns:
   *  string, HTML to insert in page.
   */
  function MakeBuyButtonForJewelleryPage($attrs) {
    $product = new Cart66Product($attrs['product_id']);
    if ($product->max_quantity == 1) {
      return <<<END_OF_HTML
          <p>Unfortunately this unique piece of jewellery has been sold.  See
            below for other items in this range or type.</p>
END_OF_HTML;
    }

    if ($attrs['archived'] !== 'false') {
      return <<<END_OF_HTML
          <p>Unfortunately this piece of jewellery is no longer being sold.  See
            below for other items in this range or type.</p>
END_OF_HTML;
    }

    if (Cart66Product::checkInventoryLevelForProduct($attrs['product_id']) > 0) {
      $price = intval($product->price);
      $content = <<<END_OF_HTML
      <p>Price: €{$price}.</p>
END_OF_HTML;
      if (!is_store_closed()) {
        $content .= <<<END_OF_HTML
      [add_to_cart item="{$attrs['product_id']}" showprice="no" ajax="yes"
         text="Add to basket"]
END_OF_HTML;
      } else {
        $store_opening_time_human = store_opening_time_human();
        $content .= <<<END_OF_HTML
        The store is currently closed, it will open again on
        {$store_opening_time_human}.
END_OF_HTML;
      }
      return $content;
    }

    return <<<END_OF_HTML
      <p>This piece is out of stock, please contact Ariane as it's possible this
        item could be made to order.  See below for other items in this range or
        type.</p>
END_OF_HTML;
  }

  /* JewelleryPageShortcode: create a jewellery page.
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
  function JewelleryPageShortcode($atts, $content, $tag) {
    if (is_null($content)) {
      return '<h1>jewellery_page: no description to display!</h1>' . "\n";
    }
    if (is_string($atts)) {
      return '<h1>jewellery_page: need attributes! </h1>' . "\n";
    }

    $attrs = shortcode_atts(
      array(
        'archived' => 'false',
        'height' => 0,
        'image_id' => '',
        'limited_to' => '0',
        'name' => '',
        'product_id' => '',
        'range' => '',
        'type' => '',
        'width' => 0,
      ),
      $atts);
    foreach ($attrs as $key => $value) {
      if ($value != 0 && $value == '') {
        return '<h1>jewellery_page: empty attribute: ' . $key . '</h1>' . "\n";
      }
    }

    # Look up the image(s).
    $image_ids = explode(',', $attrs['image_id']);
    $slider_images = array();
    foreach ($image_ids as $image_id) {
      $image_info = wp_get_attachment_image_src($image_id, 'product_size');
      $slider_images[] = array(
        'src' => $image_info[0],
        'width' => $image_info[1],
        'height' => $image_info[2],
      );
    }
    # Load the first image immediately.
    $attrs['src'] = $slider_images[0]['src'];
    $attrs['width'] = $slider_images[0]['width'];
    $attrs['height'] = $slider_images[0]['height'];

    # Change "necklace" to "necklaces".
    if (substr($attrs['type'], -1) != 's') {
      $attrs['type'] .= 's';
    }
    // Wordpress puts <br /> at the start and end of the content.
    $content = str_replace('<br />', '', $content);
    if ($attrs['limited_to'] > 0) {
      $limited_to = '<p>Limited edition: only ' . $attrs['limited_to']
        . ' will be made.</p>';
    } else {
      $limited_to = '';
    }
    // We're not ready for limited edition stuff yet.
    // TODO(johntobin): support limited editions and one-off pieces.
    $limited_to = '';

    // Don't make the range part of the name for some ranges.
    $blacklisted_ranges = array('archive', 'singles');
    if (in_array($attrs['range'], $blacklisted_ranges)) {
      $range_in_piece_name = '';
    } else {
      $range_in_piece_name = $attrs['range'] . ' ';
    }

    $html = <<<END_OF_HTML
<div class="flexboxrow">
  <div id="individual-jewellery-div" >
    <img id="individual-jewellery-image"
      alt="{$range_in_piece_name}{$attrs['name']}"
      src="{$attrs['src']}"
      width="{$attrs['width']}" height="{$attrs['height']}" />
  </div>
  <div id="individual-jewellery-description">
    <p class="highlight larger-text">{$range_in_piece_name}{$attrs['name']}</p>
    <p>{$content}</p>
    {$limited_to}
END_OF_HTML;

    $html .= MakeBuyButtonForJewelleryPage($attrs);

    $html .= <<<END_OF_HTML
    <p>See other items in this range: <a href="/jewellery/{$attrs['range']}/">{$attrs['range']}</a></p>
    <p>See other: <a href="/jewellery/{$attrs['type']}/">{$attrs['type']}</a></p>
    <p>See the items in <a href="/store/cart/">your basket</a></p>
  </div>
</div>
END_OF_HTML;
    if (count($slider_images) > 1) {
      global $PRODUCT_PAGE_SLIDER_DATA;
      $PRODUCT_PAGE_SLIDER_DATA = json_encode($slider_images);
      $html .= <<<END_OF_HTML
[product_page_slider]
END_OF_HTML;
    }
    // Shortcodes need to be expanded.
    return do_shortcode($html);
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
  function StyleWrapShortcode($atts, $content=null, $tag) {
    if (is_null($content)) {
      return '<h1>style_wrap: no content to display!</h1>' . "\n";
    }
    if (is_string($atts)) {
      return '<h1>style_wrap: need <b>class</b> or <b>id</b> attributes!</h1>'
        . "\n";
    }

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


  /* SliderImages: Dynamically build the Javascript array of images when
   * displaying the slider.
   * Returns:
   *  array of image information, needs to be passed to json_encode().
   */
  function SliderImages() {
    $media_query = new WP_Query(
      array(
        'post_type'      => 'attachment',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        's'              => 'slider',
      )
    );
    $images = array();
    foreach ($media_query->posts as $post) {
      $matches = array();
      if (preg_match('/^\s*slider\s+([^ ]+)$/', $post->post_content, $matches)) {
        $image_large = wp_get_attachment_image_src($post->ID, 'slider_large');
        $image_small = wp_get_attachment_image_src($post->ID, 'slider_small');
        $images[] = array(
          'src' => $image_large[0],
          'href' => $matches[1],
          'srcset' => ("{$image_large[0]} {$image_large[1]}w,\n"
                       . " {$image_small[0]} {$image_small[1]}w"),
          'sizes' => ("(max-width: 799px) {$image_small[1]}px,\n"
                      . " {$image_large[1]}px"),
        );
      }
    }
    return $images;
  }

  /* SliderSetupGeneric: return the Javascript needed to set up the slider,
   * including the images.
   * Args:
   *  $json: JSON containing image data.
   *  $slider_prefix: id_prefix - prefix of CSS id to be used by slider.
   * Returns:
   *  string, HTML that should be output.
   */
  function SliderSetupGeneric($images, $id_prefix) {
    $template_directory = get_bloginfo('template_directory');
    $images = trim($images);
    $output = <<<END_OF_JAVASCRIPT
<!-- Start of SliderSetup. -->
<script type="text/javascript">
jQuery(document).ready(function() {
  var images = {$images};
  Slider.initialise(images, '{$id_prefix}');
});
</script>
<!-- Include the rest of the Javascript. -->
<script type="text/javascript" src="{$template_directory}/slider.js"></script>
<!-- End of SliderSetup. -->

END_OF_JAVASCRIPT;
    return $output;
  }

  /* SliderSetupFrontPage: output the Javascript needed to set up the slider,
   * including the images.
   */
  function SliderSetupFrontPage() {
    global $SLIDER_IMAGES_FRONT_PAGE;
    echo SliderSetupGeneric($SLIDER_IMAGES_FRONT_PAGE, '#slider');
  }

  /* SliderSetupShortcode: wrap SliderSetup to provide a shortcode.
   * This *must not* be used in the enclosing form.
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
  function SliderSetupShortcode($atts, $content=null, $tag) {
    if (!is_null($content) and $content != '') {
      return '<h1>slider: no content accepted!  Given: '
        . htmlspecialchars($content) . '</h1>' . "\n";
    }
    if (!is_string($atts)) {
      return '<h1>slider: no attributes accepted!</h1>' . "\n";
    }

    add_action('wp_footer', 'SliderSetupFrontPage');
    $images = SliderImages('slider_large');
    global $SLIDER_IMAGES_FRONT_PAGE;
    $SLIDER_IMAGES_FRONT_PAGE = json_encode($images);
    $image = $images[0];
    $html = <<<END_OF_HTML
<div id="slider-div">
  <a href="{$image['href']}" id="slider-link"
    alt="Selection of Ariane's best work">
    <img id="slider-image" src="{$image['src']}"
      alt="Selection of Ariane's best work"
      srcset="{$image['srcset']}"
      sizes="{$image['sizes']}" />
  </a>
</div>
END_OF_HTML;
    return $html;
  }

  /* ProductPageSliderSetupInFooter: output the Javascript needed to set up the
   * slider, including the images.
   */
  function ProductPageSliderSetupInFooter() {
    global $PRODUCT_PAGE_SLIDER_DATA;
    echo SliderSetupGeneric($PRODUCT_PAGE_SLIDER_DATA, '#individual-jewellery');
  }

  /* ProductPageSliderSetupShortcode: wrap ProductPageSliderSetupInFooter to
   * provide a shortcode.  This *must not* be used in the enclosing form.
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
  function ProductPageSliderSetupShortcode($atts, $content=null, $tag) {
    if (!is_null($content) and $content != '') {
      return '<h1>slider: no content accepted!  Given: '
        . htmlspecialchars($content) . '</h1>' . "\n";
    }
    if (!is_string($atts)) {
      return '<h1>slider: no attributes accepted!</h1>' . "\n";
    }
    add_action('wp_footer', 'ProductPageSliderSetupInFooter');
    return '';
  }

  function CarePageShortcode($atts, $content, $tag) {
    $html = <<<END_OF_HTML
[style_wrap id="care-page"]

My jewellery is made from a variety of materials, each of which has separate
care instructions below.  The general care section applies to all my jewellery.
Be careful when cleaning jewellery made from multiple materials, because a
cleaning product that is safe on one material may damage another.  If you have
any questions or doubts, do not hesitate to <a title="Contact details"
href="https://www.arianetobin.ie/about/">contact me</a> and ask for help.

<h2 class="grey">General care instructions</h2>

<ul>
  <li>Exposure to hairspray, perfume, and other beauty products will leave a
    residue on jewellery or may damage the finish. Put your jewellery
    on <strong>after</strong> using these products.</li>
  <li>Chlorine in a swimming pool or salt in the sea can damage jewellery.
    Remove your jewellery before swimming.</li>
  <li>Do not wear your jewellery while cleaning, the chemicals in household
    cleaners can damage jewellery.</li>
  <li>Store each piece of jewellery separately so that they do not scratch or
    scrape each other.</li>
  <li>Delicate pieces of jewellery should be stored in their original
    boxes.</li>
</ul>

<h2 class="grey">Gold</h2>
<ul>
  <li>White gold will have been rhodium plated if you requested it. The rhodium
    plating will need to be renewed every 12-24 months depending on wear.</li>
  <li>Gold does not tarnish like silver, but it will get dirty over time, and
    should be cleaned with a gold cloth.</li>
  <li>Gold plating should be treated carefully, and never cleaned with anything
    abrasive.</li>
</ul>

<h2 class="grey">Pearls</h2>
<ul>
  <li>Pearls are particularly susceptible to damage from perfume and
    hairspray.</li>
  <li>Clean by rubbing gently with a soft, clean cloth - <strong>do
    not</strong> use any chemical cleaning products!</li>
  <li>Pearls will need to be restrung regularly, with the time between
    restringing dependent on wear and tear. I offer a good value restringing
    service for all of my own work.</li>
</ul>

<h2 class="grey">Precious stones</h2>
<ul>
  <li>Some precious stones can be damaged by water because they are very porous.
    If this is the case I will tell you when you are buying the piece of
    jewellery, and I will supply special care instructions for the piece.</li>
  <li>Precious stones should be cleaned with hand-warm water and a soft cloth.
    Never use anything abrasive or chemical.</li>
</ul>

<h2 class="grey">Sterling silver</h2>
<ul>
  <li>Silver naturally tarnishes over time and will eventually need cleaning. I
    recommend using a silver cloth.</li>
  <li>Over time, a matt surface will become shinier, and a shiny polished
    surface will become more matt. I will refinish my own work for a small
    fee.</li>
</ul>

<h2 class="grey">Wood</h2>
<ul>
  <li>Do not immerse wood in water.</li>
  <li>If wood has gotten dull and you want to restore the natural lustre of the
    wood, rub a small amount of food-safe wood oil (e.g. Swedish oil) over the
    wood.  If the oil spreads on to silver or gold, remove it quickly with a
    soft dry cloth because there is a risk of staining.</li>
</ul>
[/style_wrap]
END_OF_HTML;

    // Shortcodes need to be expanded.
    return do_shortcode($html);
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
  function _remove_script_version($src){
    $parts = explode('?', $src);
    return $parts[0];
  }
  add_filter('script_loader_src', '_remove_script_version', 15, 1);
  add_filter('style_loader_src', '_remove_script_version', 15, 1);

  // Stop jquery-migrate being loaded.  jQuery depends on it, so the jQuery deps
  // need to be changed too.
  function blockJqueryMigrate($scripts) {
    $data = $scripts->query('jquery');
    if (!$data) {
      return;
    }
    $data->deps = array_diff($data->deps, array('jquery-migrate'));
  }
  add_action('wp_default_scripts', 'blockJqueryMigrate');
  wp_deregister_script('jquery-migrate');

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
  function ShouldRemoveCookieLawInfo() {
    $hide = false;
    if (isset($_COOKIE['viewed_cookie_policy'])) {
      $hide = true;
    }
    // Page Speed doesn't set the cookie, so fake the typical user experience.
    // TODO: These useer-agent strings need to be verified.
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Google Page Speed Insights')) {
      $hide = true;
    }
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome-Lighthouse')) {
      $hide = true;
    }
    return $hide;
  }

  function MaybeHideCookieLawInfoInFooter() {
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
  add_shortcode('slider', 'SliderSetupShortcode');
  add_shortcode('product_page_slider', 'ProductPageSliderSetupShortcode');
  add_shortcode('style_wrap', 'StyleWrapShortcode');
  add_shortcode('care_page', 'CarePageShortcode');
?>
