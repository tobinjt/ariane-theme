<?php
declare(strict_types=1);

// Support for showing an individual piece of Jewellery.

// Extras needed by PHPLint.
/*. require_module 'core'; .*/
/*. require_module 'wordpress'; .*/
require_once(__DIR__ . '/StoreClosingTimes.php');
/*. array[string][int][string]int .*/ $CHANGE_IMAGES = array();

/* MakeBuyButtonForJewelleryPage: make a buy botton or a message or whatever
 * is appropriate for the product in the jewellery page.
 * Args:
 *  $attrs: the attributes of the product.
 * Returns:
 *  string, HTML to insert in page.
 */
function MakeBuyButtonForJewelleryPage(array $attrs): string {
  $product = new Cart66Product($attrs['product_id']);
  if ($product->max_quantity == 1) {
    return <<<'END_OF_HTML'
        <p>Unfortunately this unique piece of jewellery has been sold.  See
          below for other items in this range or type.</p>
END_OF_HTML;
  }

  if ($attrs['archived'] !== 'false') {
    return <<<'END_OF_HTML'
        <p>Unfortunately this piece of jewellery is no longer being sold.  See
          below for other items in this range or type.</p>
END_OF_HTML;
  }

  if (Cart66Product::checkInventoryLevelForProduct($attrs['product_id']) > 0) {
    $price = intval($product->price);
    $content = <<<END_OF_HTML
    <p>Price: €$price.</p>

END_OF_HTML;
    if (!is_store_closed()) {
      $product_id = strval($attrs['product_id']);
      $content .= <<<END_OF_HTML
    [add_to_cart item="$product_id" showprice="no" ajax="yes"
       text="Add to basket"]

END_OF_HTML;
    } else {
      $store_opening_time_human = store_opening_time_human();
      $content .= <<<END_OF_HTML
      The store is currently closed, it will open again on
      $store_opening_time_human.

END_OF_HTML;
    }
    return $content;
  }

  return <<<'END_OF_HTML'
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
function JewelleryPageShortcode(array $atts, string $content,
                                string $tag): string {
  $attrs = shortcode_atts(
    array(
      'archived' => 'false',
      'image_id' => null,
      'name' => null,
      'product_id' => null,
      'range' => null,
      'type' => null,
    ),
    $atts);
  foreach ($attrs as $key => $value) {
    if (is_null($value)) {
      return '<h1>jewellery_page: empty attribute: ' . strval($key) . '</h1>'
        . "\n";
    }
  }
  $attrs['height'] = 0;
  $attrs['width'] = 0;

  // Look up the image(s).
  $image_ids = explode(',', strval($attrs['image_id']));
  /*. array[int][string]int .*/ $images = array();
  foreach ($image_ids as $image_id) {
    $image_id_int = intval($image_id);
    $image_info = wp_get_attachment_image_src($image_id_int, 'product_size');
    $images[] = array(
      'src' => $image_info[0],
      'width' => $image_info[1],
      'height' => $image_info[2],
    );
    if ($image_info[1] > intval($attrs['width'])) {
      $attrs['width'] = $image_info[1];
    }
    if ($image_info[2] > intval($attrs['height'])) {
      $attrs['height'] = $image_info[2];
    }
  }

  // Change "necklace" to "necklaces".
  if (substr(strval($attrs['type']), -1) !== 's') {
    $attrs['type'] = strval($attrs['type']) . 's';
  }
  // Wordpress puts <br /> at the start and end of the content.
  $content = strval(str_replace('<br />', '', $content));

  // Don't make the range part of the name for some ranges.
  $blacklisted_ranges = array('archive', 'singles');
  if (in_array($attrs['range'], $blacklisted_ranges)) {
    $range_in_piece_name = '';
  } else {
    $range_in_piece_name = strval($attrs['range']) . ' ';
  }

  $html = <<<'END_OF_HTML'
<div class="flexboxrow">
  <div id="individual-jewellery-div">

END_OF_HTML;
  if (count($images) > 1) {
    global $CHANGE_IMAGES;
    $CHANGE_IMAGES['#individual-jewellery-image'] = $images;
    add_action('wp_footer', 'ChangeImagesSetupGeneric');
    $html .= <<<'END_OF_HTML'

    <div>
      <ul>

END_OF_HTML;

    foreach ($image_ids as $i => $image_id) {
      $image_id_int = intval($image_id);
      $image_info = wp_get_attachment_image_src($image_id_int, 'thumbnail');
      $src = strval($image_info[0]);
      $name = strval($attrs['name']);
      $width = strval($image_info[1]);
      $height = strval($image_info[2]);
      $html .= <<<END_OF_HTML
        <li><img src="$src"
                 alt="$range_in_piece_name$name"
                 onclick="change_image($i, '#individual-jewellery-image')"
                 width="$width" height="$height" /> </li>

END_OF_HTML;
    }
    $html .= <<<'END_OF_HTML'
      </ul>
    </div>


END_OF_HTML;
  }

  $div_width = strval($attrs['width']);
  $div_height = strval($attrs['height']);
  $name = strval($attrs['name']);
  $src = strval($images[0]['src']);
  $width = strval($images[0]['width']);
  $height = strval($images[0]['height']);
  $html .= <<<END_OF_HTML
    <div width="$div_width" height="$div_height">
      <img id="individual-jewellery-image"
        alt="$range_in_piece_name$name"
        src="$src"
        width="$width" height="$height" />
    </div>
  </div>
  <div id="individual-jewellery-description">
    <p class="highlight larger-text">$range_in_piece_name$name</p>
    <p>$content</p>

END_OF_HTML;

  $html .= MakeBuyButtonForJewelleryPage($attrs);

  $range = strval($attrs['range']);
  $type = strval($attrs['type']);
  $html .= <<<END_OF_HTML

    <p>See other items in this range: <a href="/jewellery/$range/">$range</a></p>
    <p>See other: <a href="/jewellery/$type/">$type</a></p>
    <p>See the items in <a href="/store/cart/">your basket</a></p>
  </div>
</div>

END_OF_HTML;
  // Shortcodes need to be expanded.
  return do_shortcode($html);
}
