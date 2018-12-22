<?php
global $IMAGE_INFO;
$IMAGE_INFO = array();

// Wordpress functions we need to fake.
function shortcode_atts(array $array1, array $array2): array {
  return array_merge($array1, $array2);
}

function do_shortcode(string $content): string {
  return $content;
}

function add_image_info(string $image_id, string $size, array $info) {
  global $IMAGE_INFO;
  $IMAGE_INFO[$image_id][$size] = $info;
}

function wp_get_attachment_image_src(int $image_id, string $size): array {
  global $IMAGE_INFO;
  return $IMAGE_INFO[$image_id][$size];
}
?>
