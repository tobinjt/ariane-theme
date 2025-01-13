<?php

declare(strict_types=1);

// Misc functions, mostly called from functions.php.

/* FrontPageSliderSetupShortcode: wrap FrontPageSliderSetup to provide a
 * shortcode.  This *must not* be used in the enclosing form.
 * Args (names are ugly but Wordpress-standard):
 *  $atts: an associative array of attributes, or an empty string if no
 *    attributes are given.
 *  $content: the enclosed content (if the shortcode is used in its enclosing
 *    form)
 *  $tag: the shortcode tag, useful for shared callback functions
 * Returns:
 *  string, the HTML to insert in the page (Wordpress does that
 *    automatically).
 */
/**
 * @param array<string, string> $atts
 */
function FrontPageSliderSetupShortcode(
    array $atts,
    string $content,
    string $tag
): string {
    $atts['unused'] = 'unused';
    unused($atts['unused']);
    unused($content);
    unused($tag);
    $images = SliderImages();
    return FrontPageSliderSetup($images);
}

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
