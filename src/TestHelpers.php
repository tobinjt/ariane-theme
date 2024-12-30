<?php

declare(strict_types=1);

// Extras needed by PHPLint.
/*. require_module 'core'; .*/

function clear_server_variables(): void
{
    // Can't replace $_SERVER entirely because it breaks phpunit.
    unset($_SERVER['REQUEST_URI']);
    unset($_SERVER['SERVER_NAME']);
}

function set_url(string $url): void
{
    $_SERVER['REQUEST_URI'] = $url;
}

function set_hostname(string $hostname): void
{
    $_SERVER['SERVER_NAME'] = $hostname;
}
