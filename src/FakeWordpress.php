<?php

declare(strict_types=1);

// Extras needed by PHPLint.
/*. require_module 'array'; .*/
/*. require_module 'core'; .*/
/*. require_module 'phpinfo'; .*/
// $GLOBALS['QUERY_RESULTS'] is declared after class WP_Post.
/*. array[string]int .*/ $GLOBALS['EXPECTED_ADD_ACTION'] = [];
/*. array[int][string][int]int .*/ $GLOBALS['IMAGE_INFO'] = [];
/*. array[string]bool .*/ $GLOBALS['PAGE_STATE_BOOL'] = [];
/*. array[string]string .*/ $GLOBALS['PAGE_STATE_STRING'] = [];

// BEGIN PHPLINT

// Fake WP_Post.
class WP_Post
{
    public int $ID = 0;
    public string $post_content = '';
    public function __construct(int $ID, string $post_content)
    {
        $this->ID = $ID;
        $this->post_content = $post_content;
    }
}

// Needs to be declared after class WP_Post.
/*. array[int]WP_Post .*/ $GLOBALS['QUERY_RESULTS'] = [];

// Fake WP_Query.
class WP_Query
{
/** @var array<int, WP_Post> */
    /*. array[int]WP_Post .*/ public array $posts = [];

    /**
     * @param array<mixed> $query
     */
    public function __construct(array $query)
    {
        // $query is unused.
        $query[] = 'make the linter happy.';
        $this->posts = $GLOBALS['QUERY_RESULTS'];
    }

    // Helper functions for populating $GLOBALS['QUERY_RESULTS'].
    public static function clearQueryResults(): void
    {
        $GLOBALS['QUERY_RESULTS'] = [];
    }

    public static function addQueryResult(WP_Post $result): void
    {
        $GLOBALS['QUERY_RESULTS'][] = $result;
    }
}

// END PHPLINT

// Wordpress functions we need to fake.
// phplint: /*. array .*/ function shortcode_atts(/*. array .*/ $array1, /*. array .*/ $array2) {}
/**
 * @param array<mixed> $array1
 * @param array<mixed> $array2
 *
 * @return array<mixed>
 */
function shortcode_atts(array $array1, array $array2): array
{
    return array_merge($array1, $array2);
}

// phplint: /*. string .*/ function do_shortcode(/*. string .*/ $content) {}
function do_shortcode(string $content): string
{
    return $content;
}

// Functions about the page state, type of page, etc.
// phplint: /*. void .*/ function clear_page_state() {}
function clear_page_state(): void
{
    $GLOBALS['PAGE_STATE_BOOL'] = [];
}

// phplint: /*. bool .*/ function is_404() {}
function is_404(): bool
{
    if (isset($GLOBALS['PAGE_STATE_BOOL']['is_404'])) {
        return $GLOBALS['PAGE_STATE_BOOL']['is_404'];
    }
    return false;
}

// phplint: /*. bool .*/ function set_is_404(/*. bool .*/ $is) {}
function set_is_404(bool $is): void
{
    $GLOBALS['PAGE_STATE_BOOL']['is_404'] = $is;
}

// phplint: /*. bool .*/ function is_single() {}
function is_single(): bool
{
    if (isset($GLOBALS['PAGE_STATE_BOOL']['is_single'])) {
        return $GLOBALS['PAGE_STATE_BOOL']['is_single'];
    }
    return false;
}

// phplint: /*. void .*/ function set_is_single(/*. bool .*/ $is) {}
function set_is_single(bool $is): void
{
    $GLOBALS['PAGE_STATE_BOOL']['is_single'] = $is;
}

// phplint: /*. bool .*/ function is_page() {}
function is_page(): bool
{
    if (isset($GLOBALS['PAGE_STATE_BOOL']['is_page'])) {
        return $GLOBALS['PAGE_STATE_BOOL']['is_page'];
    }
    return false;
}

