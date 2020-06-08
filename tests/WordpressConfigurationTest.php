<?php
use PHPUnit\Framework\TestCase;
require_once('src/WordpressConfiguration.php');

class WordpressConfigurationTest extends TestCase {
  public function test_remove_script_version() {
    $this->assertEquals('foo', remove_script_version('foo'));
    $this->assertEquals('bar', remove_script_version('bar?baz'));
    $this->assertEquals('bar', remove_script_version('bar?baz?quux'));
  }

  public function test_ShouldRemoveCookieLawInfo() {
    $this->assertFalse(ShouldRemoveCookieLawInfo());
    $_SERVER['HTTP_USER_AGENT'] = 'Chrome';
    $this->assertFalse(ShouldRemoveCookieLawInfo());
    $_SERVER['HTTP_USER_AGENT'] = 'Chrome-Lighthouse';
    $this->assertTrue(ShouldRemoveCookieLawInfo());
    $_SERVER['HTTP_USER_AGENT'] = 'Lighthouse';
    $_COOKIE['viewed_cookie_policy'] = 'cookies!!!';
    $this->assertTrue(ShouldRemoveCookieLawInfo());
  }

  public function test_HideCookieLawInfoInFooter() {
    $content = HideCookieLawInfoInFooter();
    $this->assertMatchesRegularExpression('/display: none/', $content);
    $this->assertMatchesRegularExpression('/#cookie-law-info-bar/', $content);
  }
}
