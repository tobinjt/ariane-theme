<?php

use PHPUnit\Framework\TestCase;

require_once('src/Functions.php');

class FunctionsTest extends TestCase
{
    public function test_fake_test(): void
    {
        $this->assertEquals('', '');
    }

    public function test_json_encode_wrapper(): void
    {
        $input = array(array('qwerty' => 7), array('foo' => 'bar', 'baz' => 'quux'));
        $this->assertEquals(
            '[{"qwerty":7},{"foo":"bar","baz":"quux"}]',
            json_encode_wrapper($input)
        );
        $input = array(array('NAN' => NAN, 'INF' => INF));
        $this->assertEquals('JSON_ENCODE FAILED!', json_encode_wrapper($input));
    }
}
