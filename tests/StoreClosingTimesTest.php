<?php
use PHPUnit\Framework\TestCase;
require_once('src/StoreClosingTimes.php');

class StoreClosingTimesTest extends TestCase {
  public function test_setting_and_getting_times() {
    global $TIMES;
    set_closing_time('asdf');
    $this->assertEquals('asdf', $TIMES[STORE_CLOSING_TIME]);
    $this->assertEquals('asdf', store_closing_time());
    set_opening_time('qwerty');
    $this->assertEquals('qwerty', $TIMES[STORE_OPENING_TIME]);
    $this->assertEquals('qwerty', store_opening_time());
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
    set_last_delivery_outside_ireland('2018-12-11 18:30:00 Europe/Dublin');
    $this->assertEquals('Monday 17 December', store_closing_time_human());
    $this->assertEquals('Monday 07 January', store_opening_time_human());
    $this->assertEquals('Tuesday 11 December',
      last_day_for_delivery_outside_ireland_human());
  }
}
?>
