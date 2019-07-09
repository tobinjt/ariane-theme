<?php
use PHPUnit\Framework\TestCase;
require_once('src/StoreClosingTimes.php');

class StoreClosingTimesTest extends TestCase {
  public function setUp(): void {
    clear_all_times();
  }

  public function test_setting_and_getting_times() {
    global $TIMES;
    set_closing_time('asdf');
    $this->assertEquals('asdf', $TIMES[STORE_CLOSING_TIME]);
    $this->assertEquals('asdf', store_closing_time());
    set_opening_time('qwerty');
    $this->assertEquals('qwerty', $TIMES[STORE_OPENING_TIME]);
    $this->assertEquals('qwerty', store_opening_time());

    set_start_displaying_rds_message('pinky');
    $this->assertEquals('pinky', $TIMES[START_DISPLAYING_RDS_MESSAGE]);
    $this->assertEquals('pinky', start_displaying_rds_message());
    set_stop_displaying_rds_message('the brain');
    $this->assertEquals('the brain', $TIMES[STOP_DISPLAYING_RDS_MESSAGE]);
    $this->assertEquals('the brain', stop_displaying_rds_message());

    set_rds_start_time('Loki');
    $this->assertEquals('Loki', $TIMES[RDS_START_TIME]);
    $this->assertEquals('Loki', rds_start_time());
    set_rds_stop_time('Molly');
    $this->assertEquals('Molly', $TIMES[RDS_STOP_TIME]);
    $this->assertEquals('Molly', rds_stop_time());

    set_last_delivery_outside_ireland('wasd');
    $this->assertEquals('wasd', $TIMES[LAST_DELIVERY_OUTSIDE_IRELAND]);
    $this->assertEquals('wasd', last_day_for_delivery_outside_ireland());
    set_store_closing_message_display_date('zxcv');
    $this->assertEquals('zxcv', $TIMES[CLOSING_MESSAGE_DISPLAY_DATE]);
    $this->assertEquals('zxcv', store_closing_message_display_date());
  }

  public function test_timestring_to_human() {
    $this->assertEquals('Tuesday 25 December',
      timestring_to_human('2018-12-25 18:30:00 Europe/Dublin'));
    set_closing_time('2018-12-17 18:30:00 Europe/Dublin');
    set_opening_time('2019-01-07 00:30:00 Europe/Dublin');
    set_rds_start_time('2018-12-05 18:30:00 Europe/Dublin');
    set_rds_stop_time('2018-12-09 00:30:00 Europe/Dublin');
    set_last_delivery_outside_ireland('2018-12-11 18:30:00 Europe/Dublin');
    $this->assertEquals('Monday 17 December', store_closing_time_human());
    $this->assertEquals('Monday 07 January', store_opening_time_human());
    $this->assertEquals('Wednesday 05 December', rds_start_time_human());
    $this->assertEquals('Sunday 09 December', rds_stop_time_human());
    $this->assertEquals('Tuesday 11 December',
      last_day_for_delivery_outside_ireland_human());
  }

  public function test_time_comparisons() {
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

    set_closing_time('2018-12-23 00:00:00 Europe/Dublin');
    set_opening_time('2018-12-27 00:00:00 Europe/Dublin');
    set_now_for_testing('2018-12-25 00:00:00 Europe/Dublin');
    $this->assertTrue(is_store_closed());
    set_opening_time('2018-12-24 00:00:00 Europe/Dublin');
    $this->assertFalse(is_store_closed());
  }
}
