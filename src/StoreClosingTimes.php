<?php
/* Functions related to the store closing and opening.
 * Requires a function "now(): int {" that returns the current timestamp; it's
 * not defined in this file so that it can be easily overridden for testing.
 * TODO: is there a better way to do this?
 */

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

/* store_closing_time_human: human readable time for the store to close.
 * Must be manually kept in sync with store_closing_time().
 */
function store_closing_time_human(): string {
  return 'Monday 17th December';
}

/* store_closing_time: when the store closes next. */
function store_closing_time(): string {
  return '2018-12-17 18:30:00 Europe/Dublin';
}

/* store_opening_time_human: human readable time for the store to open.
 * Must be manually kept in sync with store_opening_time().
 */
function store_opening_time_human(): string {
  return 'Monday 7th January';
}

/* store_opening_time: when the store opens next. */
function store_opening_time(): string {
  return '2019-01-07 00:30:00 Europe/Dublin';
}

/* last_day_for_delivery_outside_ireland_human.
 * Must be manually kept in sync with last_day_for_delivery_outside_ireland().
 */
function last_day_for_delivery_outside_ireland_human(): string {
  return 'Wednesday 11th December';
}

function last_day_for_delivery_outside_ireland(): string {
  return '2018-12-11 18:30:00 Europe/Dublin';
}

/* last_day_for_delivery_outside_ireland */
function show_store_closing_message_after_this_date(): string {
  return '2018-12-01 01:30:00 Europe/Dublin';
}

/* is_store_closed: is the store currently closed?  Uses store_closing_time()
 * and store_opening_time().
 */
function is_store_closed(): bool {
  return is_time_between(store_closing_time(), store_opening_time());
}
?>
