<?php

declare(strict_types=1);

// Support for showing an individual piece of Jewellery.

/* Represents a Jewellery Page. */
final class JewelleryPage
{
    private string $name = '';
    private string $range = '';
    private string $type = '';

    /**
     * @var array<int, WPImageInfo>
     */
    private array $images = [];
    private int $height_int = 0;
    private int $width_int = 0;

    public function __construct(
        string $name,
        string $range,
        string $type,
        string $image_ids,
    ) {
        $this->name = $name;
        $this->range = $range;
        $this->type = $type;
        $this->images = [];

        // Change "necklace" to "necklaces".
        if (substr($this->type, -1) !== 's') {
            $this->type .= 's';
        }

        $ids = explode(',', $image_ids);
        foreach ($ids as $id_str) {
            $id_int = intval($id_str);
            $image = new WPImageInfo($id_int, 'product_size');
            $this->images[] = $image;
            $this->width_int = max($this->width_int, $image->getWidthInt());
            $this->height_int = max($image->getHeightInt(), $this->height_int);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRange(): string
    {
        return $this->range;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<int, WPImageInfo>
     */
    public function getImages(): array
    {
        return $this->images;
    }

    public function getHeightStr(): string
    {
        return strval($this->height_int);
    }

    public function getWidthStr(): string
    {
        return strval($this->width_int);
    }

    /* Convert an array of WPImageInfo to an array compatible with slider.js. */
    /**
     * @return array<int, array{'src': string, 'width': int, 'height': int}>
     */
    public function imagesToData(): array
    {
        // This needs to stay compatible with slider.js.
        /*. array[int]mixed .*/
        $data = [];
        foreach ($this->images as $image) {
            $data[] = $image->imageToData();
        }
        return $data;
    }
}

/* AddImageNavigation: add the HTML for image navigation.
 * Args:
 *  $jewellery_page: the JewelleryPage object.
 *  $range_in_piece_name: the range name to prepend to the piece name.
 * Returns:
 *  string, the HTML to insert in the page.
 */
function MakeImageNavigation(
    JewelleryPage $jewellery_page,
    string $range_in_piece_name,
): string {
    add_change_image(
        '#individual-jewellery-image',
        json_encode_wrapper($jewellery_page->imagesToData())
    );
    add_action('wp_footer', 'ChangeImagesSetupGeneric');
    $html = <<<'END_OF_HTML'

    <div>
      <ul>

END_OF_HTML;

    foreach ($jewellery_page->getImages() as $i => $full_size_image) {
        $image = new WPImageInfo(
            $full_size_image->getImageId(),
            'thumbnail'
        );
        $src = $image->getUrl();
        $name = $jewellery_page->getName();
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
    return $html;
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
    unused($tag);
    $attrs = shortcode_atts(
        [
            'image_id' => '',
            'name' => '',
            // Must be kept because there are many pages using it.
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
        $attrs['range'],
        $attrs['type'],
        $attrs['image_id'],
    );

    // Wordpress puts <br /> at the start and end of the content.
    $content = strval(str_replace('<br />', '', $content));

    // Don't make the range part of the name for some ranges.
    if (in_array($jewellery_page->getRange(), ['archive', 'singles'])) {
        $range_in_piece_name = '';
    } else {
        $range_in_piece_name = $jewellery_page->getRange() . ' ';
    }

    $html = <<<'END_OF_HTML'
<div class="flexboxrow">
  <div class="individual-jewellery-div">

END_OF_HTML;
    if (count($jewellery_page->getImages()) > 1) {
        $html .= MakeImageNavigation($jewellery_page, $range_in_piece_name);
    }

    $html .= <<<END_OF_HTML
    <div width="{$jewellery_page->getWidthStr()}"
      height="{$jewellery_page->getHeightStr()}">
      <img id="individual-jewellery-image"
        class="block aligncenter"
        alt="{$range_in_piece_name}{$jewellery_page->getName()}"
        src="{$jewellery_page->getImages()[0]->getUrl()}"
        width="{$jewellery_page->getImages()[0]->getWidthStr()}"
        height="{$jewellery_page->getImages()[0]->getHeightStr()}" />
    </div>
  </div>
  <div class="individual-jewellery-description">
    <p class="highlight larger-text">
      {$range_in_piece_name}{$jewellery_page->getName()}</p>
    <p>{$content}</p>

    <p>See other items in this range:
      <a href="/jewellery/{$jewellery_page->getRange()}/">
        {$jewellery_page->getRange()}</a></p>
    <p>See other: <a href="/jewellery/{$jewellery_page->getType()}/">
      {$jewellery_page->getType()}</a></p>
  </div>
</div>

END_OF_HTML;
    // Shortcodes need to be expanded.
    return do_shortcode($html);
}
