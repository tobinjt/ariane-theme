<?php
use PHPUnit\Framework\TestCase;
require_once('src/FakeWordpress.php');
require_once('src/JewelleryPage.php');

class JewelleryPageTest extends TestCase {
  public function setUp(): void {
    clear_wordpress_testing_state();
  }

  public function tearDown(): void {
    verify_wordpress_testing_state();
  }

  // Get a reasonable set of attrs to pass to JewelleryPageShortcode.
  /**
   * @return array<string, string>
   */
  public function get_attrs(): array {
    return array(
      'archived' => 'false',
      'image_id' => '-1',
      'name' => 'name should be set',
      'product_id' => '-1',
      'range' => 'range should be set',
      'type' => 'type should be set',
    );
  }

  public function test_missing_attr(): void {
    $content = JewelleryPageShortcode([], '', '');
    $this->assertMatchesRegularExpression(
      '/jewellery_page: empty attribute/', $content);
  }

  public function test_single_image(): void {
    clear_change_images();
    $attrs = $this->get_attrs();
    $attrs['image_id'] = '3';
    $attrs['product_id'] = '7';
    add_image_info(
      intval($attrs['image_id']), 'product_size', array('URL', 23, 59));
    $content = JewelleryPageShortcode($attrs, 'description of piece', '');
    $this->assertEquals([], get_change_images());
    $expected = <<<'EXPECTED'
<div class="flexboxrow">
  <div class="individual-jewellery-div">
    <div width="23" height="59">
      <img id="individual-jewellery-image"
        class="block aligncenter"
        alt="range should be set name should be set"
        src="URL"
        width="23" height="59" />
    </div>
  </div>
  <div class="individual-jewellery-description">
    <p class="highlight larger-text">range should be set name should be set</p>
    <p>description of piece</p>

    <p>See other items in this range: <a href="/jewellery/range should be set/">
      range should be set</a></p>
    <p>See other: <a href="/jewellery/type should be sets/">type should be sets</a></p>
  </div>
</div>

EXPECTED;
    $this->assertEquals($expected, $content);
  }

  public function test_no_price(): void {
    // This tests that "Price on request" is displayed and the add to cart
    // button is not displayed.
    clear_change_images();
    $attrs = $this->get_attrs();
    $attrs['image_id'] = '3';
    $attrs['product_id'] = '7';
    add_image_info(
      intval($attrs['image_id']), 'product_size', array('URL', 23, 59));
    $content = JewelleryPageShortcode($attrs, 'description of piece', '');
    $this->assertEquals([], get_change_images());
    $expected = <<<'EXPECTED'
<div class="flexboxrow">
  <div class="individual-jewellery-div">
    <div width="23" height="59">
      <img id="individual-jewellery-image"
        class="block aligncenter"
        alt="range should be set name should be set"
        src="URL"
        width="23" height="59" />
    </div>
  </div>
  <div class="individual-jewellery-description">
    <p class="highlight larger-text">range should be set name should be set</p>
    <p>description of piece</p>

    <p>See other items in this range: <a href="/jewellery/range should be set/">
      range should be set</a></p>
    <p>See other: <a href="/jewellery/type should be sets/">type should be sets</a></p>
  </div>
</div>

EXPECTED;
    $this->assertEquals($expected, $content);
  }

  public function test_multiple_images(): void {
    clear_change_images();
    $attrs = $this->get_attrs();
    $attrs['image_id'] = '3,79,37';
    $attrs['product_id'] = '7';
    $attrs['range'] = 'singles';  // Test that range isn't included for singles.
    expect_add_action('wp_footer', 'ChangeImagesSetupGeneric');
    add_image_info(3, 'product_size', array('URL', 23, 59));
    add_image_info(79, 'product_size', array('URL2', 41, 83));
    add_image_info(37, 'product_size', array('URL3', 47, 97));
    add_image_info(3, 'thumbnail', array('thumb', 25, 57));
    add_image_info(79, 'thumbnail', array('thumb2', 44, 80));
    add_image_info(37, 'thumbnail', array('thumb3', 51, 93));
    $content = JewelleryPageShortcode($attrs, '<br /> asdf', '');
    $expected_array = [
      '#individual-jewellery-image' => json_encode_wrapper([
          ['src' => 'URL', 'width' => 23, 'height' => 59],
          ['src' => 'URL2', 'width' => 41, 'height' => 83],
          ['src' => 'URL3', 'width' => 47, 'height' => 97],
      ]),
    ];
    $this->assertEquals($expected_array, get_change_images());
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
        class="block aligncenter"
        alt="name should be set"
        src="URL"
        width="23" height="59" />
    </div>
  </div>
  <div class="individual-jewellery-description">
    <p class="highlight larger-text">name should be set</p>
    <p> asdf</p>

    <p>See other items in this range: <a href="/jewellery/singles/">
      singles</a></p>
    <p>See other: <a href="/jewellery/type should be sets/">type should be sets</a></p>
  </div>
</div>

EXPECTED;
    $this->assertEquals($expected, $content);
  }

  public function test_skipped_images(): void {
    clear_change_images();
    $attrs = $this->get_attrs();
    $attrs['image_id'] = '3,-1,37';
    $attrs['product_id'] = '7';
    $attrs['range'] = 'singles';  // Test that range isn't included for singles.
    expect_add_action('wp_footer', 'ChangeImagesSetupGeneric');
    add_image_info(3, 'product_size', array('URL', 23, 59));
    add_image_info(37, 'product_size', array('URL3', 47, 97));
    add_image_info(3, 'thumbnail', array('thumb', 25, 57));
    add_image_info(37, 'thumbnail', array('thumb3', 51, 93));
    $content = JewelleryPageShortcode($attrs, '<br /> asdf', '');
    $expected_array = [
      '#individual-jewellery-image' => json_encode_wrapper([
          ['src' => 'URL', 'width' => 23, 'height' => 59],
          ['src' => 'URL3', 'width' => 47, 'height' => 97],
      ]),
    ];
    $this->assertEquals($expected_array, get_change_images());
  }
}
