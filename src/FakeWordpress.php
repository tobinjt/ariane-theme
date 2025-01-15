<?php

declare(strict_types=1);

final class FakeWordpressState
{
    public string $expected_action = '';
    public string $wp_title = '';
    public bool $is_404 = false;
    public bool $is_page = false;
    public bool $is_single = false;
}

// Fake WP_Post.
final class WP_Post
{
    public int $ID = 0;
    public string $post_content = '';
    public function __construct(int $ID, string $post_content)
    {
        $this->ID = $ID;
        $this->post_content = $post_content;
    }
}

// Fake WP_Query.
final class WP_Query
{
/**
 * @var array<int, WP_Post>
 */
    /*. array[int]WP_Post .*/ public array $posts = [];

    /**
     * @param array<mixed> $query
     */
    public function __construct(array $query)
    {
        unused(strval($query['post_type']));
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

function get_fake_wordpress_state(): FakeWordpressState
{
    return $GLOBALS['FAKE_WORDPRESS_STATE'];
}

function clear_fake_wordpress_state(): void
{
    $GLOBALS['FAKE_WORDPRESS_STATE'] = new FakeWordpressState();
}

// Wordpress functions we need to fake.
/**
 * @param array<string> $array1
 * @param array<string> $array2
 *
 * @return array<string>
 */
function shortcode_atts(array $array1, array $array2): array
{
    return array_merge($array1, $array2);
}

function do_shortcode(string $content): string
{
    return $content;
}

// Functions about the page state, type of page, etc.
function clear_page_state(): void
{
    get_fake_wordpress_state()->is_404 = false;
    get_fake_wordpress_state()->is_page = false;
    get_fake_wordpress_state()->is_single = false;
}

function is_404(): bool
{
    return get_fake_wordpress_state()->is_404;
}

function set_is_404(bool $is): void
{
    get_fake_wordpress_state()->is_404 = $is;
}

function is_single(): bool
{
    return get_fake_wordpress_state()->is_single;
}

function set_is_single(bool $is): void
{
    get_fake_wordpress_state()->is_single = $is;
}

function is_page(): bool
{
    return get_fake_wordpress_state()->is_page;
}

function set_is_page(bool $is): void
{
    get_fake_wordpress_state()->is_page = $is;
}

function wp_title(string $sep, bool $display): string
{
    unused($sep);
    unused(strval($display));
    return get_fake_wordpress_state()->wp_title;
}

function set_wp_title(string $title): void
{
    get_fake_wordpress_state()->wp_title = $title;
}

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
function clear_add_action(): void
{
    get_fake_wordpress_state()->expected_action = '';
}

function expect_add_action(string $section, string $func): void
{
    unused($section);
    get_fake_wordpress_state()->expected_action = $func;
}

function add_action(string $section, string|callable $func, int $priority = 10): void
{
    unused(strval($priority));
    assert($section === 'wp_footer');
    assert(is_callable($func));
    assert(get_fake_wordpress_state()->expected_action === $func);
    get_fake_wordpress_state()->expected_action = '';
}

function verify_add_action(): void
{
    $expected_action = get_fake_wordpress_state()->expected_action;
    assert(
        $expected_action === '',
        new Exception("{$expected_action} wasn't called")
    );
}

// Functions for wp_get_attachment_image_src.
/**
 * @return array<int, int>
 */
function wp_get_attachment_image_src(int $image_id, string $size): array
{
    return $GLOBALS['IMAGE_INFO'][$image_id][$size];
}

function clear_image_info(): void
{
    $GLOBALS['IMAGE_INFO'] = [];
}

/**
 * @param array<int, int|string> $info
 */
function add_image_info(int $image_id, string $size, array $info): void
{
    $GLOBALS['IMAGE_INFO'][$image_id][$size] = $info;
}

// Clean up all state set up by tests.
function clear_wordpress_testing_state(): void
{
    WP_Query::clearQueryResults();
    clear_image_info();
    clear_add_action();
    clear_page_state();
    clear_styles_state();
}

// Verify all state set up by tests.
function verify_wordpress_testing_state(): void
{
    verify_add_action();
}

function clear_styles_state(): void
{
    $GLOBALS['DEQUEUED_STYLES'] = [];
}
function wp_dequeue_script(string $handle): void
{
    unused($handle);
}

function wp_dequeue_style(string $handle): void
{
    $GLOBALS['DEQUEUED_STYLES'][] = $handle;
}

function wp_deregister_script(string $handle): void
{
    unused($handle);
}

function wp_deregister_style(string $handle): void
{
    unused($handle);
}
