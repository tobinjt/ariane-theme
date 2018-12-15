<?php
/* Functions related to the store closing and opening.
 * Requires a function "now(): int {" that returns the current timestamp; it's
 * not defined in this file so that it can be easily overridden for testing.
 * TODO: is there a better way to do this?
 */

global $TIMES;
$TIMES = array();
define('STORE_CLOSING_TIME', 0);
define('STORE_OPENING_TIME', 1);
define('LAST_DELIVERY_OUTSIDE_IRELAND', 2);
define('CLOSING_MESSAGE_DISPLAY_DATE', 3);

/* Set the closing time of the store.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_closing_time(string $timestring) {
  global $TIMES;
  $TIMES[STORE_CLOSING_TIME] = $timestring;
}

/* Set the opening time of the store.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_opening_time(string $timestring) {
  global $TIMES;
  $TIMES[STORE_OPENING_TIME] = $timestring;
}

/* Set the last delivery time for orders outside Ireland.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_last_delivery_outside_ireland(string $timestring) {
  global $TIMES;
  $TIMES[LAST_DELIVERY_OUTSIDE_IRELAND] = $timestring;
}

/* Set the time to start displaying the store closing message.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_store_closing_message_display_date(string $timestring) {
  global $TIMES;
  $TIMES[CLOSING_MESSAGE_DISPLAY_DATE] = $timestring;
}

/* timestring_to_human: converts a timestring parsable by strtotime() to a human
 * readable string for display on the website.
 */
function timestring_to_human(string $timestring): string {
  return strftime('%A %d %B', strtotime($timestring));
}

/* store_closing_time: when the store closes next. */
function store_closing_time(): string {
  global $TIMES;
  return $TIMES[STORE_CLOSING_TIME];
}

function store_closing_time_human(): string {
  return timestring_to_human(store_closing_time());
}

/* store_opening_time: when the store opens next. */
function store_opening_time(): string {
  global $TIMES;
  return $TIMES[STORE_OPENING_TIME];
}

function store_opening_time_human(): string {
  return timestring_to_human(store_opening_time());
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
?>
