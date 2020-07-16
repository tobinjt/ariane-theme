<?php
declare(strict_types=1);

// Support for multiple images in a page: sliders and manually changed.

// Extras needed by PHPLint.
/*. require_module 'core'; .*/
/*. require_module 'pcre'; .*/
require_once(__DIR__ . '/Cast.php');
require_once(__DIR__ . '/DataStructures.php');
require_once(__DIR__ . '/Urls.php');
/*. array[int][string]string .*/ $CHANGE_IMAGES = array();
/*. array[string]string .*/ $SLIDER_IMAGES = array();

/* Used to collect slider configs and set them up.  Maps ID => JSON-encoded
 * image info.
 */
$SLIDER_IMAGES = array();
/* Used to collect change images configs and set them up.  Maps ID => raw
 * image info.
 */
$CHANGE_IMAGES = array();

/* SliderImages: Dynamically build the Javascript array of images when
 * displaying the slider.
 * Returns:
 *  array of image information, needs to be passed to json_encode().
 */
function SliderImages(): array {
  $media_query = new WP_Query(
    array(
      'post_type'      => 'attachment',
      'post_status'    => 'any',
      'posts_per_page' => -1,
      's'              => 'slider',
    )
  );
  /*. array[int][string]string .*/ $images = array();
  foreach ($media_query->posts as $post) {
    /*. array[int]mixed .*/ $matches = array();
    if (preg_match('/^\\s*slider\\s+([^ ]+)\\s*$/', $post->post_content, $matches) === 1) {
      $image_large = wp_get_attachment_image_src($post->ID, 'slider_large');
      $image_small = wp_get_attachment_image_src($post->ID, 'slider_small');
      $lh = $image_large[0];
      $lw_w = $image_large[1] . 'w';
      $lw_px = $image_large[1] . 'px';
      $sh = $image_small[0];
      $sw_w = $image_small[1] . 'w';
      $sw_px = $image_small[1] . 'px';
      $images[] = array(
        'src' => strval($image_large[0]),
        'href' => strval($matches[1]),
        'srcset' => "$lh $lw_w, $sh $sw_w",
        'sizes' => "(max-width: 799px) $sw_px, $lw_px",
      );
    }
  }
  return $images;
}

/* SliderSetupGeneric: output the Javascript needed to set up the slider,
 * including the images.  Should be called indirectly by Wordpress, by
 * registering it with:
 * add_action('wp_footer', 'SliderSetupGeneric');
 */
function SliderSetupGeneric(): void {
  $output = <<<'END_OF_JAVASCRIPT'
<!-- Start of SliderSetup. -->
<script type="text/javascript">
jQuery(document).ready(function() {

END_OF_JAVASCRIPT;
  $is_dev_website = is_dev_website() ? 'true' : 'false';
  global $SLIDER_IMAGES;
  foreach ($SLIDER_IMAGES as $id_prefix => $images) {
    $images = trim($images);
    $output .= <<<END_OF_JAVASCRIPT
  Slider.initialise({'id_prefix': '$id_prefix',
                     'log_to_console': $is_dev_website},
                    $images);

END_OF_JAVASCRIPT;
  }
  $template_directory = get_bloginfo('template_directory');
  $output .= <<<END_OF_JAVASCRIPT
});
</script>
<!-- Include the rest of the Javascript. -->
<script type="text/javascript" src="$template_directory/slider.js"></script>
<!-- End of SliderSetup. -->

END_OF_JAVASCRIPT;
  echo $output;
}

/* FrontPageSliderSetup: wrap SliderSetupGeneric to set up the front page image
 * slider.
 * Args:
 *  $images: array of images returned by SliderImages().
 * Returns:
 *  string, the HTML to insert in the page (Wordpress does that
 *    automatically).
 */
function FrontPageSliderSetup(array $images): string {
  add_action('wp_footer', 'SliderSetupGeneric');
  global $SLIDER_IMAGES;
  $SLIDER_IMAGES['#slider'] = json_encode_wrapper($images);
  $image = cast('array[string]string', $images[0]);
  $href = $image['href'];
  $src = $image['src'];
  $srcset = $image['srcset'];
  $sizes = $image['sizes'];
  $html = <<<END_OF_HTML
<div id="slider-div">
  <a href="$href" id="slider-link"
    alt="Selection of Ariane's best work">
    <img id="slider-image" src="$src"
      alt="Selection of Ariane's best work"
      srcset="$srcset"
      sizes="$sizes" />
  </a>
</div>

END_OF_HTML;
  return $html;
}

/* ChangeImagesSetupGeneric: output the Javascript needed to set up changing
 * of images by clicking on thumbnails, including the images.  Should be
 * called indirectly by Wordpress, by registering it with:
 * add_action('wp_footer', 'ChangeImagesSetupGeneric');
 */
function ChangeImagesSetupGeneric(): void {
  global $CHANGE_IMAGES;
  try {
    $images = json_encode($CHANGE_IMAGES);
  }
  catch (JsonException $e) {
    error_log("JSON encoding failed! $e");
  }
  $output = <<<END_OF_JAVASCRIPT
<!-- Start of ChangeImages. -->
<script type="text/javascript">
function change_image(i, id) {
var images = $images;
// Construct a new image and swap it in, otherwise it flashes awkwardly - the
// old image resizes and then the new image is displayed.
var img = jQuery(id);
var new_img = jQuery('<img>');
new_img.attr('id', img.attr('id'));
new_img.attr('alt', img.attr('alt'));
new_img.attr(images[id][i]);
img.replaceWith(new_img);
};
</script>
<!-- End of ChangeImages. -->

END_OF_JAVASCRIPT;
  echo $output;
}
