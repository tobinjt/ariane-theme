<?php
use PHPUnit\Framework\TestCase;
require_once('src/FakeWordpress.php');
require_once('src/Functions.php');
require_once('src/Header.php');
require_once('src/TestHelpers.php');
require_once('src/Urls.php');

class HeaderTest extends TestCase {
  public function setUp(): void {
    clear_fake_wordpress_state();
    clear_server_variables();
    clear_wordpress_testing_state();
  }

  public function test_get_google_analytics_code_dev(): void {
    $_SERVER['SERVER_NAME'] = 'dev.arianetobin.ie';
    $this->assertEquals('', get_google_analytics_code());
  }

  public function test_get_google_analytics_code_prod(): void {
    $_SERVER['SERVER_NAME'] = 'www.arianetobin.ie';
    $content = get_google_analytics_code();
    $this->assertMatchesRegularExpression('/G-5GXZQT5D22/', $content);
    $this->assertMatchesRegularExpression('/www.googletagmanager.com/',
      $content);
  }

  public function test_links_to_html(): void {
    $links = array(
      'link1' => 'asdf',
      'link2' => 'qwerty',
      'pinky' => 'the brain',
      'pinkyfinger' => 'two hands',
      'wibble' => 'make ME lowercase',
    );
    $output = links_to_html($links, 'pinky', 'highlight ME', 8);
    $expected = <<<'END_OF_EXPECTED'
        <a href="link1">asdf</a>
        <a href="link2">qwerty</a>
        <a href="pinky" class="highlight ME">the brain</a>
        <a href="pinkyfinger">two hands</a>
        <a href="wibble">make me lowercase</a>
END_OF_EXPECTED;
    $this->assertEquals($expected, $output);
  }

  public function test_wrap_with_tag(): void {
    $output = wrap_with_tag('the tag', 'the class',
      '  leading spaces are stripped, trailing are not ', 4);
    $expected = implode("\n", array(
      '    <the tag class="the class">',
      '      leading spaces are stripped, trailing are not ',
      '    </the tag>',
    ));
    $this->assertEquals($expected, $output);
  }

  public function test_make_menu_bar(): void {
    $output = make_menu_bar(array('chunk 1', 'chunk 2'), 'css_tag');
    $expected = <<<'END_OF_EXPECTED'
    <div class="menubar css_tag">
      chunk 1
      chunk 2
    </div>

END_OF_EXPECTED;
    $this->assertEquals($expected, $output);
  }

  public function test_make_icon_link(): void {
    $output = make_icon_link('icon.png', 'alt text for icon', 7, 11);
    $expected = <<<'END_OF_EXPECTED'
<img class="greyscale" width="7" height="11"  src="DIR/images/icon.png" alt="alt text for icon" />
END_OF_EXPECTED;
    $this->assertEquals($expected, $output);
  }

  public function test_make_link_group(): void {
    set_url('url1/');
    $groups = array(
      'classy' => array('url1/' => 'txet 1knil', '5url' => 'wibble'),
      'more classy' => array('mega-url' => 'urly urly urly', 'foo' => 'bar'),
    );
    $output = make_link_group($groups, '5url');
    $expected = <<<'END_OF_EXPECTED'
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

  public function test_pick_url_to_highlight_404(): void {
    set_is_404(true);
    $output = pick_url_to_highlight(array(), 'zxcv');
    $this->assertEquals('/qwertyasdf', $output);
  }

  public function test_pick_url_to_highlight_last_match_wins(): void {
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

  public function test_pick_url_to_highlight_default_if_no_match(): void {
    set_url('/wibble/');
    $groups = array(
      'main' => array('/care/' => 'care'),
      'foo' => array('/qwerty/' => 'qwerty', '/bar/' => 'bar'),
    );
    $output = pick_url_to_highlight($groups, '/default_url/');
    $this->assertEquals('/default_url/', $output);
  }

  public function test_make_full_menu_bar_no_jewellery(): void {
    set_url('/care/');
    $output = make_full_menu_bar();
    $expected = <<<'END_OF_EXPECTED'
    <div class="menubar ">
      <span class="largest-text left-page-links">
        <a href="/">home</a>
        <a href="/jewellery/">jewellery</a>
        <a href="/care/" class="highlight">care</a>
        <a href="/about/">about</a>
      </span>
      <span class="float-right">
        <a href="https://www.facebook.com/ArianeTobinJewellery"><img class="greyscale" width="20" height="20"  src="dir/images/facebook.png" alt="facebook icon" /></a>
        <a href="https://www.instagram.com/arianetobin/"><img class="greyscale" width="20" height="20"  src="dir/images/instagram-icon.jpg" alt="instagram icon" /></a>
      </span>
    </div>

END_OF_EXPECTED;
    $this->assertEquals($expected, $output);
  }

  public function test_make_full_menu_bar_with_jewellery(): void {
    set_url('/jewellery/care/');
    $output = make_full_menu_bar();
    $expected = <<<'END_OF_EXPECTED'
    <div class="menubar ">
      <span class="largest-text left-page-links">
        <a href="/">home</a>
        <a href="/jewellery/" class="highlight">jewellery</a>
        <a href="/care/">care</a>
        <a href="/about/">about</a>
      </span>
      <span class="float-right">
        <a href="https://www.facebook.com/ArianeTobinJewellery"><img class="greyscale" width="20" height="20"  src="dir/images/facebook.png" alt="facebook icon" /></a>
        <a href="https://www.instagram.com/arianetobin/"><img class="greyscale" width="20" height="20"  src="dir/images/instagram-icon.jpg" alt="instagram icon" /></a>
      </span>
    </div>

    <div class="menubar larger-text">
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

    <div class="menubar larger-text">
      <span class="left-page-links">
        <a href="/jewellery/amble/">amble</a>
        <a href="/jewellery/botanical/">botanical</a>
        <a href="/jewellery/carapace/">carapace</a>
        <a href="/jewellery/cellule/">cellule</a>
        <a href="/jewellery/confluence/">confluence</a>
        <a href="/jewellery/dabble/">dabble</a>
        <a href="/jewellery/ellipse/">ellipse</a>
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

  public function test_get_title_404(): void {
    set_is_404(true);
    $this->assertEquals('Not Found - BLOG NAME', get_title());
  }

  public function test_get_title_not_a_page(): void {
    $this->assertEquals('BLOG NAME', get_title());
  }

  public function test_get_title_page_without_title(): void {
    set_is_page(true);
    $this->assertEquals('BLOG NAME - BLOG NAME', get_title());
  }

  public function test_get_title_page_with_title(): void {
    set_is_page(true);
    set_wp_title('PAGE TITLE');
    $this->assertEquals('PAGE TITLE - BLOG NAME', get_title());
  }

  public function test_get_title_post_with_title(): void {
    set_is_single(true);
    set_wp_title('PAGE TITLE');
    $this->assertEquals('PAGE TITLE - BLOG NAME', get_title());
  }
}
