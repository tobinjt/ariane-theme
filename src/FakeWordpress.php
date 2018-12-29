<?php

// Clean up all state set up by tests.
function clear_wordpress_testing_state() {
  WP_Query::clear_query_results();
  clear_image_info();
  clear_add_action();
}

// Clean up all state set up by tests.
function verify_wordpress_testing_state() {
  verify_add_action();
}

// Wordpress functions we need to fake.
function shortcode_atts(array $array1, array $array2): array {
  return array_merge($array1, $array2);
}

function do_shortcode(string $content): string {
  return $content;
}

// Functions for add_action.
function clear_add_action() {
  global $EXPECTED_ADD_ACTION;
  $EXPECTED_ADD_ACTION = array();
}

function expect_add_action(string $section, string $function, int $num_calls) {
  // $section is unused.
  global $EXPECTED_ADD_ACTION;
  $EXPECTED_ADD_ACTION[$function] = $num_calls;
}

function add_action(string $section, string $function) {
  assert($section == 'wp_footer');
  assert(function_exists($function), $function . ' is not a function');
  global $EXPECTED_ADD_ACTION;
  assert(array_key_exists($function, $EXPECTED_ADD_ACTION),
    $function . ' was not registered with expect_add_action');
  $EXPECTED_ADD_ACTION[$function] -= 1;
}

function verify_add_action() {
  global $EXPECTED_ADD_ACTION;
  foreach ($EXPECTED_ADD_ACTION as $function => $should_be_zero) {
    assert($should_be_zero == 0,
      'Non-zero remaining calls (' . $should_be_zero . ') for ' . $function);
  }
}

// Functions for wp_get_attachment_image_src.
function wp_get_attachment_image_src(int $image_id, string $size): array {
  global $IMAGE_INFO;
  return $IMAGE_INFO[$image_id][$size];
}

function clear_image_info() {
  global $IMAGE_INFO;
  $IMAGE_INFO = array();
}

function add_image_info(int $image_id, string $size, array $info) {
  global $IMAGE_INFO;
  $IMAGE_INFO[$image_id][$size] = $info;
}

// Fake WP_Query.
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
