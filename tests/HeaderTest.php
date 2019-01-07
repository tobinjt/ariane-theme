<?php
use PHPUnit\Framework\TestCase;
require_once('src/FakeWordpress.php');
require_once('src/StoreClosingTimes.php');
require_once('src/TestHelpers.php');
require_once('src/Header.php');

class GetGoogleAnalyticsCodeTest extends TestCase {
  public function test_dev() {
    $_SERVER['SERVER_NAME'] = 'dev.arianetobin.ie';
    $this->assertEquals('', get_google_analytics_code());
  }

  public function test_prod() {
    $_SERVER['SERVER_NAME'] = 'www.arianetobin.ie';
    $content = get_google_analytics_code();
    $this->assertRegExp('/UA-21043347-2/', $content);
    $this->assertRegExp('/analytics.js/', $content);
  }
}

class LinksToHTMLTest extends TestCase {
  public function test_all() {
    $links = array(
      'link1' => 'asdf',
      'link2' => 'qwerty',
      'pinky' => 'the brain',
      'pinkyfinger' => 'two hands',
      'wibble' => 'make ME lowercase',
    );
    $output = links_to_html($links, 'pinky', 'highlight ME', 8);
    $expected = <<<END_OF_EXPECTED
        <a href="link1">asdf</a>
        <a href="link2">qwerty</a>
        <a href="pinky" class="highlight ME">the brain</a>
        <a href="pinkyfinger">two hands</a>
        <a href="wibble">make me lowercase</a>
END_OF_EXPECTED;
    $this->assertEquals($expected, $output);
  }
}

class WrapWithTagTest extends TestCase {
  public function test_all() {
    $output = wrap_with_tag('the tag', 'the class',
      '  leading spaces are stripped, trailing are not ', 4);
    $expected = implode("\n", array(
      '    <the tag class="the class">',
      '      leading spaces are stripped, trailing are not ',
      '    </the tag>',
    ));
    $this->assertEquals($expected, $output);
  }
}

class MakeMenuBarTest extends TestCase {
  public function test_all() {
    $output = make_menu_bar(array('chunk 1', 'chunk 2'), 'css_tag');
    $expected = <<<END_OF_EXPECTED
    <div class="menubar css_tag">
      chunk 1
      chunk 2
    </div>

END_OF_EXPECTED;
    $this->assertEquals($expected, $output);
  }
}

class MakeIconLinkTest extends TestCase {
  public function test_all() {
    $output = make_icon_link('icon.png', 'alt text for icon', '7', '11');
    $expected = <<<END_OF_EXPECTED
<img class="greyscale" width="7" height="11" src="DIR/images/icon.png" alt="alt text for icon" />
END_OF_EXPECTED;
    $this->assertEquals($expected, $output);
  }
}

class MakeLinkGroupTest extends TestCase {
  public function test_simple() {
    set_url('url1/');
    $groups = array(
      'classy' => array('url1/' => 'txet 1knil', '5url' => 'wibble'),
      'more classy' => array('mega-url' => 'urly urly urly', 'foo' => 'bar'),
    );
    $output = make_link_group($groups, '5url');
    $expected = <<<END_OF_EXPECTED
      <span class="classy">
        <a href="url1/" class="highlight">txet 1knil</a>
        <a href="5url">wibble</a>
      </span>
      <span class="more classy">
        <a href="mega-url">urly urly urly</a>
        <a href="foo">bar</a>
      </span>
END_OF_EXPECTED;
    $this->assertEquals($expected, $output);
  }
}

class PickURLToHighlightTest extends TestCase {
  public function setUp() {
    clear_wordpress_testing_state();
  }

  public function test_404() {
    set_is_404(true);
    $output = pick_url_to_highlight(array(), 'zxcv');
    $this->assertEquals('/qwertyasdf', $output);
  }

  public function test_store_page() {
    set_url('/store/wibble');
    $groups = array(
      'main' => array('/care/' => 'care', '/store/basket' => 'basket'),
      'foo' => array('/qwerty/' => 'qwerty', '/bar/' => 'bar'),
    );
    $output = pick_url_to_highlight($groups, '/default_url/');
    $this->assertEquals('/store/basket', $output);
  }

  public function test_last_match_wins() {
    set_url('/jewellery/wave/ring');
    $groups = array(
      'main' => array('/jewellery' => 'jewellery', '/jewellery/wave' => 'wave'),
    );
    $output = pick_url_to_highlight($groups, '/default_url/');
    $this->assertEquals('/jewellery/wave', $output);

    set_url('/jewellery/wave/ring');
    $groups = array(
      'main' => array('/jewellery/wave' => 'wave', '/jewellery' => 'jewellery'),
    );
    $output = pick_url_to_highlight($groups, '/default_url/');
    $this->assertEquals('/jewellery', $output);
  }

