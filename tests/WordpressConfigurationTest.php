<?php

use PHPUnit\Framework\TestCase;

require_once('src/FakeWordpress.php');
require_once('src/Functions.php');
require_once('src/WordpressConfiguration.php');

class WordpressConfigurationTest extends TestCase
{
    public function test_remove_script_version(): void
    {
        $this->assertEquals('foo', remove_script_version('foo'));
        $this->assertEquals('bar', remove_script_version('bar?baz'));
        $this->assertEquals('bar', remove_script_version('bar?baz?quux'));
    }

    public function test_ShouldRemoveCookieLawInfo(): void
    {
        unset($_COOKIE['viewed_cookie_policy']);
        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertFalse(ShouldRemoveCookieLawInfo());
        $_SERVER['HTTP_USER_AGENT'] = 'Chrome';
        $this->assertFalse(ShouldRemoveCookieLawInfo());
        $_SERVER['HTTP_USER_AGENT'] = 'Chrome-Lighthouse';
        $this->assertTrue(ShouldRemoveCookieLawInfo());
        $_SERVER['HTTP_USER_AGENT'] = 'Lighthouse';
        $_COOKIE['viewed_cookie_policy'] = 'cookies!!!';
        $this->assertTrue(ShouldRemoveCookieLawInfo());
    }

    public function test_HideCookieLawInfoInFooter(): void
    {
        $content = HideCookieLawInfoInFooter();
        $this->assertMatchesRegularExpression('/display: none/', $content);
        $this->assertMatchesRegularExpression('/#cookie-law-info-bar/', $content);
    }

    public function test_MaybeRemoveCookieLawInfoFromHead_ShouldNotRemove(): void
    {
        clear_styles_state();
        unset($_COOKIE['viewed_cookie_policy']);
        unset($_SERVER['HTTP_USER_AGENT']);
        MaybeRemoveCookieLawInfoFromHead();
        $this->assertEmpty($GLOBALS['DEQUEUED_STYLES']);
    }

    public function test_MaybeRemoveCookieLawInfoFromHead_ShouldRemove(): void
    {
        clear_styles_state();
        $_COOKIE['viewed_cookie_policy'] = 'cookies!!!';
        unset($_SERVER['HTTP_USER_AGENT']);
        MaybeRemoveCookieLawInfoFromHead();
        // I'm not testing what was dequeued, because that would be a
        // change-detector test.  I'm testing that the function dequeues
        // *something*.
        $this->assertEquals(2, count($GLOBALS['DEQUEUED_STYLES']));
    }
}
