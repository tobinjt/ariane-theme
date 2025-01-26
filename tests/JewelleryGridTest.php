<?php

use PHPUnit\Framework\TestCase;

require_once('src/FakeWordpress.php');
require_once('src/FakeWP_Query.php');
require_once('src/Functions.php');
require_once('src/JewelleryGrid.php');
require_once('src/MultipleImageSupport.php');
require_once('src/TestHelpers.php');
require_once('src/WPImageInfo.php');

class JewelleryGridTest extends TestCase
{
    public function setUp(): void
    {
        clear_fake_wordpress_state();
        clear_server_variables();
        clear_wordpress_testing_state();
        set_url('/jewellery/foo/');
    }

    public function tearDown(): void
    {
        verify_wordpress_testing_state();
    }

    public function test_safe_preg_replace(): void
    {
        // This works by passing an invalid regex to make preg_replace return null.
        // Prefixing @safe_preg_replace with @ is a hack to suppress the warning
        // that would otherwise make PHPUnit unhappy.
        $input = 'aaaaaaa';
        $actual = @safe_preg_replace('/[a/', 'b', $input);
        $this->assertEquals($input, $actual);
    }

    public function test_parsing(): void
    {
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
            new JewelleryGridEntry(
                'name of the range 1',
                'this is the alt text 1',
                '11',
                'linky/',
            ),
            new JewelleryGridEntry(
                'name of the range 2',
                'this is the alt text 2',
                '11',
                'linky/',
            ),
            new JewelleryGridEntry(
                'name of the range 3',
                'this is the alt text 3',
                '11',
                'linky/',
            ),
            new JewelleryGridEntry(
                'name of the range 4',
                'this is the alt text 4',
                '11',
                'linky/',
            ),
            new JewelleryGridEntry(
                'name of the range 5',
                'this is the alt text 5',
                '11',
                'linky/',
            ),
        );
        $actual = ParseJewelleryGridContents($input);
        $this->assertEquals($expected, $actual);
    }

    public function test_single_images(): void
    {
        add_image_info(11, 'grid_size', array('URL', 23, 59));
        $input = <<<'END_OF_INPUT'
# Format: range|alt|image_id|href|product_id
name of the range|this is the alt text|11|linky/|19

END_OF_INPUT;
        $output = JewelleryGridShortcode(
            array('description' => 'DESCRIPTION'),
            $input,
            ''
        );
        $expected = <<<'END_OF_EXPECTED'
        <div class="jewellery-grid">
          <div>
            <p class="grey large-text text-centered">DESCRIPTION</p>
          </div>
          <div class="flexboxrow jewellery-grid-inner">
            <div class="aligncenter jewellery-block">
              <div class="jewellery-picture-container">
                <a href="linky/">
                  <img src="URL" alt="this is the alt text"
                    width="23"
                    height="59"
                    class="aligncenter block" id="item-0-image"/>
                </a>
              </div>
              <div class="larger-text text-centered left-right-margin grey">
                <a href="linky/">name of the range</a>
              </div>
            </div>
          </div>
        </div>

END_OF_EXPECTED;
        $output = $this->add_numbers($output);
        $expected = $this->add_numbers($expected);
        $this->assertEquals($expected, $output);
    }

    public function test_multiple_images_and_pieces(): void
    {
        expect_add_action('wp_footer', 'SliderSetupGeneric');
        // First range.
        add_image_info(11, 'grid_size', array('URL', 23, 59));
        // Second range.
        add_image_info(13, 'grid_size', array('URLX', 23, 59));
        add_image_info(17, 'grid_size', array('URLY', 29, 61));
        add_image_info(23, 'grid_size', array('URLZ', 31, 67));
        $input = <<<'END_OF_INPUT'
# Format: range|alt|image_id|href|product_id
name of the range|this is the alt text|11|linky/|19
range range range|alt text for second range|13,17,23|linky/|53

END_OF_INPUT;
        $output = JewelleryGridShortcode(
            array('description' => 'DESCRIPTION'),
            $input,
            ''
        );
        $expected = <<<'END_OF_EXPECTED'
        <div class="jewellery-grid">
          <div>
            <p class="grey large-text text-centered">DESCRIPTION</p>
          </div>
          <div class="flexboxrow jewellery-grid-inner">
            <div class="aligncenter jewellery-block">
              <div class="jewellery-picture-container">
                <a href="linky/">
                  <img src="URL" alt="this is the alt text"
                    width="23"
                    height="59"
                    class="aligncenter block" id="item-0-image"/>
                </a>
              </div>
              <div class="larger-text text-centered left-right-margin grey">
                <a href="linky/">name of the range</a>
              </div>
            </div>
            <div class="aligncenter jewellery-block">
              <div class="jewellery-picture-container">
                <a href="linky/">
                  <img src="URLX" alt="alt text for second range"
                    width="23"
                    height="59"
                    class="aligncenter block" id="item-1-image"/>
                </a>
              </div>
              <div class="larger-text text-centered left-right-margin grey">
                <a href="linky/">range range range</a>
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
        $this->assertEquals($expected_slider, get_slider_images());
    }

    public function add_numbers(string $content): string
    {
        $lines = explode("\n", $content);
        $new_lines = array();
        foreach ($lines as $i => $line) {
            $new_lines[] = $i . ' ' . $line;
        }
        return implode("\n", $new_lines);
    }
}
