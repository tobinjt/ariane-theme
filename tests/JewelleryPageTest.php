<?php
use PHPUnit\Framework\TestCase;
require_once('src/StoreClosingTimes.php');
require_once('src/JewelleryPage.php');

global $INVENTORY_LEVEL, $MAX_QUANTITIES, $PRICES;
$INVENTORY_LEVEL = array();
$MAX_QUANTITIES = array();
$PRICES = array();

class Cart66Product {
  public $max_quantity = 0;
  public $price = 0;
  function __construct(int $id) {
    global $MAX_QUANTITIES, $PRICES;
    if (isset($MAX_QUANTITIES[$id])) {
      $this->max_quantity = $MAX_QUANTITIES[$id];
    }
    if (isset($PRICES[$id])) {
      $this->price = $PRICES[$id];
    }
  }

  // Mock functions used by SUT.
  public static function checkInventoryLevelForProduct(int $id) {
    global $INVENTORY_LEVEL;
    return $INVENTORY_LEVEL[$id];
  }

  // Helper functions used by tests.
  public static function setInventoryLevelForProduct(int $id, int $level) {
    global $INVENTORY_LEVEL;
    $INVENTORY_LEVEL[$id] = $level;
  }
  public static function setMaxQuantity(int $id, int $max) {
    global $MAX_QUANTITIES;
    $MAX_QUANTITIES[$id] = $max;
  }
  public static function setPrice(int $id, int $price) {
    global $PRICES;
    $PRICES[$id] = $price;
  }
}

class MakeBuyButtonForJewelleryPageTest extends TestCase {
  public function test_max_quantity() {
    $attrs = ['product_id' => 7];
    Cart66Product::setMaxQuantity($attrs['product_id'], 1);
    $content = MakeBuyButtonForJewelleryPage($attrs);
    $this->assertRegExp('/unique piece of jewellery has been sold./', $content);
  }

  public function test_archived() {
    $attrs = ['product_id' => 11, 'archived' => 'yes'];
    $content = MakeBuyButtonForJewelleryPage($attrs);
    $this->assertRegExp('/piece of jewellery is no longer being sold/',
      $content);
  }

  public function test_no_stock() {
    $attrs = ['product_id' => 13, 'archived' => 'false'];
    Cart66Product::setInventoryLevelForProduct($attrs['product_id'], 0);
    $content = MakeBuyButtonForJewelleryPage($attrs);
    $this->assertRegExp('/This piece is out of stock/', $content);
  }

  public function test_has_stock_store_closed() {
    set_closing_time('2018-12-23 00:00:00 Europe/Dublin');
    set_opening_time('2018-12-27 00:00:00 Europe/Dublin');
    set_now_for_testing('2018-12-25 00:00:00 Europe/Dublin');
    $attrs = ['product_id' => 17, 'archived' => 'false'];
    Cart66Product::setInventoryLevelForProduct($attrs['product_id'], 2);
    Cart66Product::setPrice($attrs['product_id'], 135);
    $content = MakeBuyButtonForJewelleryPage($attrs);
    $this->assertRegExp('/The store is currently closed/', $content);
    $this->assertRegExp('/Price: €135/', $content);
  }

  public function test_has_stock_store_open() {
    set_closing_time('2018-12-23 00:00:00 Europe/Dublin');
    set_opening_time('2018-12-27 00:00:00 Europe/Dublin');
    set_now_for_testing('2018-12-29 00:00:00 Europe/Dublin');
    $attrs = ['product_id' => 19, 'archived' => 'false'];
    Cart66Product::setPrice($attrs['product_id'], 234);
    Cart66Product::setInventoryLevelForProduct($attrs['product_id'], 3);
    $content = MakeBuyButtonForJewelleryPage($attrs);
    $this->assertRegExp('/add_to_cart item="19" showprice="no"/', $content);
    $this->assertRegExp('/Price: €234/', $content);
  }
}

global $IMAGE_INFO;
$IMAGE_INFO = array();

// Wordpress functions we need to fake.
function shortcode_atts(array $array1, array $array2): array {
  return array_merge($array1, $array2);
}

function do_shortcode(string $content): string {
  return $content;
}

function add_image_info(string $image_id, string $size, array $info) {
  global $IMAGE_INFO;
  $IMAGE_INFO[$image_id][$size] = $info;
}

function wp_get_attachment_image_src(int $image_id, string $size): array {
  global $IMAGE_INFO;
  return $IMAGE_INFO[$image_id][$size];
}

class JewelleryPageShortcodeTest extends TestCase {
  // Get a reasonable set of attrs to pass to JewelleryPageShortcode.
  public function get_attrs(): array {
    return array(
      'archived' => 'false',
      'image_id' => null,
      'limited_to' => '0',
      'name' => 'name should be set',
      'product_id' => null,
      'range' => 'range should be set',
      'type' => 'type should be set',
    );
  }

