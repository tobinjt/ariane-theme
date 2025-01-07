<?php

declare(strict_types=1);

// Support for showing an individual piece of Jewellery.

// Extras needed by PHPLint.
/*. require_module 'core'; .*/
/*. require_module 'fakecart66'; .*/
/*. require_module 'wordpress'; .*/
require_once __DIR__ . '/DataStructures.php';
require_once __DIR__ . '/StoreClosingTimes.php';

/* Represents a Jewellery Page. */
class JewelleryPage
{
    public string $name = '';
    public int $product_id = 0;
    public string $range = '';
    public string $type = '';
    public bool $archived = false;

/** @var array<int, WPImageInfo> */
    /*. array[int]WPImageInfo .*/ public array $images = [];
    public int $height_int = 0;
    public string $height_str = '';
    public int $width_int = 0;
    public string $width_str = '';

    public function __construct(
        string $name,
        int $product_id,
        string $range,
        string $type,
        string $image_ids,
        bool $archived
    ) {
        $this->name = $name;
        $this->product_id = $product_id;
        $this->range = $range;
        $this->type = $type;
        $this->archived = $archived;
        $this->images = [];

        // Change "necklace" to "necklaces".
        if (substr($this->type, -1) !== 's') {
            $this->type .= 's';
        }

        $ids = explode(',', $image_ids);
        foreach ($ids as $id_str) {
            $id_int = intval($id_str);
            if ($id_int === -1) {
                // Skip this, it's not a real image ID.  Mostly used in testing.
                continue;
            }
            $image = new WPImageInfo($id_int, 'product_size');
            $this->images[] = $image;
            if ($image->getWidthInt() > $this->width_int) {
                $this->width_int = $image->getWidthInt();
            }
            if ($image->getHeightInt() > $this->height_int) {
                $this->height_int = $image->getHeightInt();
            }
        }
        $this->width_str = strval($this->width_int);
        $this->height_str = strval($this->height_int);
    }

    /* Convert an array of WPImageInfo to an array compatible with slider.js. */
    /**
     * @return array<int, array{'src': string, 'width': int, 'height': int}>
     */
    public function imagesToData(): array
    {
        // This needs to stay compatible with slider.js.
        /*. array[int]mixed .*/ $data = [];
        foreach ($this->images as $image) {
            $data[] = $image->imageToData();
        }
        return $data;
    }
}

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
        add_change_image(
            '#individual-jewellery-image',
            json_encode_wrapper($jewellery_page->imagesToData())
        );
        add_action('wp_footer', 'ChangeImagesSetupGeneric');
        $html .= <<<'END_OF_HTML'

    <div>
      <ul>

END_OF_HTML;

        foreach ($jewellery_page->images as $i => $full_size_image) {
            $image = new WPImageInfo($full_size_image->getImageId(), 'thumbnail');
            $src = $image->getUrl();
            $name = $jewellery_page->name;
            $width = $image->getWidthStr();
            $height = $image->getHeightStr();
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
    $src = $jewellery_page->images[0]->getUrl();
    $width = $jewellery_page->images[0]->getWidthStr();
    $height = $jewellery_page->images[0]->getHeightStr();
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
