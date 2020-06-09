<?php
// Extras needed by PHPLint.
/*. require_module 'array'; .*/
/*. require_module 'core'; .*/
/*. require_module 'phpinfo'; .*/
/*. int[int] .*/ $QUERY_RESULTS = array();
/*. int[int] .*/ $EXPECTED_ADD_ACTION = array();
/*. int[int] .*/ $IMAGE_INFO = array();
/*. bool[string] .*/ $PAGE_STATE = array();

// Clean up all state set up by tests.
// phplint: /*. void .*/ function clear_wordpress_testing_state() {}
function clear_wordpress_testing_state(): void {
  WP_Query::clear_query_results();
  clear_image_info();
  clear_add_action();
  clear_page_state();
}

// Clean up all state set up by tests.
// phplint: /*. void .*/ function verify_wordpress_testing_state() {}
function verify_wordpress_testing_state(): void {
  verify_add_action();
}

// Wordpress functions we need to fake.
// phplint: /*. array .*/ function shortcode_atts(/*. array .*/ $array1, /*. array .*/ $array2) {}
function shortcode_atts(array $array1, array $array2): array {
  return array_merge($array1, $array2);
}

// phplint: /*. string .*/ function do_shortcode(/*. string .*/ $content) {}
function do_shortcode(string $content): string {
  return $content;
}

// Functions about the page state, type of page, etc.
// phplint: /*. void .*/ function clear_page_state() {}
function clear_page_state(): void {
  global $PAGE_STATE;
  $PAGE_STATE = array();
}

// phplint: /*. bool .*/ function is_404() {}
function is_404(): bool {
  global $PAGE_STATE;
  if (isset($PAGE_STATE['is_404'])) {
    return $PAGE_STATE['is_404'];
  }
  return false;
}

// phplint: /*. bool .*/ function set_is_404(/*. bool .*/ $is) {}
function set_is_404(bool $is): void {
  global $PAGE_STATE;
  $PAGE_STATE['is_404'] = $is;
}

// phplint: /*. bool .*/ function is_single() {}
function is_single(): bool {
  global $PAGE_STATE;
  if (isset($PAGE_STATE['is_single'])) {
    return $PAGE_STATE['is_single'];
  }
  return false;
}

// phplint: /*. void .*/ function set_is_single(/*. bool .*/ $is) {}
function set_is_single(bool $is): void {
  global $PAGE_STATE;
  $PAGE_STATE['is_single'] = $is;
}

// phplint: /*. bool .*/ function is_page() {}
function is_page(): bool {
  global $PAGE_STATE;
  if (isset($PAGE_STATE['is_page'])) {
    return $PAGE_STATE['is_page'];
  }
  return false;
}

// phplint: /*. void .*/ function set_is_page(/*. bool .*/ $is) {}
function set_is_page(bool $is): void {
  global $PAGE_STATE;
  $PAGE_STATE['is_page'] = $is;
}

// phplint: /*. string .*/ function wp_title() {}
function wp_title(): string {
  global $PAGE_STATE;
  if (isset($PAGE_STATE['wp_title'])) {
    return $PAGE_STATE['wp_title'];
  }
  return false;
}

// phplint: /*. void .*/ function set_wp_title(/*. string .*/ $title) {}
function set_wp_title(string $title): void {
  global $PAGE_STATE;
  $PAGE_STATE['wp_title'] = $title;
}

// phplint: /*. string .*/ function get_bloginfo(/*. string .*/ $param) {}
function get_bloginfo(string $param): string {
  $values = array(
    'name' => 'BLOG NAME',
    'template_directory' => 'DIR',
  );
  assert(isset($values[$param]));
  return $values[$param];
}

// Functions for add_action.
// phplint: /*. void .*/ function clear_add_action() {}
function clear_add_action(): void {
  global $EXPECTED_ADD_ACTION;
  $EXPECTED_ADD_ACTION = array();
}

// phplint: /*. void .*/ function expect_add_action(/*. string .*/ $section, /*. string .*/ $function, /*. int .*/ $num_calls) {}
function expect_add_action(string $section, string $function, int $num_calls): void {
  // $section is unused.
  global $EXPECTED_ADD_ACTION;
  $EXPECTED_ADD_ACTION[$function] = $num_calls;
}

// phplint: /*. void .*/ function add_action(/*. string .*/ $section, /*. string .*/ $function) {}
function add_action(string $section, string $function): void {
  assert($section == 'wp_footer');
  assert(function_exists($function), $function . ' is not a function');
  global $EXPECTED_ADD_ACTION;
  assert(array_key_exists($function, $EXPECTED_ADD_ACTION),
    $function . ' was not registered with expect_add_action');
  $EXPECTED_ADD_ACTION[$function] -= 1;
}

// phplint: /*. void .*/ function verify_add_action() {}
function verify_add_action(): void {
  global $EXPECTED_ADD_ACTION;
  foreach ($EXPECTED_ADD_ACTION as $function => $should_be_zero) {
    assert($should_be_zero == 0,
      'Non-zero remaining calls (' . $should_be_zero . ') for ' . $function);
  }
}

// Functions for wp_get_attachment_image_src.
// phplint: /*. array .*/ function wp_get_attachment_image_src(/*. int .*/ $image_id, /*. string .*/ $size) {}
function wp_get_attachment_image_src(int $image_id, string $size): array {
  global $IMAGE_INFO;
  return $IMAGE_INFO[$image_id][$size];
}

// phplint: /*. void .*/ function clear_image_info() {}
function clear_image_info(): void {
  global $IMAGE_INFO;
  $IMAGE_INFO = array();
}

// phplint: /*. void .*/ function add_image_info(/*. int .*/ $image_id, /*. string .*/ $size, /*. array .*/ $info) {}
function add_image_info(int $image_id, string $size, array $info): void {
  global $IMAGE_INFO;
  $IMAGE_INFO[$image_id][$size] = $info;
}

// Fake WP_Query.
class WP_Query {
  public $posts;
  public function __construct($query) {
    // $query is unused.
    global $QUERY_RESULTS;
    $this->posts = $QUERY_RESULTS;
  }

  // Helper functions for populating $QUERY_RESULTS.
  public static function clear_query_results(): void {
    global $QUERY_RESULTS;
    $QUERY_RESULTS = array();
  }

  public static function add_query_result(WP_Post $result): void {
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
