<?php
declare(strict_types=1);

// Support for Jewellery grids showing multiple products.

// Extras needed by PHPLint.
/*. require_module 'array'; .*/
/*. require_module 'core'; .*/
/*. require_module 'fakecart66'; .*/
/*. require_module 'pcre'; .*/
require_once(__DIR__ . '/Cast.php');
require_once(__DIR__ . '/DataStructures.php');
require_once(__DIR__ . '/StoreClosingTimes.php');
require_once(__DIR__ . '/Urls.php');
/*. array[int][string]string .*/ $CHANGE_IMAGES = array();
/*. array[string]string .*/ $SLIDER_IMAGES = array();

/* ParseJewelleryGridContents: turn the CSV from page contents into a data
 * structure.
 * Args:
 *  $page_contents: string, the contents of the page.  First line (CSV header)
 *    will be removed.  Blank lines will be skipped.  <br /> will be stripped
 *    from the end of each line.
 * Returns:
 *  array, data structure to process.
 */
function ParseJewelleryGridContents(string $page_contents): array {
  $lines = str_getcsv($page_contents, "\n");
  /*. array[int]JewelleryGridEntry .*/ $ranges = array();
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
    // Awful hack to work around wordpress turning 276x300 into 276!300, where
    // ! is actually some weird unicode x - this breaks image urls. ARGH.
    $line = preg_replace('/&#215;/', 'x', $line);
    $csv_data = str_getcsv($line, '|');
    // Skip blank lines.  The CSV parser will return an array with a single
    // element when given a blank line.
    if (count($csv_data) === 1) {
      continue;
    }
    // Line format:
    // * Range name|Image description|Image ID(s)|Link to page|Product ID
    // * The top-level jewellery page links to ranges rather than products, so
    //   we can't include purchasing.  We use -1 to indicate that there isn't a
    //   product to offer, and that's checked for later.
    if (count($csv_data) < 5) {
      $csv_data[] = '-1';
    }
    $ranges[] = new JewelleryGridEntry($csv_data[0], $csv_data[1], $csv_data[2],
      $csv_data[3], intval($csv_data[4]));
  }
  return $ranges;
}

/* MakeBuyButtonForJewelleryGrid: make a buy button or a message or whatever
 * is appropriate for the product in the jewellery grid.
 * Args:
 *  $product_id: id of the product in Cart66.
 * Returns:
 *  string, HTML to insert in page.
 */
function MakeBuyButtonForJewelleryGrid(int $product_id): string {
  // -1 means there isn't a product to sell, and that happens on the main
  // jewellery page.
  // Skip showing cart buttons for everything that's been archived.
  if ($product_id === -1 || is_archive_page()) {
    return <<<'END_OF_NO_PRODUCT_OR_ARCHIVE'
    <!-- This creates some space underneath. -->

END_OF_NO_PRODUCT_OR_ARCHIVE;
  }

  $product = new Cart66Product($product_id);
  if (Cart66Product::checkInventoryLevelForProduct($product_id) === 0) {
    if ($product->max_quantity === 1) {
      return <<<'END_OF_SOLD'
    Sold

END_OF_SOLD;
    }

    return <<<'END_OF_OUT_OF_STOCK'
    This piece is out of stock, please contact Ariane as it's possible this
    item could be made to order.

END_OF_OUT_OF_STOCK;
  }

  $price = $product->price;
  $content = <<<END_OF_DIV_TAG
                <div class="larger-text">

END_OF_DIV_TAG;
  if ($price > 0) {
    $content .= <<<END_OF_PRICE
                  €$price

END_OF_PRICE;
  if (!is_store_closed()) {
      $content .= <<<END_OF_BUY
                  [add_to_cart item="$product_id" showprice="no" ajax="yes"
                     text="Add to basket" style="display: inline;"]

END_OF_BUY;
    } else {
      $content .= <<<'END_OF_CLOSED'
                  (store closed)

END_OF_CLOSED;
    }
  } else {
    $content .= <<<END_OF_PRICE
                  Price on request.

END_OF_PRICE;
  }
  $content .= <<<'END_OF_DIV'
                </div>

END_OF_DIV;
  return $content;
}

/* JewelleryGridShortcode: create a table from CSV content.
 * This *must* be used in the enclosing form.
 * Args (names are ugly but Wordpress-standard):
 *  $atts: associative array of attributes, optionally containing $description:
 *    string, the description to display at the top of the page.  If the string
 *    is empty no description will be added.
 *  $content: string, the contents of the page.  Blank lines will be skipped.
 *    <br /> will be stripped from the end of each line.
 *  $tag: unused.
 * Returns:
 *  string, the HTML to insert in the page (Wordpress does that automatically).
 */
function JewelleryGridShortcode(array $atts, string $content,
                                string $tag): string {
  $tag .= 'make the linter happy.';
  $attrs = cast('array[string]string', shortcode_atts(
    array(
      'description' => '',
    ),
    $atts));

  $description = $attrs['description'];
  $attrs = array('do not use' => 'dollar_attrs');
  $ranges = cast('array[int]JewelleryGridEntry',
    ParseJewelleryGridContents($content));
  // Turn the data structure into <divs>s.
  /*. array[int]string .*/ $divs = array();
  $slider_needed = false;
  foreach ($ranges as $i => $entry) {
    $id = 'item-' . $i;
    if (count($entry->images) > 1) {
      global $SLIDER_IMAGES;
      $SLIDER_IMAGES['#' . $id] = json_encode_wrapper($entry->images_to_data());
      $slider_needed = true;
    }

    $href = $entry->page_url;
    $src = $entry->images[0]->url;
    $alt = $entry->alt;
    $width = $entry->images[0]->width_str;
    $height = $entry->images[0]->height_str;
    $range = $entry->range;
    $div = <<<END_OF_IMAGE_AND_RANGE
            <div class="aligncenter jewellery-block">
              <div class="jewellery-picture-container">
                <a href="$href">
                  <img src="$src" alt="$alt"
                    width="$width" height="$height"
                    class="aligncenter block" id="$id-image"/>
                </a>
              </div>
              <div class="larger-text text-centered left-right-margin grey">
                <a href="$href">$range</a>
              </div>

END_OF_IMAGE_AND_RANGE;
    $div .= <<<'END_OF_OPEN_BUY_DIV'
              <div class="text-centered left-right-margin top-bottom-margin grey
                jewellery-text-container">

END_OF_OPEN_BUY_DIV;
    $div .= MakeBuyButtonForJewelleryGrid($entry->product_id);
    $div .= <<<'END_OF_DIV'
              </div>
            </div>
END_OF_DIV;
    $divs[] = $div;
  }

  /*. array[int]string .*/ $html = array();
  $html[] = <<<'END_OF_HTML'
        <div class="jewellery-grid">
END_OF_HTML;
  if ($description !== '') {
    $html[] = <<<END_OF_DESCRIPTION
          <div>
            <p class="grey large-text text-centered">$description</p>
          </div>
END_OF_DESCRIPTION;
  }
  $html[] = <<<'END_OF_HTML'
          <div class="flexboxrow jewellery-grid-inner">
END_OF_HTML;
  $html = cast('array[int]string', array_merge($html, $divs));
  $html[] = <<<'END_OF_HTML'
          </div>
        </div>
END_OF_HTML;
  if ($slider_needed) {
    add_action('wp_footer', 'SliderSetupGeneric');
  }
  // Add a newline.
  $html[] = '';
  return do_shortcode(implode("\n", $html));
}
