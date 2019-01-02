<?php
use PHPUnit\Framework\TestCase;
require_once('src/FakeWordpress.php');
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
    $output = links_to_html($links, 'pinky', 'highlight ME');
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
      '  leading spaces are stripped, trailing are not ');
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
?>
