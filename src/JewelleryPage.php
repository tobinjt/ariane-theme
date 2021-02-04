<?php
declare(strict_types=1);

// Support for showing an individual piece of Jewellery.

// Extras needed by PHPLint.
/*. require_module 'core'; .*/
/*. require_module 'fakecart66'; .*/
/*. require_module 'wordpress'; .*/
require_once(__DIR__ . '/DataStructures.php');
require_once(__DIR__ . '/StoreClosingTimes.php');
/*. array[string][int][string]string .*/ $CHANGE_IMAGES = array();

/* MakeBuyButtonForJewelleryPage: make a buy button or a message or whatever
 * is appropriate for the product in the jewellery page.
 * Args:
 *  $jewellery_page: the product.
 * Returns:
 *  string, HTML to insert in page.
 */
function MakeBuyButtonForJewelleryPage(JewelleryPage $jewellery_page): string {
  $product = new Cart66Product($jewellery_page->product_id);
  if ($product->max_quantity == 1) {
    return <<<'END_OF_HTML'
        <p>Unfortunately this unique piece of jewellery has been sold.  See
          below for other items in this range or type.</p>
END_OF_HTML;
  }

  if ($jewellery_page->archived) {
    return <<<'END_OF_HTML'
        <p>Unfortunately this piece of jewellery is no longer being sold.  See
          below for other items in this range or type.</p>
END_OF_HTML;
  }

  if (Cart66Product::checkInventoryLevelForProduct($jewellery_page->product_id) <= 0) {
    return <<<'END_OF_HTML'
    <p>This piece is out of stock, please contact Ariane as it's possible this
      item could be made to order.  See below for other items in this range or
      type.</p>
END_OF_HTML;
  }

  $price = $product->price;
  if ($price <= 0) {
    return <<<'END_OF_HTML'
    <p>Price on request.</p>

END_OF_HTML;
  }

  $content = <<<END_OF_HTML
    <p>Price: €$price.</p>

END_OF_HTML;
  if (!is_store_closed()) {
    $product_id = $jewellery_page->product_id;
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
  $tag .= 'make the linter happy.';
  $attrs = cast('array[string]string', shortcode_atts(
    array(
      'archived' => 'false',
      'image_id' => '',
      'name' => '',
      'product_id' => '',
      'range' => '',
      'type' => '',
    ),
    $atts));
  foreach ($attrs as $key => $value) {
    if ($value === '') {
      return "<h1>jewellery_page: empty attribute: $key </h1>\n";
    }
  }

  $jewellery_page = new JewelleryPage($attrs['name'],
    intval($attrs['product_id']), $attrs['range'], $attrs['type'],
    $attrs['image_id'], $attrs['archived'] !== 'false');
  $attrs = array('do not use' => 'dollar_attrs');

  // Wordpress puts <br /> at the start and end of the content.
  $content = strval(str_replace('<br />', '', $content));

  // Don't make the range part of the name for some ranges.
  $excluded_ranges = array('archive', 'singles');
  if (in_array($jewellery_page->range, $excluded_ranges)) {
    $range_in_piece_name = '';
  } else {
    $range_in_piece_name = $jewellery_page->range . ' ';
  }

  $html = <<<'END_OF_HTML'
<div class="flexboxrow">
  <div id="individual-jewellery-div">

END_OF_HTML;
  if (count($jewellery_page->images) > 1) {
    global $CHANGE_IMAGES;
    $CHANGE_IMAGES['#individual-jewellery-image'] = cast(
      'array[int][string]string', $jewellery_page->images_to_data());
    add_action('wp_footer', 'ChangeImagesSetupGeneric');
    $html .= <<<'END_OF_HTML'

    <div>
      <ul>

END_OF_HTML;

    foreach ($jewellery_page->image_ids as $i => $image_id) {
      $image = new WPImageInfo($image_id, 'thumbnail');
      $src = $image->url;
      $name = $jewellery_page->name;
      $width = $image->width_str;
      $height = $image->height_str;
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

  $div_width = $jewellery_page->width_str;
  $div_height = $jewellery_page->height_str;
  $name = $jewellery_page->name;
  $src = $jewellery_page->images[0]->url;
  $width = $jewellery_page->images[0]->width_str;
  $height = $jewellery_page->images[0]->height_str;
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

  $html .= MakeBuyButtonForJewelleryPage($jewellery_page);

  $range = $jewellery_page->range;
  $type = $jewellery_page->type;
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
