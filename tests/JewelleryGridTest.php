<?php
use PHPUnit\Framework\TestCase;
require_once('src/StoreClosingTimes.php');
require_once('src/FakeCart66.php');
require_once('src/FakeWordpress.php');
require_once('src/TestHelpers.php');
require_once('src/JewelleryGrid.php');

class ParseJewelleryGridContentsTest extends TestCase {
  public function setUp(): void {
    clear_wordpress_testing_state();
  }

  public function tearDown(): void {
    verify_wordpress_testing_state();
  }

  public function test_parsing() {
    $input = <<<'END_OF_INPUT'
# This comment will be skipped.
# Format: range|alt|image_id|href|product_id
# Most basic possible line:
name of the range 1|this is the alt text 1|11|linky/|7
# Add weird HTML stuff.
<pre>name of the range 2|this is the alt text 2|11|linky/|8</pre>
# Extra spaces don't matter.
        name of the range 3|this is the alt text 3|11|linky/|9
# No product ID.
name of the range 4|this is the alt text 4|11|linky/
# Handle empty line.

# Missing trailing slash on link gets added.
name of the range 5|this is the alt text 5|11|linky|11

END_OF_INPUT;

    add_image_info(11, 'grid_size', array('URL', 23, 59));
    $expected = array(
      new JewelleryGridEntry('name of the range 1',
        'this is the alt text 1', '11', 'linky/', '7'),
      new JewelleryGridEntry('name of the range 2',
        'this is the alt text 2', '11', 'linky/', '8'),
      new JewelleryGridEntry('name of the range 3',
        'this is the alt text 3', '11', 'linky/', '9'),
      new JewelleryGridEntry('name of the range 4',
        'this is the alt text 4', '11', 'linky/', '-1'),
      new JewelleryGridEntry('name of the range 5',
        'this is the alt text 5', '11', 'linky/', '11'),
    );
    $actual = ParseJewelleryGridContents($input);
    $this->assertEquals($expected, $actual);
  }
}

class MakeBuyButtonForJewelleryGridTest extends TestCase {
  public function setUp(): void {
    clear_wordpress_testing_state();
    clear_cart66_testing_state();
    clear_server_variables();
  }

  public function tearDown(): void {
    verify_wordpress_testing_state();
  }

  public function test_negative_product_id() {
    set_url('/jewellery/foo/');
    $content = MakeBuyButtonForJewelleryGrid('-1');
    $this->assertMatchesRegularExpression('/This creates some space underneath./', $content);
  }

  public function test_archived() {
    set_url('/jewellery/archive/');
    $content = MakeBuyButtonForJewelleryGrid('11');
    $this->assertMatchesRegularExpression('/This creates some space underneath./', $content);
  }

  public function test_max_quantity() {
    set_url('/jewellery/foo/');
    Cart66Product::setMaxQuantity(7, 1);
    Cart66Product::setInventoryLevelForProduct(7, 0);
    $content = MakeBuyButtonForJewelleryGrid('7');
    $this->assertEquals("    Sold\n", $content);
  }

  public function test_no_stock() {
    set_url('/jewellery/foo/');
    Cart66Product::setInventoryLevelForProduct(13, 0);
    $content = MakeBuyButtonForJewelleryGrid('13');
    $this->assertMatchesRegularExpression('/This piece is out of stock/', $content);
  }

  public function test_has_stock_store_closed() {
    set_url('/jewellery/foo/');
    set_closing_time('2018-12-23 00:00:00 Europe/Dublin');
    set_opening_time('2018-12-27 00:00:00 Europe/Dublin');
    set_now_for_testing('2018-12-25 00:00:00 Europe/Dublin');
    Cart66Product::setInventoryLevelForProduct(17, 2);
    Cart66Product::setPrice(17, 135);
    $content = MakeBuyButtonForJewelleryGrid('17');
    $this->assertMatchesRegularExpression('/\(store closed\)/', $content);
    $this->assertMatchesRegularExpression('/€135/', $content);
  }

  public function test_has_stock_store_open() {
    set_url('/jewellery/foo/');
    set_closing_time('2018-12-23 00:00:00 Europe/Dublin');
    set_opening_time('2018-12-27 00:00:00 Europe/Dublin');
    set_now_for_testing('2018-12-29 00:00:00 Europe/Dublin');
    Cart66Product::setPrice(19, 234);
    Cart66Product::setInventoryLevelForProduct(19, 3);
    $content = MakeBuyButtonForJewelleryGrid('19');
    $expected = <<<'END_OF_EXPECTED'
                <div class="larger-text">
                  €234
                  [add_to_cart item="19" showprice="no" ajax="yes"
                     text="Add to basket" style="display: inline;"]
                </div>

END_OF_EXPECTED;
    $this->assertEquals($expected, $content);
  }
}

class JewelleryGridShortcodeTest extends TestCase {
  public function setUp(): void {
    clear_wordpress_testing_state();
    clear_cart66_testing_state();
    clear_server_variables();
    set_url('/jewellery/foo/');
    set_closing_time('2018-12-23 00:00:00 Europe/Dublin');
    set_opening_time('2018-12-27 00:00:00 Europe/Dublin');
    set_now_for_testing('2018-12-29 00:00:00 Europe/Dublin');
  }

