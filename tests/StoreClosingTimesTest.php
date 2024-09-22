<?php
use PHPUnit\Framework\TestCase;
require_once('src/StoreClosingTimes.php');

class StoreClosingTimesTest extends TestCase {
  public function setUp(): void {
    clear_all_times();
  }

  public function test_setting_and_getting_times(): void {
    global $TIMES;

    set_start_displaying_banner_message('pinky');
    $this->assertEquals('pinky', $TIMES[START_DISPLAYING_BANNER_MESSAGE]);
    $this->assertEquals('pinky', start_displaying_banner_message());
    set_stop_displaying_banner_message('the brain');
    $this->assertEquals('the brain', $TIMES[STOP_DISPLAYING_BANNER_MESSAGE]);
    $this->assertEquals('the brain', stop_displaying_banner_message());
  }

  public function test_timestring_to_human(): void {
    $this->assertEquals('Tuesday 25 December 2018',
      timestring_to_human('2018-12-25 18:30:00 Europe/Dublin'));
    $this->assertMatchesRegularExpression(
      '/SOMETHING WENT TERRIBLY WRONG CONVERTING/',
      timestring_to_human('this is not a valid date string'));
  }

  public function test_time_comparisons(): void {
    global $TIMES;
    $this->assertGreaterThan(12345, now());
    set_now_for_testing('2018-12-25 00:00:00 Europe/Dublin');
    $this->assertEquals('2018-12-25 00:00:00 Europe/Dublin',
      $TIMES[NOW_FOR_TESTING]);
    $this->assertEquals(1545696000, now());

    set_now_for_testing('2018-12-25 00:00:00 Europe/Dublin');
    $this->assertTrue(is_time_after('2018-12-23 00:00:00 Europe/Dublin'));
    $this->assertFalse(is_time_after('2018-12-27 00:00:00 Europe/Dublin'));
    $this->assertFalse(is_time_before('2018-12-23 00:00:00 Europe/Dublin'));
    $this->assertTrue(is_time_before('2018-12-27 00:00:00 Europe/Dublin'));
    $this->assertTrue(is_time_between('2018-12-23 00:00:00 Europe/Dublin',
      '2018-12-27 00:00:00 Europe/Dublin'));
    $this->assertFalse(is_time_between('2018-12-27 00:00:00 Europe/Dublin',
      '2018-12-23 00:00:00 Europe/Dublin'));
    $this->assertFalse(is_time_between('2017-12-23 00:00:00 Europe/Dublin',
      '2017-12-27 00:00:00 Europe/Dublin'));
  }
}
