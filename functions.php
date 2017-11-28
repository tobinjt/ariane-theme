<?php
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

  /* get_hostname: returns the hostname.
   */
  function get_hostname() {
    return $_SERVER['SERVER_NAME'];
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
        if (strpos($url, '/store') === 0
            and strpos($current_url, '/store') === 0) {
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
    // Turn the CSV from page contents into a data structure.
    $lines = str_getcsv($page_contents, "\n");
    $ranges = array();
    $seen_header = false;
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
      $csv_data = str_getcsv($line, '|');
      // Skip blank lines.  The CSV parser will return an array with a single
      // element when given a blank line.
      if (count($csv_data) == 1) {
        continue;
      }
      if (!$seen_header) {
        // Discard the header.
        $seen_header = true;
        continue;
      }
      # Line format:
      # Short description|Image description|Image URL|Link to page|Product ID
      # The top-level jewellery page links to ranges rather than products, so we
      # can't include purchasing.  We use -1 to indicate that there isn't a
      # product to offer, and that's checked for later.
      if (count($csv_data) < 5) {
        $csv_data[] = -1;
      }
      $data = array(
        'range'      => $csv_data[0],
        'alt'        => $csv_data[1],
        'image'      => $csv_data[2],
        'link'       => $csv_data[3],
        'product_id' => $csv_data[4],
      );
      if (substr($data['link'], -1) != '/') {
        $data['link'] .= '/';
      }
      $ranges[] = $data;
    }

    # Turn the data structure into <divs>s.
    $divs = array();
    foreach ($ranges as $_ => $data) {
      $div = <<<END_OF_IMAGE_AND_RANGE
  <div class="aligncenter jewellery-block">
    <div class="jewellery-picture-container">
      <a href="{$data['link']}">
        <img src="/wp-content/uploads/{$data['image']}" alt="{$data['alt']}"
          class="aligncenter block" />
      </a>
    </div>
    <div class="larger-text text-centered left-right-margin grey">
      <a href="{$data['link']}">{$data['range']}</a>
    </div>
END_OF_IMAGE_AND_RANGE;
      $product_id = $data['product_id'];
      # -1 means there isn't a product to sell, and that happens on the main
      # jewellery page.
      # Skip showing cart buttons for everything that's been archived.
      if ($product_id != -1 && !is_archive_page()) {
        $product = new Cart66Product($product_id);
        if (Cart66Product::checkInventoryLevelForProduct($product_id) > 0) {
          $price = intval($product->price);
          $div .= <<<END_OF_BUY
    <div class="larger-text text-centered left-right-margin top-bottom-margin grey">
      €{$price}
      [add_to_cart item="{$product_id}" showprice="no" ajax="yes"
         text="Add to basket" style="display: inline;"]
    </div>
END_OF_BUY;
        } else {
          if ($product->max_quantity == 1) {
            $div .= <<<END_OF_SOLD
    <div class="text-centered left-right-margin top-bottom-margin grey">
      Sold
    </div>
END_OF_SOLD;
          } else {
            $div .= <<<END_OF_OUT_OF_STOCK
    <div class="text-centered left-right-margin top-bottom-margin grey">
      Out of stock
    </div>
END_OF_OUT_OF_STOCK;
          }
        }
      } else {
        $div .= <<<END_OF_OUT_OF_STOCK
    <div class="text-centered left-right-margin top-bottom-margin grey">
      <!-- This creates some space underneath. -->
    </div>
END_OF_OUT_OF_STOCK;
      }
      $div .= <<<END_OF_DIV
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
    return MakeJewelleryGrid($content, $attrs["description"]);
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
        'image_url' => '',
        'limited_to' => '0',
        'name' => '',
        'product_id' => '',
        'range' => '',
        'type' => '',
      ),
      $atts);
    foreach ($attrs as $key => $value) {
      if ($value == '') {
        return '<h1>jewellery_page: empty attribute: ' . $key . '</h1>' . "\n";
      }
    }
    if (substr($attrs["type"], -1) != 's') {
      $attrs["type"] .= 's';
    }

    // Wordpress puts <br /> at the start and end of the content.
    $content = str_replace('<br />', '', $content);
    if ($attrs["limited_to"] > 0) {
      $limited_to = '<p>Limited edition: only ' . $attrs["limited_to"]
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
<div id="individual-jewellery-piece" class="flexboxrow">
  <div id="individual-jewellery-image">
    <img alt="{$range_in_piece_name}{$attrs["name"]}"
      src="/wp-content/uploads{$attrs["image_url"]}" />
  </div>
  <div id="individual-jewellery-description">
    <p class="highlight larger-text">{$range_in_piece_name}{$attrs["name"]}</p>
    <p>{$content}</p>
    {$limited_to}
END_OF_HTML;

    if ($attrs['archived'] !== 'false') {
      $product = new Cart66Product($attrs['product_id']);
      if ($product->max_quantity == 1) {
        $html .= <<<END_OF_HTML
          <p>Unfortunately this unique piece of jewellery has been sold.  See
            below for other items in this range or type.</p>
END_OF_HTML;
      } else {
        $html .= <<<END_OF_HTML
          <p>Unfortunately this piece of jewellery is no longer being sold.  See
            below for other items in this range or type.</p>
END_OF_HTML;
      }
    } else {
      $product = new Cart66Product($attrs['product_id']);
      if (Cart66Product::checkInventoryLevelForProduct($attrs['product_id']) > 0) {
        $price = intval($product->price);
        $html .= <<<END_OF_HTML
      <p>Price: €{$price}.</p>
      [add_to_cart item="{$attrs["product_id"]}" showprice="no" ajax="yes"
         text="Add to basket"]
END_OF_HTML;
      } else {
        if ($product->max_quantity == 1) {
          $html .= <<<END_OF_HTML
        <p>Unfortunately this piece of jewellery has been sold.  Please
          contact Ariane to discuss commissioning a variation on this piece.
          </p>
END_OF_HTML;
          } else {
          $html .= <<<END_OF_HTML
        <p>Unfortunately this piece of jewellery is sold out.  See below for
          other items in this range or type.</p>
END_OF_HTML;
        }
      }
    }

    $html .= <<<END_OF_HTML
    <p>See other items in this range: <a href="/jewellery/{$attrs["range"]}/">{$attrs["range"]}</a></p>
    <p>See other: <a href="/jewellery/{$attrs["type"]}/">{$attrs["type"]}</a></p>
    <p>See the items in <a href="/store/cart/">your basket</a></p>
  </div>
</div>
END_OF_HTML;
    // add_to_cart needs to be expanded.
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
   *  string, the Javascript contents of the array, *without* 'var foo = '
   *  around it.
   */
  function SliderImages() {
    $media_query = new WP_Query(
      array(
        'post_type'      => 'attachment',
        'post_status'    => 'any',
        'posts_per_page' => -1,
      )
    );
    $data = array();
    foreach ($media_query->posts as $post) {
      $matches = array();
      if (preg_match('/^\s*slider\s*([^ ]*)$/', $post->post_content, $matches)) {
        $image_stats = wp_get_attachment_metadata($post->ID);
        $image_url = wp_get_attachment_url($post->ID);
        if ($image_url && $image_stats
            && $image_stats['width'] && $image_stats['height']) {
          $data[] = array(
            'image_url' => $image_url,
            'link_url' => $matches[1],
            'width' => $image_stats['width'],
            'height' => $image_stats['height'],
          );
        }
      }
    }
    return json_encode($data);
  }

  /* SliderSetup: return the Javascript needed to set up the slider, including
   * the images.
   * Returns:
   *  string, HTML that should be output.
   */
  function SliderSetup() {
    $template_directory = get_bloginfo('template_directory');
    $images = trim(SliderImages());
    $output = <<<END_OF_JAVASCRIPT
<!-- Start of SliderSetup. -->
<script type="text/javascript">
jQuery(document).ready(function() {
  var images = {$images};
  Slider.initialise(images, '#slider');
});
</script>
<!-- Include the rest of the Javascript. -->
<script type="text/javascript" src="{$template_directory}/slider.js"></script>
<!-- End of SliderSetup. -->

END_OF_JAVASCRIPT;
    return $output;
  }

  /* SliderSetupInFooter: Run SliderSetup and output the result.  This should be
   * used when registering SliderSetup to run in wp_footer().
   */
  function SliderSetupInFooter() {
    echo SliderSetup();
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
    add_action('wp_footer', 'SliderSetupInFooter');
    return '<div id="slider-div"></div>';
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
  // Disable comment feeds.  __return_false is a Wordpress function that returns
  // false to make filters easier.
  add_filter('feed_links_show_comments_feed', '__return_false');
  add_editor_style('style.css');

  // Clean up the <head>
  function removeHeadLinks() {
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    // Remove automatically generated shortlink.
    remove_action('wp_head', 'wp_shortlink_wp_head');
  }
  add_action('init', 'removeHeadLinks');
  // Remove the version strings from CSS and Javascript.  Found by searching
  // for "wordpress remove query strings from static resources".
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

  // Stop linking wp-json stuff.
  remove_action('wp_head', 'rest_output_link_wp_head');
  remove_action('wp_head', 'wp_oembed_add_discovery_links');
  remove_action('template_redirect', 'rest_output_link_header'); //, 11, 0);

  // If the Cookie Law Info cookie already exists, remove the Javascript and CSS
  // it wants to load.
  function MaybeRemoveCookieLawInfo() {
    // Page Speed doesn't set the cookie, so fake the typical user experience.
    $force_hide = false;
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Google Page Speed Insights')) {
      $force_hide = true;
    }
    if (isset($_COOKIE['viewed_cookie_policy']) || $force_hide) {
      // Remove the hooks that add Javascript and CSS.
      remove_action('wp_footer', 'cookielawinfo_inject_cli_script');
      remove_action('wp_enqueue_scripts',
        'cookielawinfo_enqueue_frontend_scripts');
    }
  }
  MaybeRemoveCookieLawInfo();

  // Add shortcodes.
  add_shortcode('jewellery_grid', 'JewelleryGridShortcode');
  add_shortcode('jewellery_page', 'JewelleryPageShortcode');
  add_shortcode('slider', 'SliderSetupShortcode');
  add_shortcode('style_wrap', 'StyleWrapShortcode');
  add_shortcode('care_page', 'CarePageShortcode');
?>
