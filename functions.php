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

  /* get_current_url: returns the URL. */
  function get_current_url() {
    return $_SERVER['REQUEST_URI'];
  }

  /* is_jewellery_page: is the current page a jewellery page?  */
  function is_jewellery_page() {
    return (strpos(get_current_url(), '/jewellery') === 0);
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

  /* make_link_bar: returns a bar of links.
   * Args:
   *   $initial_groups: an array(css-class -> array(url -> link-text)).
   *   $default_url: the URL to use if the current URL is not in $initial_groups.
   *                 Useful to make the blog link be highlighted for blog posts.
   * Returns:
   *  string.
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

    $output = array();
    foreach ($groups as $class => $links) {
      $html_links = links_to_html($links, $url_to_highlight, 'pink');
      $output[] = wrap_with_tag('span', $class, $html_links);
    }
    return wrap_with_tag('div', 'menubar', implode("\n", $output)) . "\n";
  }

  function make_icon_link($file, $alt, $width, $height) {
    return '<img class="greyscale"' .
      ' width="' . round($width * 1.414) . '"' .
      ' height="' . round($height * 1.414) . '"' .
      ' src="' .  get_bloginfo('template_directory') . '/images/' .  $file . '"' .
      ' alt="' . $alt . '" />';
  }

  /* MakeJewelleryGrid: create a table from CSV content.
   * Args:
   *  $page_contents: string, the contents of the page.  First line (CSV header)
   *    will be removed.  Blank lines will be skipped.  <br /> will be stripped
   *    from the end of each line.
   * Returns:
   *  string, HTML to insert in the page.
   */
  function MakeJewelleryGrid($page_contents) {
    // Turn the CSV from page contents into a data structure.
    $lines = str_getcsv($page_contents, "\n");
    $ranges = array();
    $seen_header = false;
    foreach ($lines as $line) {
      // Wordpress puts <br /> at the end of each line.
      $line = str_replace('<br />', '', $line);
      $data = str_getcsv($line, '|');
      // Skip blank lines.  The CSV parser will return an array with a single
      // element when given a blank line.
      if (count($data) == 1) {
        continue;
      }
      if (!$seen_header) {
        // Discard the header.
        $seen_header = true;
        continue;
      }
      $ranges[$data[0]] = array(
        'alt'   => $data[1],
        'image' => $data[2],
        'link'  => $data[3],
      );
    }

    # Turn the data structure into <tr>s.
    $tds = array();
    foreach ($ranges as $range => $data) {
      $tds[] = <<<END_OF_TD
  <td>
    <a href="{$data['link']}">
      <img src="/wp-content/uploads/{$data['image']}" alt="{$data['alt']}" />
    </a>
    <div class="jewellery-grid-name">
      <a href="{$data['link']}">{$range}</a>
    </div>
  </td>
END_OF_TD;
    }

    # Turn the <tr>s into a table with three columns.
    $num_columns = 3;
    while (count($tds) % $num_columns != 0) {
      $tds[] = '<td></td>';
    }
    $table = array();
    $table[] = <<<END_OF_TABLE_START
          <div id="jewellery-grid">
            <table>
END_OF_TABLE_START;
    for ($i = 0; $i < count($tds); $i++) {
      if ($i % $num_columns == 0) {
        $table[] = '<tr>';
      }
      $table[] = $tds[$i];
      if ($i % $num_columns == $num_columns - 1) {
        $table[] = '</tr>';
      }
    }
    $table[] = <<<END_OF_TABLE_END
            </table>
          </div>
END_OF_TABLE_END;
    return implode("\n", $table);
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
    if (!is_string($atts)) {
      return '<h1>jewellery_grid: no attributes accepted!</h1>' . "\n";
    }
    return MakeJewelleryGrid($content);
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
    return <<<END_OF_DIV
{$full_div}
  {$stripped_content}
</div>
END_OF_DIV;
  }


  /* SliderImages: Dynamically build the Javascript array of images when
   * displaying the slider.
   * Returns:
   *  string, the Javascript contents of the array, *without* 'var foo = []'
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
    $output = array();
    foreach ($media_query->posts as $post) {
      if (preg_match('/^\s*slider\s*$/', $post->post_content)) {
        $image_stats = wp_get_attachment_metadata($post->ID);
        $url = wp_get_attachment_url($post->ID);
        if ($url && $image_stats
            && $image_stats['width'] && $image_stats['height']) {
            $output[] = <<<END_OF_ROW
    {'url': '{$url}', 'width': {$image_stats['width']}, 'height': {$image_stats['height']}},
END_OF_ROW;
        }
      }
    }
    return implode("\n", $output);
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
  var images = [
    {$images}
  ];
  // The images array ends with a comma, and IE 8 adds a null or undefined
  // element after the comma, so we remove that element.
  if (images[images.length - 1] === null
        || images[images.length - 1] === undefined) {
    images.pop();
  }
  Slider.initialise(images);
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
      return '<h1>jewellery_page: need attributes! XXX </h1>'
        . "\n";
    }

    $attrs = shortcode_atts(
      array(
        'image_url' => '',
        'name' => '',
        'price' => '',
        'product_id' => '',
      ),
      $atts);
    foreach ($attrs as $key => $value) {
      if ($value == '') {
        return '<h1>jewellery_page: empty attribute: ' . $key . '</h1>' . "\n";
      }
    }

    // Wordpress puts <br /> at the start and end of the content.
    $content = str_replace('<br />', '', $content);
    $html = <<<END_OF_HTML
<table id="individual-jewellery-piece">
  <tr>
    <td>
      <img alt="{$attrs["name"]}"
        src="/wp-content/uploads{$attrs["image_url"]}" />
    </td>
    <td>
      <div id="individual-jewellery-description">
        <p class="grey larger-text">{$attrs["name"]}</p>
        <p>{$content}</p>
        <p>Price: €{$attrs["price"]}.</p>
        [add_to_cart item="{$attrs["product_id"]}" showprice="no" ajax="yes" ]
      </div>
    </td>
  </tr>
</table>
END_OF_HTML;
    // add_to_cart needs to be expanded.
    return do_shortcode($html);
  }


  // Configure Wordpress.
  // Add RSS links to <head> section.
  add_theme_support('automatic-feed-links');
  add_editor_style('style.css');

  // Clean up the <head>
  function removeHeadLinks() {
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
  }
  add_action('init', 'removeHeadLinks');
  remove_action('wp_head', 'wp_generator');
  // Remove the version strings from CSS and Javascript.  Found by searching
  // for "wordpress remove query strings from static resources".
  function _remove_script_version($src){
    $parts = explode('?', $src);
    return $parts[0];
  }
  add_filter('script_loader_src', '_remove_script_version', 15, 1);
  add_filter('style_loader_src', '_remove_script_version', 15, 1);

  // Load Javascript libraries.
  wp_enqueue_script('jquery');
  wp_register_script('modernizr',
     '//cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.min.js',
     false);
  wp_enqueue_script('modernizr');

  // Set up the sidebar.
  register_sidebar(array(
    'name' => __('Sidebar Widgets','html5reset' ),
    'id'   => 'sidebar-widgets',
    'description'   => __( 'These are widgets for the sidebar.','html5reset' ),
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget'  => '</div>',
    'before_title'  => '<h2>',
    'after_title'   => '</h2>'
  ));

  // Add shortcodes.
  add_shortcode('jewellery_grid', 'JewelleryGridShortcode');
  add_shortcode('jewellery_page', 'JewelleryPageShortcode');
  add_shortcode('slider', 'SliderSetupShortcode');
  add_shortcode('style_wrap', 'StyleWrapShortcode');
?>
