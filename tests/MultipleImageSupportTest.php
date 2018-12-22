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
?>
