<?php

declare(strict_types=1);

/**
 * @param array<mixed> $data */
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

function unused(string $arg): string
{
    if (strlen($arg) > 1) {
        return 'unused, long arg';
    }
    return 'unused, short arg';
}
