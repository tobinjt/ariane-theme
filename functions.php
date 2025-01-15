<?php

declare(strict_types=1);

require_once 'src/Functions.php';
require_once 'src/Header.php';
require_once 'src/JewelleryGrid.php';
require_once 'src/JewelleryPage.php';
require_once 'src/MultipleImageSupport.php';
require_once 'src/Urls.php';
require_once 'src/WordpressConfiguration.php';
require_once('src/WPImageInfo.php');

// Send errors to browser on dev site for easier debugging.
if (is_dev_website()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

// Configure Wordpress.
// Remove unnecessary resources that Wordpress or plugins include in every
// page.

// keep-sorted start block=true
// Clean up the <head>
add_action('init',
    function(): void {
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
    });
add_action('wp_enqueue_scripts', 'MaybeRemoveCookieLawInfoFromHead');
add_action('wp_enqueue_scripts',
    function() {
        wp_dequeue_style('classic-theme-styles');
    },
    100);
// Remove Gutenberg editor CSS that isn't needed.
add_action('wp_enqueue_scripts',
    function() {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('global-styles');
    },
    100);
// Stop wp-embed being loaded.  I don't know why this has to be triggered in
// wp_footer.
add_action('wp_footer', function() {
    wp_deregister_script('wp-embed');
});
add_filter('emoji_svg_url', '__return_false');
// Disable comment feeds on blog posts.  __return_false is a Wordpress
// function that returns false to make filters easier.
add_filter('feed_links_show_comments_feed', '__return_false');
add_filter('script_loader_src', 'remove_script_version', 15, 1);
add_filter('style_loader_src', 'remove_script_version', 15, 1);
remove_action('template_redirect', 'rest_output_link_header');
// Stop loading emoji stuff.
remove_action('wp_head', 'print_emoji_detection_script', 7);
// Stop linking wp-json stuff.
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_print_styles', 'print_emoji_styles');
// keep-sorted end
// End removing unnecessary resources.

// Add shortcodes.
add_shortcode('jewellery_grid', 'JewelleryGridShortcode');
add_shortcode('jewellery_page', 'JewelleryPageShortcode');
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
add_shortcode('front_page_slider',
    function (
        array $atts,
        string $content,
        string $tag
    ): string {
        $atts['unused'] = 'unused';
        unused($atts['unused']);
        unused($content);
        unused($tag);
        return FrontPageSliderSetup(SliderImages());
    });

// Enable extra image sizes.
add_image_size('slider_large', 1024, 768);
add_image_size('slider_small', 512, 384);
add_image_size('product_size', 520, 520);
add_image_size('grid_size', 260, 260);
// 'thumbnail' size is automatically generated by Wordpress and is used in
// product pages.

// Don't compress images, the resulting quality is too poor.
add_filter('jpeg_quality', static function ($arg) {
    unused($arg);
    return 100;
});

// Use my style sheet.
add_editor_style('style.css');
// Add RSS links to <head> section.
// add_theme_support('automatic-feed-links');
