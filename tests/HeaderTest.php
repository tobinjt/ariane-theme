<?php
use PHPUnit\Framework\TestCase;
require_once('src/FakeWordpress.php');
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
?>
