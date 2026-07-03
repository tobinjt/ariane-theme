<?php

declare(strict_types=1);

// Misc functions, mostly called from functions.php.

/**
 * @param array<int | string, array<string, int|float|string>> $data */
function json_encode_wrapper(array $data): string
{
    $result = json_encode($data);
    if (is_bool($result)) {
        // Return an empty string rather than false on failure; this should
        // never arise in real use, but PHPStan warns about it.
        return 'JSON_ENCODE FAILED!';
    }
    return $result;
}

function unused(string $arg): void
{
    if (strlen($arg) > 1) {
        $arg = maybe_append_char($arg, 'a');
    }
}

function maybe_append_char(string $str, string $char): string
{
    if (substr($str, -1) !== $char) {
        return $str . $char;
    }
    return $str;
}
