<?php

declare(strict_types=1);

/*. require_module 'core'; .*/
/*. require_module 'json'; .*/
/*. require_module 'wordpress'; .*/

/* Wrap wp_get_attachment_image_src() to return an object. */
class WPImageInfo
{
    public string $url = '';
    public int $height_int = 0;
    public string $height_str = '';
    public int $width_int = 0;
    public string $width_str = '';

    public function __construct(int $attachment_id, string $size)
    {
        // $image_info is an array of [url (str), width (int), height (int)].
        $image_info = wp_get_attachment_image_src($attachment_id, $size);
        $this->url = strval($image_info[0]);
        $this->width_int = $image_info[1];
        $this->height_int = $image_info[2];
        $this->width_str = strval($this->width_int);
        $this->height_str = strval($this->height_int);
    }

    /**
     * @return array{'src': string, 'width': int, 'height': int}
     */
    public function image_to_data(): array
    {
        # This needs to stay compatible with slider.js.
        return [
            'src' => $this->url,
            'width' => $this->width_int,
            'height' => $this->height_int,
        ];
    }
}

/* Convert an array of WPImageInfo to an array compatible with slider.js. */
/**
 * @param array<int, WPImageInfo> $images
 *
 * @return array<int, array{'src': string, 'width': int, 'height': int}>
 */
function images_to_data(array $images): array
{
    # This needs to stay compatible with slider.js.
    /*. array[int]mixed .*/ $data = [];
    foreach ($images as $image) {
        $data[] = $image->image_to_data();
    }
    return $data;
}

/* Represents a single entry from a Jewellery Grid. */
class JewelleryGridEntry
{
    public string $range = '';
    public string $alt = '';
    /** @var array<int> */
    public array $image_ids = [0];
    public string $page_url = '';
    public int $product_id = 0;
/** @var array<int, WPImageInfo> */
    /*. array[int]WPImageInfo .*/ public array $images = [];

    public function __construct(
      string $range,
      string $alt,
      string $image_ids,
      string $page_url,
      int $product_id
  ) {
        $this->range = $range;
        $this->alt = $alt;
        $this->page_url = $page_url;
        $this->product_id = $product_id;
        $this->image_ids = [];
        $this->images = [];

        if (substr($this->page_url, -1) !== '/') {
            $this->page_url .= '/';
        }

        $ids = explode(',', $image_ids);
        foreach ($ids as $id_str) {
            $id_int = intval($id_str);
            if ($id_int !== -1) {
                $this->image_ids[] = $id_int;
                $this->images[] = new WPImageInfo($id_int, 'grid_size');
            }
        }
    }

    /**
     * @return array<int, array{'src': string, 'width': int, 'height': int}>
     */
    public function images_to_data(): array
    {
        return images_to_data($this->images);
    }
}

/* Represents a Jewellery Page. */
class JewelleryPage
{
    public string $name = '';
    public int $product_id = 0;
    public string $range = '';
    public string $type = '';
    public bool $archived = false;

    /** @var array<int> */
    public array $image_ids = [0];
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
        $this->image_ids = [];
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
            $this->image_ids[] = $id_int;
            $image = new WPImageInfo($id_int, 'product_size');
            $this->images[] = $image;
            if ($image->width_int > $this->width_int) {
                $this->width_int = $image->width_int;
            }
            if ($image->height_int > $this->height_int) {
                $this->height_int = $image->height_int;
            }
        }
        $this->width_str = strval($this->width_int);
        $this->height_str = strval($this->height_int);
    }

    /**
     * @return array<int, array{'src': string, 'width': int, 'height': int}>
     */
    public function images_to_data(): array
    {
        return images_to_data($this->images);
    }
}

/* Wrap json_encode so that I can more easily ignore PHPLint warnings about the
  * exception being thrown, because I cannot find a way to test the exception
  * handling, and I care more about test coverage than lint warnings.
 */
/**
 * @param array<mixed> $data */
function json_encode_wrapper(array $data): string
{
    $result = json_encode($data);
    // TODO(johntobin): How do I test this?  Maybe I can create a recursive data
    // structure that cannot be encoded?
    if (is_bool($result)) {
        // Return an empty string rather than false on failure; this should never
        // arise in real use, but PHPStan warns about it.
        return 'JSON_ENCODE FAILED!';
    }
    return $result;
}
