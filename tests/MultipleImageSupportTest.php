<?php
use PHPUnit\Framework\TestCase;
require_once('src/MultipleImageSupport.php');
require_once('src/FakeWordpress.php');

class SliderImagesTest extends TestCase {
  public function setUp() {
    clear_wordpress_testing_state();
  }

  public function test_no_images() {
    $non_slider_post = new WP_Post(3, 'qwerty');
    WP_Query::add_query_result($non_slider_post);
    $images = SliderImages();
    $this->assertEmpty($images);
  }

  public function test_two_images() {
    $p1 = new WP_Post(5, ' slider URL_FOR_PAGE ');
    $p2 = new WP_Post(7, 'slider URL_FOR_PAGE2');
    WP_Query::add_query_result($p1);
    WP_Query::add_query_result($p2);
    add_image_info(5, 'slider_large', array('URL_FOR_5_LARGE', 50, 70));
    add_image_info(5, 'slider_small', array('URL_FOR_5_SMALL', 25, 105));
    add_image_info(7, 'slider_large', array('URL_FOR_7_LARGE', 70, 90));
    add_image_info(7, 'slider_small', array('URL_FOR_7_SMALL', 10, 107));
    $images = SliderImages();
    $expected = array(
      array(
        'src' => 'URL_FOR_5_LARGE',
        'href' => 'URL_FOR_PAGE',
        'srcset' => 'URL_FOR_5_LARGE 50w, URL_FOR_5_SMALL 25w',
        'sizes' => '(max-width: 799px) 25px, 50px',
      ),
      array(
        'src' => 'URL_FOR_7_LARGE',
        'href' => 'URL_FOR_PAGE2',
        'srcset' => 'URL_FOR_7_LARGE 70w, URL_FOR_7_SMALL 10w',
        'sizes' => '(max-width: 799px) 10px, 70px',
      ),
    );
    $this->assertEquals($expected[0]['srcset'], $images[0]['srcset']);
    $this->assertEquals($expected, $images);
  }

}

class SmallFunctionsTest extends TestCase {
  public function setUp() {
    clear_wordpress_testing_state();
  }

  public function test_ChangeImagesSetupGeneric() {
    global $CHANGE_IMAGES;
    $CHANGE_IMAGES = array();
    $CHANGE_IMAGES['foo'] = array(1, 2);
    $CHANGE_IMAGES['bar'] = array('asdf', 'qwerty');
    $expected = <<<END_OUT_OUTPUT
<!-- Start of ChangeImages. -->
<script type="text/javascript">
function change_image(i, id) {
var images = {"foo":[1,2],"bar":["asdf","qwerty"]};
// Construct a new image and swap it in, otherwise it flashes awkwardly - the
// old image resizes and then the new image is displayed.
var img = jQuery(id);
var new_img = jQuery('<img>');
new_img.attr('id', img.attr('id'));
new_img.attr('alt', img.attr('alt'));
new_img.attr(images[id][i]);
img.replaceWith(new_img);
};
</script>
<!-- End of ChangeImages. -->

END_OUT_OUTPUT;
    $this->expectOutputString($expected);
    ChangeImagesSetupGeneric();
  }

  public function test_SliderSetupGeneric() {
    global $SLIDER_IMAGES;
    $SLIDER_IMAGES = array();
    $SLIDER_IMAGES['#foo'] = json_encode(array(11, 23));
    $SLIDER_IMAGES['#bar'] = json_encode(array('pinky', 'brain'));
    $_SERVER['SERVER_NAME'] = 'dev.arianetobin.ie';
    $expected = <<<END_OUT_OUTPUT
<!-- Start of SliderSetup. -->
<script type="text/javascript">
jQuery(document).ready(function() {
  Slider.initialise({'id_prefix': '#foo',
                     'log_to_console': true},
                    [11,23]);
  Slider.initialise({'id_prefix': '#bar',
                     'log_to_console': true},
                    ["pinky","brain"]);
});
</script>
<!-- Include the rest of the Javascript. -->
<script type="text/javascript" src="DIR/slider.js"></script>
<!-- End of SliderSetup. -->

END_OUT_OUTPUT;
    $this->expectOutputString($expected);
    SliderSetupGeneric();
  }
}
?>
