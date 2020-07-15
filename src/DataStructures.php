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
    $this->height_int = $image_info[2];
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
  // TODO: should $product_id be an int?
  public $product_id = '';
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

/* Represents a Jewellery Page. */
class JewelleryPage {
  public $name = '';
  // TODO: should $product_id be an int?
  public $product_id = '';
  public $range = '';
  public $type = '';
  public $archived = false;

  public $image_ids = array(0);
  public /*. array[int]WPImageInfo .*/ $images = array();
  public $height_int = 0;
  public $height_str = '';
  public $width_int = 0;
  public $width_str = '';

  public function __construct(string $name, string $product_id, string $range,
    string $type, string $image_ids, bool $archived) {
    $this->name = $name;
    $this->product_id = $product_id;
    $this->range = $range;
    $this->type = $type;
    $this->archived = $archived;
    $this->image_ids = array();
    $this->images = array();

    // Change "necklace" to "necklaces".
    if (substr($this->type, -1) !== 's') {
      $this->type .= 's';
    }

    $ids = explode(',', $image_ids);
    foreach ($ids as $id_str) {
      $id_int = intval($id_str);
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
}

?>
