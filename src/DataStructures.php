<?php
declare(strict_types=1);

/*. require_module 'core'; .*/
/*. require_module 'wordpress'; .*/
require_once(__DIR__ . '/Cast.php');

/* Wrap wp_get_attachment_image_src() to return an object. */
class WPImageInfo {
  public string $url = '';
  public int $height_int = 0;
  public string $height_str = '';
  public int $width_int = 0;
  public string $width_str = '';

  public function __construct(int $attachment_id, string $size) {
    // $image_info is an array of [url (str), width (int), height (int)].
    $image_info = wp_get_attachment_image_src($attachment_id, $size);
    $this->url = strval($image_info[0]);
    $this->width_int = $image_info[1];
    $this->height_int = $image_info[2];
    $this->width_str = strval($this->width_int);
    $this->height_str = strval($this->height_int);
  }
}

/* Represents a single entry from a Jewellery Grid. */
class JewelleryGridEntry {
  public string $range = '';
  public string $alt = '';
  public array $image_ids = array(0);
  public string $page_url = '';
  // TODO: should $product_id be an int?
  public string $product_id = '';
  // Sadly PHPLint cannot parse the combination of PHP type and PHPLint type :(
  public /*. array[int]WPImageInfo .*/ $images = array();

  public function __construct(string $range, string $alt, string $image_ids,
    string $page_url, string $product_id) {
    $this->range = $range;
    $this->alt = $alt;
    $this->page_url = $page_url;
    $this->product_id = $product_id;
    $this->image_ids = array();
    $this->images = array();

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
}

?>