  public function tearDown(): void {
    verify_wordpress_testing_state();
  }

  public function test_single_images() {
    Cart66Product::setPrice(19, 234);
    Cart66Product::setInventoryLevelForProduct(19, 3);
    add_image_info(11, 'grid_size', array('URL', 23, 59));
    $input = <<<'END_OF_INPUT'
# Format: range|alt|image_id|href|product_id
name of the range|this is the alt text|11|linky/|19

END_OF_INPUT;
    $output = JewelleryGridShortcode(
      array('description' => 'DESCRIPTION'), $input, '');
    $expected = <<<'END_OF_EXPECTED'
        <div id="jewellery-grid">
          <div>
            <p class="grey large-text text-centered">DESCRIPTION</p>
          </div>
          <div id="jewellery-grid-inner" class="flexboxrow">
            <div class="aligncenter jewellery-block">
              <div class="jewellery-picture-container">
                <a href="linky/">
                  <img src="URL" alt="this is the alt text"
                    width="23" height="59"
                    class="aligncenter block" id="item-0-image"/>
                </a>
              </div>
              <div class="larger-text text-centered left-right-margin grey">
                <a href="linky/">name of the range</a>
              </div>
              <div class="text-centered left-right-margin top-bottom-margin grey
                jewellery-text-container">
                <div class="larger-text">
                  €234
                  [add_to_cart item="19" showprice="no" ajax="yes"
                     text="Add to basket" style="display: inline;"]
                </div>
              </div>
            </div>
          </div>
        </div>

END_OF_EXPECTED;
    $output = $this->add_numbers($output);
    $expected = $this->add_numbers($expected);
    $this->assertEquals($expected, $output);
  }

  public function test_multiple_images_and_pieces() {
    expect_add_action('wp_footer', 'SliderSetupGeneric', 1);
    // First range.
    Cart66Product::setPrice(19, 234);
    Cart66Product::setInventoryLevelForProduct(19, 3);
    add_image_info(11, 'grid_size', array('URL', 23, 59));
    // Second range.
    Cart66Product::setPrice(53, 321);
    Cart66Product::setInventoryLevelForProduct(53, 41);
    add_image_info(13, 'grid_size', array('URLX', 23, 59));
    add_image_info(17, 'grid_size', array('URLY', 29, 61));
    add_image_info(23, 'grid_size', array('URLZ', 31, 67));
    $input = <<<'END_OF_INPUT'
# Format: range|alt|image_id|href|product_id
name of the range|this is the alt text|11|linky/|19
range range range|alt text for second range|13,17,23|linky/|53

END_OF_INPUT;
    $output = JewelleryGridShortcode(
      array('description' => 'DESCRIPTION'), $input, '');
    $expected = <<<'END_OF_EXPECTED'
        <div id="jewellery-grid">
          <div>
            <p class="grey large-text text-centered">DESCRIPTION</p>
          </div>
          <div id="jewellery-grid-inner" class="flexboxrow">
            <div class="aligncenter jewellery-block">
              <div class="jewellery-picture-container">
                <a href="linky/">
                  <img src="URL" alt="this is the alt text"
                    width="23" height="59"
                    class="aligncenter block" id="item-0-image"/>
                </a>
              </div>
              <div class="larger-text text-centered left-right-margin grey">
                <a href="linky/">name of the range</a>
              </div>
              <div class="text-centered left-right-margin top-bottom-margin grey
                jewellery-text-container">
                <div class="larger-text">
                  €234
                  [add_to_cart item="19" showprice="no" ajax="yes"
                     text="Add to basket" style="display: inline;"]
                </div>
              </div>
            </div>
            <div class="aligncenter jewellery-block">
              <div class="jewellery-picture-container">
                <a href="linky/">
                  <img src="URLX" alt="alt text for second range"
                    width="23" height="59"
                    class="aligncenter block" id="item-1-image"/>
                </a>
              </div>
              <div class="larger-text text-centered left-right-margin grey">
                <a href="linky/">range range range</a>
              </div>
              <div class="text-centered left-right-margin top-bottom-margin grey
                jewellery-text-container">
                <div class="larger-text">
                  €321
                  [add_to_cart item="53" showprice="no" ajax="yes"
                     text="Add to basket" style="display: inline;"]
                </div>
              </div>
            </div>
          </div>
        </div>

END_OF_EXPECTED;
    $output = $this->add_numbers($output);
    $expected = $this->add_numbers($expected);
    $this->assertEquals($expected, $output);
    $expected_slider = array(
      '#item-1' => ('[{"src":"URLX","width":23,"height":59},'
                    . '{"src":"URLY","width":29,"height":61},'
                    . '{"src":"URLZ","width":31,"height":67}]'),
    );
    global $SLIDER_IMAGES;
    $this->assertEquals($expected_slider, $SLIDER_IMAGES);
  }

  public function add_numbers(string $content): string {
    $lines = explode("\n", $content);
    $new_lines = array();
    foreach ($lines as $i => $line) {
      $new_lines[] = $i . ' ' . $line;
    }
    return implode("\n", $new_lines);
  }
}
