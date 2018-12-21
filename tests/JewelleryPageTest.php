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
  function __construct($id) {
    global $MAX_QUANTITIES, $PRICES;
    if (isset($MAX_QUANTITIES[$id])) {
      $this->max_quantity = $MAX_QUANTITIES[$id];
    }
    if (isset($PRICES[$id])) {
      $this->price = $PRICES[$id];
    }
  }

  // Mock functions used by SUT.
  public static function checkInventoryLevelForProduct($id) {
    global $INVENTORY_LEVEL;
    return $INVENTORY_LEVEL[$id];
  }

  // Helper functions used by tests.
  public static function setInventoryLevelForProduct($id, $level) {
    global $INVENTORY_LEVEL;
    $INVENTORY_LEVEL[$id] = $level;
  }
  public static function setMaxQuantity($id, $max) {
    global $MAX_QUANTITIES;
    $MAX_QUANTITIES[$id] = $max;
  }
  public static function setPrice($id, $price) {
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
?>
