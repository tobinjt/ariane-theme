<?php
// URL-related functions.
function get_hostname(): string {
  return $_SERVER['SERVER_NAME'];
}

function is_dev_website(): bool {
  return get_hostname() == 'dev.arianetobin.ie';
}

/* get_current_url: returns the local portion of the URL, i.e. no hostname,
 * but it does include the query string.
 */
function get_current_url(): string {
  return $_SERVER['REQUEST_URI'];
}

/* is_jewellery_page: is the current page a jewellery page?  */
function is_jewellery_page(): bool {
  return (strpos(get_current_url(), '/jewellery') === 0);
}

/* is_store_page: is the current page a store page?  */
function is_store_page(): bool {
  return (strpos(get_current_url(), '/store') === 0);
}

/* is_archive_page: is the current page an archive page?  */
function is_archive_page(): bool {
  return (strpos(get_current_url(), '/jewellery/archive') === 0);
}

/* is_current_url: is the current page === $url?  The query string is stripped. */
function is_current_url(string $url): bool {
  $current_url = parse_url(get_current_url(), PHP_URL_PATH);
  return ($current_url === $url);
}

?>
