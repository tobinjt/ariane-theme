<?php

use PHPUnit\Framework\TestCase;

require_once('src/FakeWordpress.php');
require_once('src/TestHelpers.php');
require_once('src/Urls.php');

class UrlsTest extends TestCase
{
    public function setUp(): void
    {
        clear_server_variables();
    }

    public function test_get_hostname(): void
    {
        set_hostname('foo');
        $this->assertEquals('foo', get_hostname());
    }

    public function test_is_dev_website(): void
    {
        set_hostname('foo');
        $this->assertFalse(is_dev_website());
        set_hostname('dev.arianetobin.ie');
        $this->assertTrue(is_dev_website());
    }

    public function test_get_current_url(): void
    {
        set_url('foo');
        $this->assertEquals('foo', get_current_url());
    }

    public function test_is_jewellery_page(): void
    {
        set_url('foo');
        $this->assertFalse(is_jewellery_page());
        set_url('/jewellery/');
        $this->assertTrue(is_jewellery_page());
        set_url('/jewellery/qwerty/');
        $this->assertTrue(is_jewellery_page());
    }

    public function test_is_current_url(): void
    {
        set_url('foo');
        $this->assertFalse(is_current_url('bar'));
        set_url('/archive/');
        $this->assertFalse(is_current_url('/archive/123/'));
        set_url('/jewellery/archive/');
        $this->assertTrue(is_current_url('/jewellery/archive/'));
        $this->assertFalse(is_current_url('/jewellery/archive'));
        set_url('/jewellery/archive');
        $this->assertFalse(is_current_url('/jewellery/archive/'));
    }

    public function test_get_theme_image_path(): void
    {
        $this->assertEquals(
            'DIR/images/asdf.png',
            get_theme_image_path('asdf.png')
        );
    }
}
