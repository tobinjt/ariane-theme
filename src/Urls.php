<?php
declare(strict_types=1);

// URL-related functions.

// Extras needed by PHPLint.
/*. require_module 'core'; .*/
/*. require_module 'wordpress'; .*/

function get_hostname(): string {
  return $_SERVER['SERVER_NAME'];
}

function is_dev_website(): bool {
  return get_hostname() === 'dev.arianetobin.ie';
}

/* get_current_url: returns the local portion of the URL, i.e. no hostname,
 * but it does include the query string.
 */
function get_current_url(): string {
  return $_SERVER['REQUEST_URI'];
}

// is_jewellery_page: is the current page a jewellery page?
function is_jewellery_page(): bool {
  return (strpos(get_current_url(), '/jewellery') === 0);
}

// is_current_url: is the current page === $url?  The query string is stripped.
function is_current_url(string $url): bool {
  $current_url = parse_url(get_current_url(), PHP_URL_PATH);
  return ($current_url === $url);
}

// Get the full path to an image within the theme.
function get_theme_image_path(string $file): string {
  return get_bloginfo('template_directory') . '/images/' . $file;
}
