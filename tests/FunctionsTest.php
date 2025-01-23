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
        $this->assertEquals('["foo"]', json_encode_wrapper(array('foo')));
        $input = array(7, array('foo' => 'bar', 'baz' => 'quux'));
        $this->assertEquals(
            '[7,{"foo":"bar","baz":"quux"}]',
            json_encode_wrapper($input)
        );
        $input = array(NAN, INF);
        $this->assertEquals('JSON_ENCODE FAILED!', json_encode_wrapper($input));
    }
}
