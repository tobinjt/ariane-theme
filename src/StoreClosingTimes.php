<?php
declare(strict_types=1);

// Functions related to the store closing and opening.

// Extras needed by PHPLint.
/*. require_module 'core'; .*/
/*. string[int] .*/ $TIMES = array();

global $TIMES;
$TIMES = array();
define('STORE_CLOSING_TIME', 0);
define('STORE_OPENING_TIME', 1);
define('LAST_DELIVERY_OUTSIDE_IRELAND', 2);
define('CLOSING_MESSAGE_DISPLAY_DATE', 3);
define('START_DISPLAYING_BANNER_MESSAGE', 4);
define('STOP_DISPLAYING_BANNER_MESSAGE', 5);
define('NOW_FOR_TESTING', 100);

/* Set the closing time of the store.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_closing_time(string $timestring): void {
  global $TIMES;
  $TIMES[STORE_CLOSING_TIME] = $timestring;
}

/* Set the opening time of the store.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_opening_time(string $timestring): void {
  global $TIMES;
  $TIMES[STORE_OPENING_TIME] = $timestring;
}

/* Set the last delivery time for orders outside Ireland.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_last_delivery_outside_ireland(string $timestring): void {
  global $TIMES;
  $TIMES[LAST_DELIVERY_OUTSIDE_IRELAND] = $timestring;
}

/* Set the time to start displaying the store closing message.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_store_closing_message_display_date(string $timestring): void {
  global $TIMES;
  $TIMES[CLOSING_MESSAGE_DISPLAY_DATE] = $timestring;
}

/* Set the time to start displaying the BANNER message.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_start_displaying_banner_message(string $timestring): void {
  global $TIMES;
  $TIMES[START_DISPLAYING_BANNER_MESSAGE] = $timestring;
}

/* Set the time to stop displaying the BANNER message.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_stop_displaying_banner_message(string $timestring): void {
  global $TIMES;
  $TIMES[STOP_DISPLAYING_BANNER_MESSAGE] = $timestring;
}

/* Set the time returned by now() for testing purposes.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_now_for_testing(string $timestring): void {
  global $TIMES;
  $TIMES[NOW_FOR_TESTING] = $timestring;
}

/* timestring_to_human: converts a timestring parsable by strtotime() to a human
 * readable string for display on the website.
 */
function timestring_to_human(string $timestring): string {
  return strftime('%A %d %B', strtotime($timestring));
}

// store_closing_time: when the store closes next.
function store_closing_time(): string {
  global $TIMES;
  return $TIMES[STORE_CLOSING_TIME];
}

function store_closing_time_human(): string {
  return timestring_to_human(store_closing_time());
}

// store_opening_time: when the store opens next.
function store_opening_time(): string {
  global $TIMES;
  return $TIMES[STORE_OPENING_TIME];
}

function store_opening_time_human(): string {
  return timestring_to_human(store_opening_time());
}

// start_displaying_banner_message: when to start displaying the BANNER message.
function start_displaying_banner_message(): string {
  global $TIMES;
  return $TIMES[START_DISPLAYING_BANNER_MESSAGE];
}

// stop_displaying_banner_message: when to stop displaying the BANNER message.
function stop_displaying_banner_message(): string {
  global $TIMES;
  return $TIMES[STOP_DISPLAYING_BANNER_MESSAGE];
}

/* last_day_for_delivery_outside_ireland: time after which deliveries outside
 * Ireland are not guaranteed.
 */
function last_day_for_delivery_outside_ireland(): string {
  global $TIMES;
  return $TIMES[LAST_DELIVERY_OUTSIDE_IRELAND];
}

function last_day_for_delivery_outside_ireland_human(): string {
  return timestring_to_human(last_day_for_delivery_outside_ireland());
}

/* store_closing_message_display_date: date to start displaying the store
 * closing message.
 */
function store_closing_message_display_date(): string {
  global $TIMES;
  return $TIMES[CLOSING_MESSAGE_DISPLAY_DATE];
}

/* now: returns current time or fake time for testing.
 * Returns:
 *  Integer.
 */
function now(): int {
  global $TIMES;
  if (isset($TIMES[NOW_FOR_TESTING])) {
    return strtotime($TIMES[NOW_FOR_TESTING]);
  }
  return time();
}

/* is_time_after: is the current time after the specified time and date?
 * Args:
 *  $time_string: a time and date string parsable by strtotime().
 * Returns:
 *  Boolean.
 */
function is_time_after(string $time_string): bool {
  return now() > strtotime($time_string);
}

/* is_time_before: is the current time before the specified time and date?
 * Args:
 *  $time_string: a time and date string parsable by strtotime().
 * Returns:
 *  Boolean.
 */
function is_time_before(string $time_string): bool {
  return now() < strtotime($time_string);
}

/* is_time_between: is the current time between the specified times and dates?
 * Args:
 *  $start_time_string: a time and date string parsable by strtotime().
 *  $end_time_string: a time and date string parsable by strtotime().
 * Returns:
 *  Boolean.
 */
function is_time_between(string $start_time_string,
                         string $end_time_string): bool {
  return (is_time_after($start_time_string)
          && is_time_before($end_time_string));
}

/* is_store_closed: is the store currently closed?  Uses store_closing_time()
 * and store_opening_time().
 */
function is_store_closed(): bool {
  return is_time_between(store_closing_time(), store_opening_time());
}

// clear_all_times: clear all the times for predictable tests.
function clear_all_times(): void {
  global $TIMES;
  $TIMES = array();
}
