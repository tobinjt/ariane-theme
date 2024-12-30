<?php

declare(strict_types=1);

// Functions needed by header.php.

// Extras needed by PHPLint.
/*. require_module 'core'; .*/
/*. require_module 'wordpress'; .*/
require_once __DIR__ . '/StoreClosingTimes.php';
require_once __DIR__ . '/Urls.php';
$BANNER_MESSAGE = '';

// get_title(): return the appropriate title.
function get_title(): string
{
    $result = '';
    if (is_404()) {
        $result = 'Not Found - ';
    } elseif (is_single() || is_page()) {
        // is_single() is true for blog posts.
        $title = wp_title('', false);
        if ($title !== '') {
            $result = $title . ' - ';
        } else {
            // No much else we can do here :(
            $result = get_bloginfo('name') . ' - ';
        }
    }

    return $result . get_bloginfo('name');
}

/* get_google_analytics_code: returns the Jvascript code for Google Analytics,
 * depending on the hostname.
 */
function get_google_analytics_code(): string
{
    if (is_dev_website()) {
        return '';
    }
    return <<<'END_OF_JAVASCRIPT'
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-5GXZQT5D22"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-5GXZQT5D22');
</script>
END_OF_JAVASCRIPT;
}

/** links_to_html: converts an array of links into HTML with a tags.
 * Args:
 *  $links array of links mapping url to text.
 *  $url_to_highlight: the url to highlight as the current URL.
 *  $highlight_class: the value of the class attribute of the highlighted URL.
 *  $indent: the number of spaces to indent with.
 * Returns:
 *  string.
 */
/**
 * @param array<string> $links
 */
function links_to_html(
    array $links,
    string $url_to_highlight,
    string $highlight_class,
    int $indent
): string {
    $spaces = str_repeat(' ', $indent);
    /*. array[int]string .*/ $output = [];
    foreach ($links as $url => $text) {
        if ($url === $url_to_highlight) {
            $extra_class = ' class="' . $highlight_class . '"';
        } else {
            $extra_class = '';
        }
        $text = strtolower($text);
        $output[] = <<<END_OF_LINK
{$spaces}<a href="{$url}"{$extra_class}>{$text}</a>
END_OF_LINK;
    }
    return implode("\n", $output);
}

/* wrap_with_tag: wrap a tag around some html.
 * Note that the indentation of the HTML will not be correct, particularly if
 * you wrap more than once.
 * Args:
 *  $tag: the tag to wrap around the HTML.
 *  $cls: the CSS class for the tag.
 *  $html: the HTML to wrap the tag around.
 *  $indent: the number of spaces to indent with.
 * Returns:
 *  string.
 */
function wrap_with_tag(
    string $tag,
    string $cls,
    string $html,
    int $indent
): string {
    $html = ltrim($html);
    $spaces = str_repeat(' ', $indent);
    return <<<END_OF_TAG
{$spaces}<{$tag} class="{$cls}">
{$spaces}  {$html}
{$spaces}</{$tag}>
END_OF_TAG;
}

/* Find the URL to highlight.
 * Args:
 *   $groups: an array(css-class -> array(url -> link-text)).
 *   $default_url: the URL to use if the current URL is not in $groups.
 *                 Useful to make the blog link be highlighted for blog posts.
 * Returns:
 *  string, URL to highlight.
 */
/**
 * @param array<string, array<string, string>> $groups
 */
function pick_url_to_highlight(array $groups, string $default_url): string
{
    if (is_404()) {
        // Don't highlight any link for error pages
        return '/qwertyasdf';
    }
    // Strip trailing slashes everywhere to make comparisons easier.
    $current_url = rtrim(get_current_url(), '/');
    $url_to_highlight = $default_url;
    foreach ($groups as $links) {
        foreach ($links as $url => $text) {
            $text .= 'make the linter happy.';
            $pattern = rtrim($url, '/');
            if ($pattern === $current_url) {
                return $url;
            }
            // This assumes that if the URLs overlap the most specific will be last.
            // We look for matches at the start of the string.
            // Using === rather than == is essential, otherwise the comparison fails.
            if ($pattern !== '' and strpos($current_url, $pattern) === 0) {
                $url_to_highlight = $url;
            }
        }
    }
    return $url_to_highlight;
}

/* make_link_group: returns a bar of links.
 * Args:
 *   $groups: an array(css-class -> array(url -> link-text)).
 *   $default_url: the URL to use if the current URL is not in $groups.
 *                 Useful to make the blog link be highlighted for blog posts.
 * Returns:
 *  string.
 */
