<?php
use PHPUnit\Framework\TestCase;
require_once('src/Urls.php');

class UrlsTest extends TestCase {
  public function test_get_hostname() {
    $_SERVER['SERVER_NAME'] = 'foo';
    $this->assertEquals('foo', get_hostname());
  }
}
?>
