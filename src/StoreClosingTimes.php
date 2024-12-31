<?php

declare(strict_types=1);

// Functions related to the store closing and opening.

// Extras needed by PHPLint.
/*. require_module 'core'; .*/

class StoreClosingTimesState
{
    public string $start_displaying_banner_message = '';
    public string $stop_displaying_banner_message = '';
    public string $now_for_testing = '';
}

$GLOBALS['StoreClosingTimesState'] = new StoreClosingTimesState();

function get_store_closing_time_state(): StoreClosingTimesState
{
    return $GLOBALS['StoreClosingTimesState'];
}

/* Set the time to start displaying the BANNER message.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_start_displaying_banner_message(string $timestring): void
{
    get_store_closing_time_state()->start_displaying_banner_message
        = $timestring;
}

/* Set the time to stop displaying the BANNER message.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_stop_displaying_banner_message(string $timestring): void
{
    get_store_closing_time_state()->stop_displaying_banner_message
        = $timestring;
}

/* Set the time returned by now() for testing purposes.
 * Args:
 *  $timestring: a time and date string parsable by strtotime().
 */
function set_now_for_testing(string $timestring): void
{
    get_store_closing_time_state()->now_for_testing = $timestring;
}

/* timestring_to_human: converts a timestring parsable by strtotime() to a human
 * readable string for display on the website.
 */
function timestring_to_human(string $timestring): string
{
    $timestamp = strtotime($timestring);
    // Return an error message rather than false on failure; this should never
    // arise in real use, but PHPStan warns about it.
    if (is_bool($timestamp)) {
        return 'SOMETHING WENT TERRIBLY WRONG CONVERTING "' . $timestring . '"';
    }
    return date('l d F Y', $timestamp);
}

// start_displaying_banner_message: when to start displaying the BANNER message.
function start_displaying_banner_message(): string
{
    return get_store_closing_time_state()->start_displaying_banner_message;
}

// stop_displaying_banner_message: when to stop displaying the BANNER message.
function stop_displaying_banner_message(): string
{
    return get_store_closing_time_state()->stop_displaying_banner_message;
}

/* now: returns current time or fake time for testing.
 * Returns:
 *  Integer.
 */
function now(): int
{
    $time = strtotime(get_store_closing_time_state()->now_for_testing);
    if ($time) {
        return $time;
    }
    return time();
}

/* is_time_after: is the current time after the specified time and date?
 * Args:
 *  $time_string: a time and date string parsable by strtotime().
 * Returns:
 *  Boolean.
 */
function is_time_after(string $time_string): bool
{
    return now() > strtotime($time_string);
}

/* is_time_before: is the current time before the specified time and date?
 * Args:
 *  $time_string: a time and date string parsable by strtotime().
 * Returns:
 *  Boolean.
 */
function is_time_before(string $time_string): bool
{
    return now() < strtotime($time_string);
}

/* is_time_between: is the current time between the specified times and dates?
 * Args:
 *  $start_time_string: a time and date string parsable by strtotime().
 *  $end_time_string: a time and date string parsable by strtotime().
 * Returns:
 *  Boolean.
 */
function is_time_between(
    string $start_time_string,
    string $end_time_string
): bool {
    return is_time_after($start_time_string)
            && ($end_time_string === '' || is_time_before($end_time_string));
}

// clear_all_times: clear all the times for predictable tests.
function clear_all_times(): void
{
    $GLOBALS['StoreClosingTimesState'] = new StoreClosingTimesState();
}
