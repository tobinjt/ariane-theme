<?php

// Clean up all state set up by tests.
function clear_wordpress_testing_state() {
  WP_Query::clear_query_results();
  clear_image_info();
  clear_add_action();
  clear_page_state();
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

// Functions about the page state, type of page, etc.
function clear_page_state() {
  global $PAGE_STATE;
  $PAGE_STATE = array();
}

function is_404(): bool {
  global $PAGE_STATE;
  if (isset($PAGE_STATE['is_404'])) {
    return $PAGE_STATE['is_404'];
  }
  return false;
}

function set_is_404(bool $is) {
  global $PAGE_STATE;
  $PAGE_STATE['is_404'] = $is;
}

function is_single(): bool {
  global $PAGE_STATE;
  if (isset($PAGE_STATE['is_single'])) {
    return $PAGE_STATE['is_single'];
  }
  return false;
}

function set_is_single(bool $is) {
  global $PAGE_STATE;
  $PAGE_STATE['is_single'] = $is;
}

function is_page(): bool {
  global $PAGE_STATE;
  if (isset($PAGE_STATE['is_page'])) {
    return $PAGE_STATE['is_page'];
  }
  return false;
}

function set_is_page(bool $is) {
  global $PAGE_STATE;
  $PAGE_STATE['is_page'] = $is;
}

function wp_title(): string {
  global $PAGE_STATE;
  if (isset($PAGE_STATE['wp_title'])) {
    return $PAGE_STATE['wp_title'];
  }
  return false;
}

function set_wp_title(string $title) {
  global $PAGE_STATE;
  $PAGE_STATE['wp_title'] = $title;
}

function get_bloginfo(string $param): string {
  $values = array(
    'name' => 'BLOG NAME',
    'template_directory' => 'DIR',
  );
  assert(isset($values[$param]));
  return $values[$param];
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