  public function test_default_if_no_match() {
    set_url('/wibble/');
    $groups = array(
      'main' => array('/care/' => 'care', '/store/basket' => 'basket'),
      'foo' => array('/qwerty/' => 'qwerty', '/bar/' => 'bar'),
    );
    $output = pick_url_to_highlight($groups, '/default_url/');
    $this->assertEquals('/default_url/', $output);
  }

}

class MakeFullMenuBarTest extends TestCase {
  public function test_no_jewellery() {
    set_url('/care/');
    $output = make_full_menu_bar();
    $expected = <<<END_OF_EXPECTED
    <div class="menubar ">
      <span class="largest-text left-page-links">
        <a href="/">home</a>
        <a href="/jewellery/">jewellery</a>
        <a href="/care/" class="highlight">care</a>
        <a href="/about/">about</a>
        <a href="/store/cart/">basket</a>
      </span>
      <span class="float-right">
        <a href="https://www.facebook.com/ArianeTobinJewellery"><img class="greyscale" width="20" height="20" src="dir/images/facebook.png" alt="facebook icon" /></a>
        <a href="https://twitter.com/#!/ArianeTobin"><img class="greyscale" width="20" height="20" src="dir/images/twitter.png" alt="twitter icon" /></a>
        <a href="https://pinterest.com/arianetobin/"><img class="greyscale" width="20" height="20" src="dir/images/pinterest.png" alt="pinterest icon" /></a>
      </span>
    </div>

END_OF_EXPECTED;
    $this->assertEquals($expected, $output);
  }

  public function test_with_jewellery() {
    set_url('/jewellery/care/');
    $output = make_full_menu_bar();
    $expected = <<<END_OF_EXPECTED
    <div class="menubar ">
      <span class="largest-text left-page-links">
        <a href="/">home</a>
        <a href="/jewellery/" class="highlight">jewellery</a>
        <a href="/care/">care</a>
        <a href="/about/">about</a>
        <a href="/store/cart/">basket</a>
      </span>
      <span class="float-right">
        <a href="https://www.facebook.com/ArianeTobinJewellery"><img class="greyscale" width="20" height="20" src="dir/images/facebook.png" alt="facebook icon" /></a>
        <a href="https://twitter.com/#!/ArianeTobin"><img class="greyscale" width="20" height="20" src="dir/images/twitter.png" alt="twitter icon" /></a>
        <a href="https://pinterest.com/arianetobin/"><img class="greyscale" width="20" height="20" src="dir/images/pinterest.png" alt="pinterest icon" /></a>
      </span>
    </div>

    <div class="menubar larger-text bottom-margin">
      <span class="left-page-links">
        <a href="/jewellery/bangles/">bangles</a>
        <a href="/jewellery/earrings/">earrings</a>
        <a href="/jewellery/necklaces/">necklaces</a>
        <a href="/jewellery/rings/">rings</a>
      </span>
      <span class="float-right grey">
        Free delivery on all orders to Ireland
      </span>
    </div>

    <div class="menubar larger-text bottom-margin">
      <span class="left-page-links">
        <a href="/jewellery/amble/">amble</a>
        <a href="/jewellery/botanical/">botanical</a>
        <a href="/jewellery/carapace/">carapace</a>
        <a href="/jewellery/cellule/">cellule</a>
        <a href="/jewellery/confluence/">confluence</a>
        <a href="/jewellery/dabble/">dabble</a>
        <a href="/jewellery/halo/">halo</a>
        <a href="/jewellery/laria/">laria</a>
        <a href="/jewellery/pod/">pod</a>
        <a href="/jewellery/sentinel/">sentinel</a>
        <a href="/jewellery/wave/">wave</a>
        <a href="/jewellery/archive/">archive</a>
      </span>
    </div>

END_OF_EXPECTED;
    $this->assertEquals($expected, $output);
  }
}

class GetTitleTest extends TestCase {
  public function setUp() {
    clear_wordpress_testing_state();
  }

  public function test_404() {
    set_is_404(true);
    $this->assertEquals('Not Found - BLOG NAME', get_title());
  }

  public function test_not_a_page() {
    $this->assertEquals('BLOG NAME', get_title());
  }

  public function test_page_without_title() {
    set_is_page(true);
    $this->assertEquals('BLOG NAME - BLOG NAME', get_title());
  }

  public function test_page_with_title() {
    set_is_page(true);
    set_wp_title('PAGE TITLE');
    $this->assertEquals('PAGE TITLE - BLOG NAME', get_title());
  }

