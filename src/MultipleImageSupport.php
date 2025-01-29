<?php

declare(strict_types=1);

// Support for multiple images in a page: sliders and manually changed.

/* Used to collect slider configs and set them up.  Maps ID => JSON-encoded
 * image info.  $GLOBALS['SLIDER_IMAGES'] = [];
 */
/* Used to collect change images configs and set them up.
 * Maps ID => json_encoded(array(raw image info)).
 * $GLOBALS['CHANGE_IMAGES'] = [];
 */

/**
 * @return array<string, string>
 */
function get_slider_images(): array
{
    return $GLOBALS['SLIDER_IMAGES'];
}

function clear_slider_images(): void
{
    $GLOBALS['SLIDER_IMAGES'] = [];
}

function add_slider_image(string $id, string $json): void
{
    $GLOBALS['SLIDER_IMAGES'][$id] = $json;
}

/**
 * @return array<string, string>
 */
function get_change_images(): array
{
    return $GLOBALS['CHANGE_IMAGES'];
}

function clear_change_images(): void
{
    $GLOBALS['CHANGE_IMAGES'] = [];
}

function add_change_image(string $id, string $json): void
{
    $GLOBALS['CHANGE_IMAGES'][$id] = $json;
}

/* SliderImages: Dynamically build the JavaScript array of images when
 * displaying the slider.
 * Returns:
 *  array of image information, needs to be passed to json_encode().
 */
/**
 * @return array<array<string, string>>
 */
function SliderImages(): array
{
    $media_query = new WP_Query(
        [
            'post_type' => 'attachment',
            'post_status' => 'any',
            'posts_per_page' => '-1',
            's' => 'slider',
        ]
    );
    $images = [];
    foreach ($media_query->posts as $post) {
        $matches = [];
        if (preg_match(
            '/^\\s*slider\\s+([^ ]+)\\s*$/',
            $post->post_content,
            $matches
        ) === 1) {
            $image_large = new WPImageInfo($post->ID, 'slider_large');
            $image_small = new WPImageInfo($post->ID, 'slider_small');
            // These intermediate variables are used to keep the srcset line
            // manageable.
            $l_url = $image_large->getUrl();
            $l_w_w = $image_large->getWidthStr() . 'w';
            $l_w_px = $image_large->getWidthStr() . 'px';
            $s_url = $image_small->getUrl();
            $s_w_w = $image_small->getWidthStr() . 'w';
            $s_w_px = $image_small->getWidthStr() . 'px';
            $images[] = [
                'src' => $image_large->getUrl(),
                'href' => strval($matches[1]),
                'srcset' => "{$l_url} {$l_w_w}, {$s_url} {$s_w_w}",
                'sizes' => "(max-width: 799px) {$s_w_px}, {$l_w_px}",
            ];
        }
    }
    return $images;
}

/* SliderSetupGeneric: output the JavaScript needed to set up the slider,
 * including the images.  Should be called indirectly by Wordpress, by
 * registering it with:
 * add_action('wp_footer', 'SliderSetupGeneric');
 */
function SliderSetupGeneric(): void
{
    $template_directory = get_bloginfo('template_directory');
    $output = <<<END_OF_JAVASCRIPT
<!-- Include necessary JavaScript. -->
<script type="text/javascript" src="/wp-includes/js/jquery/jquery.min.js"
  id="jquery-core-js"></script>
<script type="text/javascript" src="{$template_directory}/slider.js"></script>
<!-- Start of SliderSetup. -->
<script type="text/javascript">
jQuery(document).ready(function() {

END_OF_JAVASCRIPT;
    $is_dev_website = is_dev_website() ? 'true' : 'false';
    foreach (get_slider_images() as $id_prefix => $images) {
        $images = trim($images);
        $output .= <<<END_OF_JAVASCRIPT
  Slider.initialise({'id_prefix': '{$id_prefix}',
                     'log_to_console': {$is_dev_website}},
                    {$images});

END_OF_JAVASCRIPT;
    }
    $output .= <<<END_OF_JAVASCRIPT
});
</script>
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
/**
 * @param array<array<string, string>> $images
 */
function FrontPageSliderSetup(array $images): string
{
    add_action('wp_footer', 'SliderSetupGeneric');
    add_slider_image('#slider', json_encode_wrapper($images));
    return <<<END_OF_HTML
<div id="slider-div" class="aligncenter">
  <a href="{$images[0]['href']}" id="slider-link"
    alt="Selection of Ariane's best work">
    <img id="slider-image" src="{$images[0]['src']}"
      class="block aligncenter"
      alt="Selection of Ariane's best work"
      srcset="{$images[0]['srcset']}"
      sizes="{$images[0]['sizes']}" />
  </a>
</div>

END_OF_HTML;
}

/* ChangeImagesSetupGeneric: output the JavaScript needed to set up changing
 * of images by clicking on thumbnails, including the images.  Should be
 * called indirectly by Wordpress, by registering it with:
 * add_action('wp_footer', 'ChangeImagesSetupGeneric');
 */
function ChangeImagesSetupGeneric(): void
{
    $images = [];
    foreach (get_change_images() as $key => $value) {
        $images[$key] = json_decode($value, true);
    }
    $images_json = json_encode_wrapper($images);

    $output = <<<END_OF_JAVASCRIPT
<!-- Include necessary JavaScript. -->
<script type="text/javascript" src="/wp-includes/js/jquery/jquery.min.js"
  id="jquery-core-js"></script>
<!-- Start of ChangeImages. -->
<script type="text/javascript">
function change_image(i, id) {
  var images = {$images_json};
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
