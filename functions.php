<?php
// Requires PHP 7.0 or greater.
declare(strict_types=1);

require_once('src/Header.php');
require_once('src/JewelleryGrid.php');
require_once('src/JewelleryPage.php');
require_once('src/MultipleImageSupport.php');
require_once('src/StoreClosingTimes.php');
require_once('src/Urls.php');
require_once('src/WordpressConfiguration.php');

// Send errors to browser on dev site for easier debugging.
if (is_dev_website()) {
  error_reporting(E_ALL);
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
}

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
function FrontPageSliderSetupShortcode(array $atts, string $content,
                                       string $tag): string {
  $images = SliderImages();
  return FrontPageSliderSetup($images);
}


// Configure Wordpress.
// Remove unnecessary resources that Wordpress or plugins include in every
// page.

// Disable comment feeds on blog posts.  __return_false is a Wordpress
// function that returns false to make filters easier.
add_filter('feed_links_show_comments_feed', '__return_false');

// Clean up the <head>
function removeHeadLinks() {
  // Remove some links that are unnecessary.
  remove_action('wp_head', 'rsd_link');
  remove_action('wp_head', 'wp_generator');
  remove_action('wp_head', 'wlwmanifest_link');
  // Remove automatically generated shortlink.
  remove_action('wp_head', 'wp_shortlink_wp_head');
  // Disable comment feeds on pages.
  remove_action('wp_head', 'feed_links_extra', 3);
  remove_action('wp_head', 'feed_links', 2);
}
add_action('init', 'removeHeadLinks');

add_filter('script_loader_src', 'remove_script_version', 15, 1);
add_filter('style_loader_src', 'remove_script_version', 15, 1);

// Stop jquery-migrate being loaded.  jQuery depends on it, so the jQuery deps
// need to be changed too.
function blockJqueryMigrate(WP_Scripts $scripts) {
  $data = $scripts->query('jquery');
  if (!$data) {
    return;
  }
  $data->deps = array_diff($data->deps, array('jquery-migrate'));
}
add_action('wp_default_scripts', 'blockJqueryMigrate');

// Stop wp-embed being loaded.  I don't know why this has to be triggered in
// wp_footer.
function blockWPEmbed() {
  wp_deregister_script('wp-embed');
}
add_action('wp_footer', 'blockWPEmbed');

// Stop loading emoji stuff.
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
add_filter('emoji_svg_url', '__return_false');

// Stop linking wp-json stuff.
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('template_redirect', 'rest_output_link_header');

function MaybeRemoveCookieLawInfoFromHead() {
  if (!ShouldRemoveCookieLawInfo()) {
    return;
  }
  // Remove the Javascript and CSS.  There will still be some Javascript output
  // directly in the page with the plugin settings, don't worry about that.
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
add_action('wp_enqueue_scripts', 'MaybeRemoveCookieLawInfoFromHead');

// Remove Gutenberg editor CSS that isn't needed.
function remove_wp_block_library_css(){
  wp_dequeue_style('wp-block-library');
  wp_dequeue_style('wp-block-library-theme');
}
add_action( 'wp_enqueue_scripts', 'remove_wp_block_library_css', 100 );

// Remove classic theme CSS that isn't needed.
function remove_classic_themes_css(){
  wp_dequeue_style('classic-theme-styles');
}
add_action( 'wp_enqueue_scripts', 'remove_classic_themes_css', 100 );

// End removing unnecessary resources.

// Add shortcodes.
add_shortcode('jewellery_grid', 'JewelleryGridShortcode');
add_shortcode('jewellery_page', 'JewelleryPageShortcode');
add_shortcode('front_page_slider', 'FrontPageSliderSetupShortcode');

// Enable extra image sizes.
add_image_size('slider_large', 1024, 768);
add_image_size('slider_small', 512, 384);
add_image_size('product_size', 520, 520);
add_image_size('grid_size', 260, 260);
// 'thumbnail' size is automatically generated by Wordpress and is used in
// product pages.

// Don't compress images, the resulting quality is too poor.
add_filter('jpeg_quality', function($arg) { return 100; });

// Use my style sheet.
add_editor_style('style.css');
// Add RSS links to <head> section.
// add_theme_support('automatic-feed-links');

set_start_displaying_banner_message('2021-10-10 00:30:00 Europe/Dublin');
set_stop_displaying_banner_message('');
$rds_banner = <<<RDS_BANNER
        Ariane will be at <a class="external-link"
        href="http://www.giftedfair.ie/">Gifted - The Contemporary Craft &amp;
        Design Fair</a> from XXX to XXX.
        Please visit us at stand XXX, we'd love to see you!
RDS_BANNER;
$non_rds_banner = <<<NON_RDS_BANNER
        Ariane will <i>not</i> be at Gifted in the RDS this year, she's taking a
        break.  Her work is still available to purchase from this website, and
        she can be contacted at ariane @ arianetobin.ie.
NON_RDS_BANNER;
$maternity_leave_banner = <<<MATERNITY_LEAVE_BANNER
        Ariane is on sabbatical and is not accepting commissions or selling from
        the website.  Thanks!
MATERNITY_LEAVE_BANNER;
$maternity_leave_banner3 = <<<MATERNITY_LEAVE_BANNER
        The store is now closed for Christmas.  I want to thank all of my
        customers for all of your support this year and I wish you and your
        families a Happy Christmas and hopefully a better 2022.
MATERNITY_LEAVE_BANNER;
set_banner_message($maternity_leave_banner);
