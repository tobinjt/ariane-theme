<?php
/* Support for multiple images in a page: sliders and manually changed.  */

/* Used to collect slider configs and set them up.  Maps ID => JSON-encoded
 * image info.
 */
global $SLIDER_IMAGES;
$SLIDER_IMAGES = array();
/* Used to collect change_images configs and set them up.  Maps ID => raw
 * image info.
 */
global $CHANGE_IMAGES;
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
  $images = array();
  foreach ($media_query->posts as $post) {
    $matches = array();
    if (preg_match('/^\s*slider\s+([^ ]+)$/', $post->post_content, $matches)) {
      $image_large = wp_get_attachment_image_src($post->ID, 'slider_large');
      $image_small = wp_get_attachment_image_src($post->ID, 'slider_small');
      $images[] = array(
        'src' => $image_large[0],
        'href' => $matches[1],
        'srcset' => ("{$image_large[0]} {$image_large[1]}w,\n"
                     . " {$image_small[0]} {$image_small[1]}w"),
        'sizes' => ("(max-width: 799px) {$image_small[1]}px,\n"
                    . " {$image_large[1]}px"),
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
function SliderSetupGeneric() {
  $output = <<<END_OF_JAVASCRIPT
<!-- Start of SliderSetup. -->
<script type="text/javascript">
jQuery(document).ready(function() {

END_OF_JAVASCRIPT;
  $is_dev_website = is_dev_website() ? 'true' : 'false';
  global $SLIDER_IMAGES;
  foreach ($SLIDER_IMAGES as $id_prefix => $images) {
    $images = trim($images);
    $output .= <<<END_OF_JAVASCRIPT
Slider.initialise({'id_prefix': '{$id_prefix}',
                   'log_to_console': {$is_dev_website}},
                  {$images});
END_OF_JAVASCRIPT;
  }
  $template_directory = get_bloginfo('template_directory');
  $output .= <<<END_OF_JAVASCRIPT
});
</script>
<!-- Include the rest of the Javascript. -->
<script type="text/javascript" src="{$template_directory}/slider.js"></script>
<!-- End of SliderSetup. -->

END_OF_JAVASCRIPT;
  echo $output;
}

/* FrontPageSliderSetupShortcode: wrap SliderSetupGeneric to provide a
 * shortcode.  This *must not* be used in the enclosing form.
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
function FrontPageSliderSetupShortcode(string $atts, string $content,
                                       string $tag): string {
  if (!is_null($content) and $content != '') {
    return '<h1>FrontPageSliderSetupShortcode: no content accepted!  Given: '
      . htmlspecialchars($content) . '</h1>' . "\n";
  }
  add_action('wp_footer', 'SliderSetupGeneric');
  $images = SliderImages('slider_large');
  global $SLIDER_IMAGES;
  $SLIDER_IMAGES['#slider'] = json_encode($images);
  $image = $images[0];
  $html = <<<END_OF_HTML
<div id="slider-div">
<a href="{$image['href']}" id="slider-link"
  alt="Selection of Ariane's best work">
  <img id="slider-image" src="{$image['src']}"
    alt="Selection of Ariane's best work"
    srcset="{$image['srcset']}"
    sizes="{$image['sizes']}" />
</a>
</div>
END_OF_HTML;
  return $html;
}

/* SliderSetupShortcode: wrap SliderSetupGeneric to provide a shortcode.  This
 * *must not* be used in the enclosing form.
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
function SliderSetupShortcode(array $atts, string $content,
                              string $tag): string {
  if (!is_null($content) and $content != '') {
    return '<h1>SliderSetupShortcode: no content accepted!  Given: '
      . htmlspecialchars($content) . '</h1>' . "\n";
  }
  add_action('wp_footer', 'SliderSetupGeneric');
  return '';
}

/* ChangeImagesSetupGeneric: output the Javascript needed to set up changing
 * of images by clicking on thumbnails, including the images.  Should be
 * called indirectly by Wordpress, by registering it with:
 * add_action('wp_footer', 'ChangeImagesSetupGeneric');
 */
function ChangeImagesSetupGeneric() {
  global $CHANGE_IMAGES;
  $images = json_encode($CHANGE_IMAGES);
  $output = <<<END_OF_JAVASCRIPT
<!-- Start of ChangeImages. -->
<script type="text/javascript">
function change_image(i, id) {
var images = {$images};
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

/* ChangeImagesSetupShortcode: wrap ChangeImagesSetupGeneric to provide a
 * shortcode.  This *must not* be used in the enclosing form.
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
function ChangeImagesSetupShortcode(string $atts, string $content=null,
                                    string $tag): string {
  if (!is_null($content) and $content != '') {
    return '<h1>slider: no content accepted!  Given: '
      . htmlspecialchars($content) . '</h1>' . "\n";
  }
  add_action('wp_footer', 'ChangeImagesSetupGeneric');
  return '';
}
?>