// phplint: /*. void .*/ function set_is_page(/*. bool .*/ $is) {}
function set_is_page(bool $is): void
{
    $GLOBALS['PAGE_STATE_BOOL']['is_page'] = $is;
}

// phplint: /*. string .*/ function wp_title(/*. string .*/ $sep, /*. bool .*/ $display) {}
function wp_title(string $sep, bool $display): string
{
    $sep .= 'make the linter happy.';
    // $display is otherwise unused, so use it to make the linter happy.
    if (! $display) {
        $display = false;
    }
    if (isset($GLOBALS['PAGE_STATE_STRING']['wp_title'])) {
        return $GLOBALS['PAGE_STATE_STRING']['wp_title'];
    }
    return '';
}

// phplint: /*. void .*/ function set_wp_title(/*. string .*/ $title) {}
function set_wp_title(string $title): void
{
    $GLOBALS['PAGE_STATE_STRING']['wp_title'] = $title;
}

// phplint: /*. string .*/ function get_bloginfo(/*. string .*/ $param) {}
function get_bloginfo(string $param): string
{
    $values = [
        'name' => 'BLOG NAME',
        'template_directory' => 'DIR',
    ];
    assert(isset($values[$param]));
    return $values[$param];
}

// Functions for add_action.
// phplint: /*. void .*/ function clear_add_action() {}
function clear_add_action(): void
{
    $GLOBALS['EXPECTED_ADD_ACTION'] = [];
}

// phplint: /*. void .*/ function expect_add_action(/*. string .*/ $section, /*. string .*/ $func, /*. int .*/ $num_calls) {}
function expect_add_action(string $section, string $func, int $num_calls): void
{
    // $section is unused.
    $section .= 'make the linter happy.';
    $GLOBALS['EXPECTED_ADD_ACTION'][$func] = $num_calls;
}

// phplint: /*. void .*/ function add_action(/*. string .*/ $section, /*. string .*/ $func) {}
function add_action(string $section, string $func): void
{
    assert($section === 'wp_footer');
    assert(function_exists($func), new Exception($func . ' is not a function'));
    assert(
        array_key_exists($func, $GLOBALS['EXPECTED_ADD_ACTION']),
        new Exception($func . ' was not registered with expect_add_action')
    );
    $GLOBALS['EXPECTED_ADD_ACTION'][$func] -= 1;
}

// phplint: /*. void .*/ function verify_add_action() {}
function verify_add_action(): void
{
    foreach ($GLOBALS['EXPECTED_ADD_ACTION'] as $func => $should_be_zero) {
        assert(
            $should_be_zero === 0,
            new Exception(
                "Non-zero remaining calls ({$should_be_zero}) for {$func}"
            )
        );
    }
}

// Functions for wp_get_attachment_image_src.
// phplint: /*. array[int]int .*/ function wp_get_attachment_image_src(/*. int .*/ $image_id, /*. string .*/ $size) {}
/**
 * @return array<int, int>
 */
function wp_get_attachment_image_src(int $image_id, string $size): array
{
    return $GLOBALS['IMAGE_INFO'][$image_id][$size];
}

// phplint: /*. void .*/ function clear_image_info() {}
function clear_image_info(): void
{
    $GLOBALS['IMAGE_INFO'] = [];
}

// phplint: /*. void .*/ function add_image_info(/*. int .*/ $image_id, /*. string .*/ $size, /*. array .*/ $info) {}
/**
 * @param array<int, int|string> $info
 */
function add_image_info(int $image_id, string $size, array $info): void
{
    $GLOBALS['IMAGE_INFO'][$image_id][$size] = $info;
}

// Clean up all state set up by tests.
// phplint: /*. void .*/ function clear_wordpress_testing_state() {}
function clear_wordpress_testing_state(): void
{
    WP_Query::clearQueryResults();
    clear_image_info();
    clear_add_action();
    clear_page_state();
}

// Verify all state set up by tests.
// phplint: /*. void .*/ function verify_wordpress_testing_state() {}
function verify_wordpress_testing_state(): void
{
    verify_add_action();
}
