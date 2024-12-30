<?php

declare(strict_types=1);

// Support for showing an individual piece of Jewellery.

// Extras needed by PHPLint.
/*. require_module 'core'; .*/
/*. require_module 'fakecart66'; .*/
/*. require_module 'wordpress'; .*/
require_once __DIR__ . '/DataStructures.php';
require_once __DIR__ . '/StoreClosingTimes.php';
/*. array[string][int][string]string .*/ $GLOBALS['CHANGE_IMAGES'] = [];

/* JewelleryPageShortcode: create a jewellery page.
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
function JewelleryPageShortcode(
    array $atts,
    string $content,
    string $tag
): string {
    $tag .= 'make the linter happy.';
    $attrs = shortcode_atts(
        [
            'archived' => 'false',
            'image_id' => '',
            'name' => '',
            'product_id' => '',
            'range' => '',
            'type' => '',
        ],
        $atts
    );
    foreach ($attrs as $key => $value) {
        if ($value === '') {
            return "<h1>jewellery_page: empty attribute: {$key} </h1>\n";
        }
    }

    $jewellery_page = new JewelleryPage(
        $attrs['name'],
        intval($attrs['product_id']),
        $attrs['range'],
        $attrs['type'],
        $attrs['image_id'],
        $attrs['archived'] !== 'false'
    );
    $attrs = ['do not use' => 'dollar_attrs'];

    // Wordpress puts <br /> at the start and end of the content.
    $content = strval(str_replace('<br />', '', $content));

    // Don't make the range part of the name for some ranges.
    $excluded_ranges = ['archive', 'singles'];
    if (in_array($jewellery_page->range, $excluded_ranges)) {
        $range_in_piece_name = '';
    } else {
        $range_in_piece_name = $jewellery_page->range . ' ';
    }

    $html = <<<'END_OF_HTML'
<div class="flexboxrow">
  <div class="individual-jewellery-div">

END_OF_HTML;
    if (count($jewellery_page->images) > 1) {
        $GLOBALS['CHANGE_IMAGES']['#individual-jewellery-image'] =
          $jewellery_page->imagesToData();
        add_action('wp_footer', 'ChangeImagesSetupGeneric');
        $html .= <<<'END_OF_HTML'

    <div>
      <ul>

END_OF_HTML;

        foreach ($jewellery_page->image_ids as $i => $image_id) {
            $image = new WPImageInfo($image_id, 'thumbnail');
            $src = $image->url;
            $name = $jewellery_page->name;
            $width = $image->width_str;
            $height = $image->height_str;
            $html .= <<<END_OF_HTML
        <li><img src="{$src}"
                 alt="{$range_in_piece_name}{$name}"
                 onclick="change_image({$i}, '#individual-jewellery-image')"
                 width="{$width}" height="{$height}" /> </li>

END_OF_HTML;
        }
        $html .= <<<'END_OF_HTML'
      </ul>
    </div>


END_OF_HTML;
    }

    $div_width = $jewellery_page->width_str;
    $div_height = $jewellery_page->height_str;
    $name = $jewellery_page->name;
    $src = $jewellery_page->images[0]->url;
    $width = $jewellery_page->images[0]->width_str;
    $height = $jewellery_page->images[0]->height_str;
    $html .= <<<END_OF_HTML
    <div width="{$div_width}" height="{$div_height}">
      <img id="individual-jewellery-image"
        class="block aligncenter"
        alt="{$range_in_piece_name}{$name}"
        src="{$src}"
        width="{$width}" height="{$height}" />
    </div>
  </div>
  <div class="individual-jewellery-description">
    <p class="highlight larger-text">{$range_in_piece_name}{$name}</p>
    <p>{$content}</p>

END_OF_HTML;

    $range = $jewellery_page->range;
    $type = $jewellery_page->type;
    $html .= <<<END_OF_HTML

    <p>See other items in this range: <a href="/jewellery/{$range}/">
      {$range}</a></p>
    <p>See other: <a href="/jewellery/{$type}/">{$type}</a></p>
  </div>
</div>

END_OF_HTML;
    // Shortcodes need to be expanded.
    return do_shortcode($html);
}
