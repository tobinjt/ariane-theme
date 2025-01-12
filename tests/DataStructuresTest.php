<?php
use PHPUnit\Framework\TestCase;
require_once('src/DataStructures.php');
require_once('src/WPImageInfo.php');

// Most of the DataStructures code is tested indirectly and that is intentional
// because if there isn't something using a piece of code it should be deleted.
// This file covers some code that exists to keep linters etc happy.
class DataStructuresTest extends TestCase {
  public function test_json_encode_wrapper(): void {
    $this->assertEquals('["foo"]', json_encode_wrapper(array('foo')));
    $input = array(7, array('foo' => 'bar', 'baz' => 'quux'));
    $this->assertEquals(
      '[7,{"foo":"bar","baz":"quux"}]', json_encode_wrapper($input));
    $input = array(NAN, INF);
    $this->assertEquals('JSON_ENCODE FAILED!', json_encode_wrapper($input));
  }
}