  public function test_post_with_title() {
    set_is_single(true);
    set_wp_title('PAGE TITLE');
    $this->assertEquals('PAGE TITLE - BLOG NAME', get_title());
  }
}

class GetRDSMessageTest extends TestCase {
  public function setUp() {
    clear_all_times();
  }

  public function test_get_rds_message() {
    set_start_displaying_rds_message('2018-10-23 00:00:00 Europe/Dublin');
    set_stop_displaying_rds_message('2018-12-27 00:00:00 Europe/Dublin');
    set_rds_start_time('2018-12-23 00:00:00 Europe/Dublin');
    set_rds_stop_time(stop_displaying_rds_message());
    set_now_for_testing('2018-12-29 00:00:00 Europe/Dublin');
    set_rds_stand('B15 on the Balcony');
    set_rds_link('http://www.giftedfair.ie/');
    set_rds_name('Gifted - The Contemporary Craft &amp; Design Fair');

    $this->assertEquals('', get_rds_message());
    set_now_for_testing('2018-12-25 00:00:00 Europe/Dublin');
    $message = get_rds_message();
    $regexes = array(
      'Ariane will be at',
      rds_start_time_human() ,
      rds_stand(),
      rds_name(),
    );
    foreach($regexes as $regex) {
      $this->assertRegExp('/' . $regex . '/', $message);
    }
  }
}

class GetJewelleryPageMessageTest extends TestCase {
  public function setUp() {
    clear_all_times();
  }

  public function test_store_closed() {
    set_closing_time('2018-12-17 18:30:00 Europe/Dublin');
    set_opening_time('2019-01-07 00:30:00 Europe/Dublin');
    set_now_for_testing('2018-12-25 00:00:00 Europe/Dublin');
    $expected = <<<END_OF_EXPECTED
      <p class="text-centered larger-text grey">
        The store is now closed, and Ariane will return to the workshop
        Monday 07 January.
      </p>
END_OF_EXPECTED;
    $this->assertEquals($expected, get_jewellery_page_message());
  }

  public function test_no_message() {
    set_now_for_testing('2018-12-15 00:00:00 Europe/Dublin');
    set_store_closing_message_display_date('2018-12-22 18:30:00 Europe/Dublin');
    set_closing_time('2018-12-27 18:30:00 Europe/Dublin');
    set_opening_time('2019-01-07 00:30:00 Europe/Dublin');
    $this->assertEquals('', get_jewellery_page_message());
  }

  public function test_last_delivery_warning() {
    set_store_closing_message_display_date('2018-12-18 18:30:00 Europe/Dublin');
    set_now_for_testing('2018-12-22 00:00:00 Europe/Dublin');
    set_last_delivery_outside_ireland('2018-12-23 00:00:00 Europe/Dublin');
    set_closing_time('2018-12-27 18:30:00 Europe/Dublin');
    set_opening_time('2019-01-07 00:30:00 Europe/Dublin');
    $expected = <<<END_OF_EXPECTED
    <p class="text-centered larger-text grey">
      Delivery outside Ireland before December 25th cannot be guaranteed for
      orders placed after Sunday 23 December.
      The store will be closing on Thursday 27 December.
      Ariane will return to the workshop on Monday 07 January.
    </p>
END_OF_EXPECTED;
    $this->assertEquals($expected, get_jewellery_page_message());
  }
}

class GetStorePageMessageTest extends TestCase {
  public function setUp() {
    clear_server_variables();
  }

  public function test_cart() {
    set_url('/store/cart/');
    $this->assertRegExp('/press the <em>Checkout<\/em> button/',
      get_store_page_message());
  }

  public function test_checkout() {
    set_url('/store/checkout/');
    $this->assertRegExp('/press the <em>PayPal<\/em> button/',
      get_store_page_message());
  }

  public function test_express() {
    set_url('/store/express/');
    $this->assertRegExp('/press the <em>Complete Order<\/em>/',
      get_store_page_message());
  }

  public function test_full_message() {
    set_url('/store/express/');
    $expected = <<<END_OF_EXPECTED
    <div class="largest-text highlight bold top-bottom-margin">
      To complete your order you <em>must</em> press the <em>Complete Order</em>
      button at the bottom left of the page.
    </div>
    <div id="store_message">
      <ul class="grey">
        <li>Each piece of jewellery is handmade by Ariane in her studio in
            Carlow, as a result there is normally a two week lead time on all
            orders.</li>
        <li>Free registered shipping to Ireland, EU, and USA on all orders over
            €50.</li>
        <li>Free unregistered shipping to Ireland on all orders under €50.</li>
        <li>All taxes and duties are the responsibility of the buyer.</li>
      </ul>
    </div>
END_OF_EXPECTED;
    $this->assertEquals($expected, get_store_page_message());
  }
}
?>