/**
 * @param array<string, array<string, string>> $groups
 */
function make_link_group(array $groups, string $default_url): string
{
    $url_to_highlight = pick_url_to_highlight($groups, $default_url);
    /*. array[int]string .*/ $output = [];
    foreach ($groups as $cls => $links) {
        $html_links = links_to_html($links, $url_to_highlight, 'highlight', 8);
        $output[] = wrap_with_tag('span', $cls, $html_links, 6);
    }
    return implode("\n", $output);
}

/* make_menu_bar: returns a menu bar.
 * Args:
 *   $menu_chunks: an array of HTML strings.
 *   $css_tags: a string of CSS tags to be added to the containing div.
 *       'menubar' will always be present in the tags.
 * Returns:
 *  string.
 */
/**
 * @param array<string> $menu_chunks
 */
function make_menu_bar(array $menu_chunks, string $css_tags): string
{
    $html = wrap_with_tag(
        'div',
        'menubar ' . $css_tags,
        implode("\n      ", $menu_chunks),
        4
    );
    return $html . "\n";
}

function make_icon_link(
    string $file,
    string $alt,
    int $width,
    int $height
): string {
    return '<img class="greyscale"' .
      ' width="' . $width . '"' .
      ' height="' . $height . '"' .
      ' src="' . get_theme_image_path($file) . '"' .
      ' alt="' . $alt . '" />';
}

function make_full_menu_bar(): string
{
    $output = '';
    // This assumes that arrays are ordered, which appears to be true.
    $main_links = [
        '/' => 'home',
        '/jewellery/' => 'jewellery',
        '/care/' => 'care',
        '/about/' => 'about',
    ];
    $icon_links = [
        'https://www.facebook.com/ArianeTobinJewellery' => make_icon_link('facebook.png', 'Facebook icon', 20, 20),
        'https://www.instagram.com/arianetobin/' => make_icon_link('instagram-icon.jpg', 'Instagram icon', 20, 20),
    ];
    $output .= make_menu_bar(
        [
            make_link_group(
                ['largest-text left-page-links' => $main_links,
                    'float-right' => $icon_links,
                ],
                '/news/'
            ),
        ],
        ''
    );

    if (is_jewellery_page()) {
        $output .= "\n";
        $jewellery_types_links = [
            '/jewellery/bangles/' => 'bangles',
          // '/jewellery/brooches/'   => 'brooches',
            '/jewellery/earrings/' => 'earrings',
            '/jewellery/necklaces/' => 'necklaces',
            '/jewellery/rings/' => 'rings',
        ];
        $output .= make_menu_bar(
            [
                make_link_group(
                    ['left-page-links' => $jewellery_types_links],
                    '/jewellery/'
                ),
                ltrim(wrap_with_tag(
                    'span',
                    'float-right grey',
                    'Free delivery on all orders to Ireland',
                    6
                )),
            ],
            'larger-text'
        );
        $output .= "\n";

        $jewellery_ranges_links = [
            '/jewellery/amble/' => 'amble',
            '/jewellery/botanical/' => 'botanical',
            '/jewellery/carapace/' => 'carapace',
            '/jewellery/cellule/' => 'cellule',
            '/jewellery/confluence/' => 'confluence',
            '/jewellery/dabble/' => 'dabble',
            '/jewellery/ellipse/' => 'ellipse',
            '/jewellery/halo/' => 'halo',
            '/jewellery/laria/' => 'laria',
            '/jewellery/pod/' => 'pod',
            '/jewellery/sentinel/' => 'sentinel',
            '/jewellery/wave/' => 'wave',
          // '/jewellery/singles/'    => 'singles',
            '/jewellery/archive/' => 'archive',
        ];
        $output .= make_menu_bar(
            [
                make_link_group(
                    ['left-page-links' => $jewellery_ranges_links],
                    '/jewellery/'
                ),
            ],
            'larger-text'
        );
    }

    return $output;
}

/* get_banner_message: return the message to display about the banner, or an
 * empty string if it's not the right time of year.
 * Returns:
 *  string.
 */
function get_banner_message(): string
{
    if (is_time_between(
        start_displaying_banner_message(),
        stop_displaying_banner_message()
    )) {
        global $BANNER_MESSAGE;
        return <<<BANNER_MESSAGE
      <p class="text-centered larger-text grey">
        {$BANNER_MESSAGE}
      </p>

BANNER_MESSAGE;
    }
    return '';
}

function set_banner_message(string $banner_message): void
{
    global $BANNER_MESSAGE;
    $BANNER_MESSAGE = $banner_message;
}