  public function set_up_MakeBuyButton(int $product_id, int $price,
    int $stock_level) {
    set_closing_time('2018-12-23 00:00:00 Europe/Dublin');
    set_opening_time('2018-12-27 00:00:00 Europe/Dublin');
    set_now_for_testing('2018-12-29 00:00:00 Europe/Dublin');
    Cart66Product::setPrice($product_id, $price);
    Cart66Product::setInventoryLevelForProduct($product_id, $stock_level);
    Cart66Product::setMaxQuantity($product_id, 17);
  }

  public function test_missing_attr() {
    $content = JewelleryPageShortcode([], '', '');
    $this->assertRegExp('/jewellery_page: empty attribute/', $content);
  }

  public function test_single_image() {
    global $CHANGE_IMAGES;
    $CHANGE_IMAGES['#individual-jewellery-image'] = null;
    $attrs = $this->get_attrs();
    $attrs['image_id'] = 3;
    $attrs['product_id'] = 7;
    add_image_info($attrs['image_id'], 'product_size', array('URL', 23, 59));
    $this->set_up_MakeBuyButton($attrs['product_id'], 123, 11);
    $content = JewelleryPageShortcode($attrs, 'description of piece', '');
    $this->assertNull($CHANGE_IMAGES['#individual-jewellery-image']);
    $expected = <<<EXPECTED
<div class="flexboxrow">
  <div id="individual-jewellery-div">
    <div width="23" height="59">
      <img id="individual-jewellery-image"
        alt="range should be set name should be set"
        src="URL"
        width="23" height="59" />
    </div>
  </div>
  <div id="individual-jewellery-description">
    <p class="highlight larger-text">range should be set name should be set</p>
    <p>description of piece</p>
    <p>Price: €123.</p>
    [add_to_cart item="7" showprice="no" ajax="yes"
       text="Add to basket"]

    <p>See other items in this range: <a href="/jewellery/range should be set/">range should be set</a></p>
    <p>See other: <a href="/jewellery/type should be sets/">type should be sets</a></p>
    <p>See the items in <a href="/store/cart/">your basket</a></p>
  </div>
</div>

EXPECTED;
    $this->assertEquals($expected, $content);
  }

  public function test_multiple_images() {
    global $CHANGE_IMAGES;
    $CHANGE_IMAGES['#individual-jewellery-image'] = null;
    $attrs = $this->get_attrs();
    $attrs['image_id'] = '3,79,37';
    $attrs['product_id'] = 7;
    $attrs['range'] = 'singles';  # Test that range isn't included for singles.
    add_image_info(3, 'product_size', array('URL', 23, 59));
    add_image_info(79, 'product_size', array('URL2', 41, 83));
    add_image_info(37, 'product_size', array('URL3', 47, 97));
    add_image_info(3, 'thumbnail', array('thumb', 25, 57));
    add_image_info(79, 'thumbnail', array('thumb2', 44, 80));
    add_image_info(37, 'thumbnail', array('thumb3', 51, 93));
    $this->set_up_MakeBuyButton($attrs['product_id'], 543, 15);
    $content = JewelleryPageShortcode($attrs, '<br /> asdf', '');
    $expected_array = array(
      array('src' => 'URL', 'width' => 23, 'height' => 59),
      array('src' => 'URL2', 'width' => 41, 'height' => 83),
      array('src' => 'URL3', 'width' => 47, 'height' => 97),
    );
    $this->assertEquals($expected_array,
      $CHANGE_IMAGES['#individual-jewellery-image']);
    $expected = <<<EXPECTED
<div class="flexboxrow">
  <div id="individual-jewellery-div">

    <div>
      <ul>
        <li><img src="thumb"
                 alt="name should be set"
                 onclick="change_image(0, '#individual-jewellery-image')"
                 width="25" height="57" /> </li>
        <li><img src="thumb2"
                 alt="name should be set"
                 onclick="change_image(1, '#individual-jewellery-image')"
                 width="44" height="80" /> </li>
        <li><img src="thumb3"
                 alt="name should be set"
                 onclick="change_image(2, '#individual-jewellery-image')"
                 width="51" height="93" /> </li>
      </ul>
    </div>
    [change_images]

    <div width="47" height="97">
      <img id="individual-jewellery-image"
        alt="name should be set"
        src="URL"
        width="23" height="59" />
    </div>
  </div>
  <div id="individual-jewellery-description">
    <p class="highlight larger-text">name should be set</p>
    <p> asdf</p>
    <p>Price: €543.</p>
    [add_to_cart item="7" showprice="no" ajax="yes"
       text="Add to basket"]

    <p>See other items in this range: <a href="/jewellery/singles/">singles</a></p>
    <p>See other: <a href="/jewellery/type should be sets/">type should be sets</a></p>
    <p>See the items in <a href="/store/cart/">your basket</a></p>
  </div>
</div>

EXPECTED;
    $this->assertEquals($expected, $content);
  }
}
?>
