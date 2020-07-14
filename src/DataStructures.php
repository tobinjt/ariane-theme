<?php
declare(strict_types=1);

/*. require_module 'core'; .*/
/*. require_module 'wordpress'; .*/
require_once(__DIR__ . '/Cast.php');

/* Wrap wp_get_attachment_image_src() to return an object. */
class WPImageInfo {
  public $url = '';
  public $height_int = 0;
  public $height_str = '';
  public $width_int = 0;
  public $width_str = '';

  public function __construct(int $attachment_id, string $size) {
    // $image_info is an array of [url (str), width (int), height (int)].
    $image_info = wp_get_attachment_image_src($attachment_id, $size);
    $this->url = strval($image_info[0]);
    $this->width_int = $image_info[1];
    $this->height_int = $image_info[1];
    $this->width_str = strval($this->width_int);
    $this->height_str = strval($this->height_int);
  }
}

/* Represents a single entry from a Jewellery Grid. */
class JewelleryGridEntry {
  public $range = '';
  public $alt = '';
  public $image_ids = array(0);
  public $page_url = '';
  public $product_id = 0;
  public $images = array();

  public function __construct(string $range, string $alt, string $image_ids,
    string $page_url, int $product_id) {
    $this->range = $range;
    $this->alt = $alt;
    $this->page_url = $page_url;
    $this->product_id = $product_id;
    $this->image_ids = array();

    $ids = explode(',', $image_ids);
    foreach ($ids as $i => $id) {
      $this->image_ids[] = intval($id);
      $this->images[] = new WPImageInfo($this->image_ids[-1], 'grid_size');
    }
  }
}

?>
