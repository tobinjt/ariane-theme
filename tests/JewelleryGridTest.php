<?php
use PHPUnit\Framework\TestCase;
// require_once('src/StoreClosingTimes.php');
// require_once('src/FakeCart66.php');
require_once('src/FakeWordpress.php');
require_once('src/JewelleryGrid.php');

class ParseJewelleryGridContentsTest extends TestCase {
  public function setUp() {
    clear_wordpress_testing_state();
    // clear_cart66_testing_state();
  }

  public function test_parsing() {
    $input = <<<END_OF_INPUT
# This comment will be skipped.
# Format: range|alt|image_id|href|product_id
# Most basic possible line:
name of the range|this is the alt text|11|linky/|7
# Add weird HTML stuff.
<pre>name of the range|this is the alt text|11|linky/|7</pre>
# Extra spaces don't matter.
  name of the range|this is the alt text|11|linky/|7      
# No product ID.
name of the range|this is the alt text|11|linky/
# Handle empty line.

# Missing trailing slash on link gets added.
name of the range|this is the alt text|11|linky|7

END_OF_INPUT;
    # Most basic possible line:
    add_image_info(11, 'grid_size', array('URL', 23, 59));
    $expected = array(
      array(
        'range' => 'name of the range',
        'alt' => 'this is the alt text',
        'image_id' => '11',
        'href' => 'linky/',
        'product_id' => '7',
        'images' => array(
          array(
            'src' => 'URL',
            'width' => 23,
            'height' => 59,
          ),
        ),
      ),
    );
    # The lines should all parse the same as the first, or have minor changes.
    $expected[] = $expected[0];
    $expected[] = $expected[0];
    $expected[] = $expected[0];
    $expected[3]['product_id'] = -1;
    $expected[] = $expected[0];
    $actual = ParseJewelleryGridContents($input);
    $this->assertEquals($expected, $actual);
  }
}
?>
