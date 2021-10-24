<?php
use PHPUnit\Framework\TestCase;
require_once('src/StoreClosingTimes.php');
require_once('src/FakeCart66.php');
require_once('src/FakeWordpress.php');
require_once('src/JewelleryPage.php');

class MakeBuyButtonForJewelleryPageTest extends TestCase {
  public function setUp(): void {
    clear_wordpress_testing_state();
    clear_cart66_testing_state();
  }

  public function tearDown(): void {
    verify_wordpress_testing_state();
  }

  public function test_max_quantity() {
    $jewellery_page = new JewelleryPage('test name', 7, 'test range',
      'test type', '-1', false);
    Cart66Product::setMaxQuantity($jewellery_page->product_id, 1);
    $content = MakeBuyButtonForJewelleryPage($jewellery_page);
    $this->assertMatchesRegularExpression('/unique piece of jewellery has been sold./', $content);
  }

  public function test_archived() {
    $jewellery_page = new JewelleryPage('test name', 11, 'test range',
      'test type', '-1', true);
    $content = MakeBuyButtonForJewelleryPage($jewellery_page);
    $this->assertMatchesRegularExpression('/piece of jewellery is no longer being sold/',
      $content);
  }

  public function test_no_stock() {
    $jewellery_page = new JewelleryPage('test name', 13, 'test range',
      'test type', '-1', false);
    Cart66Product::setInventoryLevelForProduct($jewellery_page->product_id, 0);
    $content = MakeBuyButtonForJewelleryPage($jewellery_page);
    $this->assertMatchesRegularExpression('/This piece is out of stock/', $content);
  }

  public function test_has_stock_store_closed() {
    set_closing_time('2018-12-23 00:00:00 Europe/Dublin');
    set_opening_time('2018-12-27 00:00:00 Europe/Dublin');
    set_now_for_testing('2018-12-25 00:00:00 Europe/Dublin');
    $jewellery_page = new JewelleryPage('test name', 17, 'test range',
      'test type', '-1', false);
    Cart66Product::setInventoryLevelForProduct($jewellery_page->product_id, 2);
    Cart66Product::setPrice($jewellery_page->product_id, 135);
    $content = MakeBuyButtonForJewelleryPage($jewellery_page);
    $this->assertMatchesRegularExpression('/The store is currently closed/', $content);
    $this->assertMatchesRegularExpression('/Price: €135/', $content);
  }

  public function test_has_stock_store_open() {
    set_closing_time('2018-12-23 00:00:00 Europe/Dublin');
    set_opening_time('2018-12-27 00:00:00 Europe/Dublin');
    set_now_for_testing('2018-12-29 00:00:00 Europe/Dublin');
    $jewellery_page = new JewelleryPage('test name', 19, 'test range',
      'test type', '-1', false);
    Cart66Product::setPrice($jewellery_page->product_id, 234);
    Cart66Product::setInventoryLevelForProduct($jewellery_page->product_id, 3);
    $content = MakeBuyButtonForJewelleryPage($jewellery_page);
    $this->assertMatchesRegularExpression('/add_to_cart item="19" showprice="no"/', $content);
    $this->assertMatchesRegularExpression('/Price: €234/', $content);
  }
}

class JewelleryPageShortcodeTest extends TestCase {
  public function setUp(): void {
    clear_wordpress_testing_state();
    clear_cart66_testing_state();
  }

  public function tearDown(): void {
    verify_wordpress_testing_state();
  }

  // Get a reasonable set of attrs to pass to JewelleryPageShortcode.
  public function get_attrs(): array {
    return array(
      'archived' => 'false',
      'image_id' => null,
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
    $this->assertMatchesRegularExpression('/jewellery_page: empty attribute/', $content);
  }

  public function test_single_image() {
    global $CHANGE_IMAGES;
    $CHANGE_IMAGES['#individual-jewellery-image'] = null;
    $attrs = $this->get_attrs();
    $attrs['image_id'] = '3';
    $attrs['product_id'] = '7';
    add_image_info($attrs['image_id'], 'product_size', array('URL', 23, 59));
    $this->set_up_MakeBuyButton($attrs['product_id'], 123, 11);
    $content = JewelleryPageShortcode($attrs, 'description of piece', '');
    $this->assertNull($CHANGE_IMAGES['#individual-jewellery-image']);
    $expected = <<<'EXPECTED'
<div class="flexboxrow">
  <div class="individual-jewellery-div">
    <div width="23" height="59">
      <img id="individual-jewellery-image"
        class="individual-jewellery-image aligncenter"
        alt="range should be set name should be set"
        src="URL"
        width="23" height="59" />
    </div>
  </div>
  <div class="individual-jewellery-description">
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

  public function test_no_price() {
    // This tests that "Price on request" is displayed and the add to cart
    // button is not displayed.
    global $CHANGE_IMAGES;
    $CHANGE_IMAGES['#individual-jewellery-image'] = null;
    $attrs = $this->get_attrs();
    $attrs['image_id'] = '3';
    $attrs['product_id'] = '7';
    add_image_info($attrs['image_id'], 'product_size', array('URL', 23, 59));
    $this->set_up_MakeBuyButton($attrs['product_id'], 0, 11);
    $content = JewelleryPageShortcode($attrs, 'description of piece', '');
    $this->assertNull($CHANGE_IMAGES['#individual-jewellery-image']);
    $expected = <<<'EXPECTED'
<div class="flexboxrow">
  <div class="individual-jewellery-div">
    <div width="23" height="59">
      <img id="individual-jewellery-image"
        class="individual-jewellery-image aligncenter"
        alt="range should be set name should be set"
        src="URL"
        width="23" height="59" />
    </div>
  </div>
  <div class="individual-jewellery-description">
    <p class="highlight larger-text">range should be set name should be set</p>
    <p>description of piece</p>
    <p>Price on request.</p>

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
    $attrs['product_id'] = '7';
    $attrs['range'] = 'singles';  // Test that range isn't included for singles.
    expect_add_action('wp_footer', 'ChangeImagesSetupGeneric', 1);
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
    $expected = <<<'EXPECTED'
<div class="flexboxrow">
  <div class="individual-jewellery-div">

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

    <div width="47" height="97">
      <img id="individual-jewellery-image"
        class="individual-jewellery-image aligncenter"
        alt="name should be set"
        src="URL"
        width="23" height="59" />
    </div>
  </div>
  <div class="individual-jewellery-description">
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
