<?php
use PHPUnit\Framework\TestCase;
require_once('src/Urls.php');

class UrlsTest extends TestCase {
  public function set_hostname(string $hostname) {
    $_SERVER['SERVER_NAME'] = $hostname;
  }

  public function set_url(string $url) {
    $_SERVER['REQUEST_URI'] = $url;
  }

  public function test_get_hostname() {
    $this->set_hostname('foo');
    $this->assertEquals('foo', get_hostname());
  }

  public function test_is_dev_website() {
    $this->set_hostname('foo');
    $this->assertFalse(is_dev_website());
    $this->set_hostname('dev.arianetobin.ie');
    $this->assertTrue(is_dev_website());
  }

  public function test_get_current_url() {
    $this->set_url('foo');
    $this->assertEquals('foo', get_current_url());
  }

  public function test_is_jewellery_page() {
    $this->set_url('foo');
    $this->assertFalse(is_jewellery_page());
    $this->set_url('/jewellery/');
    $this->assertTrue(is_jewellery_page());
    $this->set_url('/jewellery/qwerty/');
    $this->assertTrue(is_jewellery_page());
  }

  public function test_is_store_page() {
    $this->set_url('foo');
    $this->assertFalse(is_store_page());
    $this->set_url('/store/');
    $this->assertTrue(is_store_page());
    $this->set_url('/store/qwerty/');
    $this->assertTrue(is_store_page());
  }

  public function test_is_archive_page() {
    $this->set_url('foo');
    $this->assertFalse(is_archive_page());
    $this->set_url('/archive/');
    $this->assertFalse(is_archive_page());
    $this->set_url('/jewellery/archive/');
    $this->assertTrue(is_archive_page());
    $this->set_url('/jewellery/archive/qwerty/');
    $this->assertTrue(is_archive_page());
  }

  public function test_is_current_url() {
    $this->set_url('foo');
    $this->assertFalse(is_current_url('bar'));
    $this->set_url('/archive/');
    $this->assertFalse(is_current_url('/archive/123/'));
    $this->set_url('/jewellery/archive/');
    $this->assertTrue(is_current_url('/jewellery/archive/'));
    $this->assertFalse(is_current_url('/jewellery/archive'));
    $this->set_url('/jewellery/archive');
    $this->assertFalse(is_current_url('/jewellery/archive/'));
  }
}
?>
