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

function MaybeRemoveCookieLawInfoFromHead(): void
{
    if (! ShouldRemoveCookieLawInfo()) {
        return;
    }
    // Remove the Javascript and CSS.  There will still be some Javascript
    // output directly in the page with the plugin settings, don't worry about
    // that.
    // To figure out the correct strings in future, add this at the end of
    // header.php:
    // <pre>
    // <?php
    //   global $wp_scripts;
    //   echo htmlspecialchars(print_r($wp_scripts, true));
    // ? >
    // </pre>
    // Then search the output for 'cookie-law-info' and look for "handle =
    // 'foo'", where 'foo' is the string you need.
    wp_dequeue_style('cookie-law-info');
    wp_deregister_style('cookie-law-info');
    wp_dequeue_style('cookie-law-info-gdpr');
    wp_deregister_style('cookie-law-info-gdpr');
    wp_dequeue_script('cookie-law-info');
    wp_deregister_script('cookie-law-info');
}

function removeHeadLinks(): void
{
    // Remove some links that are unnecessary.
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    // Remove automatically generated shortlink.
    remove_action('wp_head', 'wp_shortlink_wp_head');
    // Disable comment feeds on pages.
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'feed_links', 2);
    // Remove shortlink from HTTP headers, I only want the long version used,
    // and linkchecker complains about the redirects.
    remove_action('template_redirect', 'wp_shortlink_header', 11);
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
