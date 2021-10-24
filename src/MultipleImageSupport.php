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
      'posts_per_page' => '-1',
      's'              => 'slider',
    )
  );
  /*. array[int][string]string .*/ $images = array();
  foreach ($media_query->posts as $post) {
    /*. array[int]mixed .*/ $matches = array();
    if (preg_match('/^\\s*slider\\s+([^ ]+)\\s*$/', $post->post_content, $matches) === 1) {
      $image_large = new WPImageInfo($post->ID, 'slider_large');
      $image_small = new WPImageInfo($post->ID, 'slider_small');
      // All the intermediate variables are due to PHPLint parsing restrictions,
      // particularly it's impossible to use {$width}px or similar.
      $l_url = $image_large->url;
      $l_w_w = $image_large->width_str . 'w';
      $l_w_px = $image_large->width_str . 'px';
      $s_url = $image_small->url;
      $s_w_w = $image_small->width_str . 'w';
      $s_w_px = $image_small->width_str . 'px';
      $images[] = array(
        'src' => $image_large->url,
        'href' => strval($matches[1]),
        'srcset' => "$l_url $l_w_w, $s_url $s_w_w",
        'sizes' => "(max-width: 799px) $s_w_px, $l_w_px",
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
<div id="slider-div" class="aligncenter">
  <a href="$href" id="slider-link"
    alt="Selection of Ariane's best work">
    <img id="slider-image" src="$src"
      class="individual-jewellery-image aligncenter"
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
  $images = json_encode_wrapper($CHANGE_IMAGES);
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
