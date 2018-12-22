<?php

// Clean up all state set up by tests.
function clear_wordpress_testing_state() {
  WP_Query::clear_query_results();
  clear_image_info();
}

global $IMAGE_INFO;
$IMAGE_INFO = array();

// Wordpress functions we need to fake.
function shortcode_atts(array $array1, array $array2): array {
  return array_merge($array1, $array2);
}

function do_shortcode(string $content): string {
  return $content;
}

function add_action(string $section, string $function) {
  assert($section == 'wp_footer');
  assert(function_exists($function));
}

function wp_get_attachment_image_src(int $image_id, string $size): array {
  global $IMAGE_INFO;
  return $IMAGE_INFO[$image_id][$size];
}

// Setup functions for wp_get_attachment_image_src.
function clear_image_info() {
  global $IMAGE_INFO;
  $IMAGE_INFO = array();
}

function add_image_info(int $image_id, string $size, array $info) {
  global $IMAGE_INFO;
  $IMAGE_INFO[$image_id][$size] = $info;
}

global $QUERY_RESULTS;
$QUERY_RESULTS = array();
class WP_Query {
  public $posts;
  public function __construct($query) {
    // $query is unused.  TODO: validate?
    global $QUERY_RESULTS;
    $this->posts = $QUERY_RESULTS;
  }

  // Helper functions for populating $QUERY_RESULTS.
  public static function clear_query_results() {
    global $QUERY_RESULTS;
    $QUERY_RESULTS = array();
  }

  public static function add_query_result(WP_Post $result) {
    global $QUERY_RESULTS;
    $QUERY_RESULTS[] = $result;
  }
}

class WP_Post {
  public $ID;
  public $post_content;
  public function __construct(int $ID, string $post_content) {
    $this->ID = $ID;
    $this->post_content = $post_content;
  }
}
?>
