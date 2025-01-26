<?php

declare(strict_types=1);

final class FakeWordpressState
{
    private string $expected_action = '';
    private string $wp_title = '';
    private bool $is_404 = false;
    private bool $is_page = false;
    private bool $is_single = false;

    public function __construct(
        string $expected_action = '',
        string $wp_title = '',
        bool $is_404 = false,
        bool $is_page = false,
        bool $is_single = false
    ) {
        $this->expected_action = $expected_action;
        $this->wp_title = $wp_title;
        $this->is_404 = $is_404;
        $this->is_page = $is_page;
        $this->is_single = $is_single;
    }

    public function clearExpectedAction(): void
    {
        $this->expected_action = '';
    }

    public function getExpectedAction(): string
    {
        return $this->expected_action;
    }

    public function recordExpectedAction(string $action): void
    {
        $this->expected_action = $action;
    }

    public function getWpTitle(): string
    {
        return $this->wp_title;
    }

    public function getIs404(): bool
    {
        return $this->is_404;
    }

    public function getIsPage(): bool
    {
        return $this->is_page;
    }

    public function getIsSingle(): bool
    {
        return $this->is_single;
    }
}

function get_fake_wordpress_state(): FakeWordpressState
{
    return $GLOBALS['FAKE_WORDPRESS_STATE'];
}

function set_fake_wordpress_state(FakeWordpressState $state): void
{
    $GLOBALS['FAKE_WORDPRESS_STATE'] = $state;
}

function clear_fake_wordpress_state(): void
{
    set_fake_wordpress_state(new FakeWordpressState());
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

function is_404(): bool
{
    return get_fake_wordpress_state()->getIs404();
}

function is_single(): bool
{
    return get_fake_wordpress_state()->getIsSingle();
}

function is_page(): bool
{
    return get_fake_wordpress_state()->getIsPage();
}

function wp_title(string $sep, bool $display): string
{
    unused($sep);
    unused(strval($display));
    return get_fake_wordpress_state()->getWpTitle();
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
    get_fake_wordpress_state()->clearExpectedAction();
}

function expect_add_action(string $section, string $func): void
{
    unused($section);
    get_fake_wordpress_state()->recordExpectedAction($func);
}

function add_action(
    string $section,
    string|callable $func,
    int $priority = 10
): void {
    unused(strval($priority));
    assert($section === 'wp_footer');
    assert(is_callable($func));
    assert(get_fake_wordpress_state()->getExpectedAction() === $func);
    get_fake_wordpress_state()->clearExpectedAction();
}

function verify_add_action(): void
{
    $expected_action = get_fake_wordpress_state()->getExpectedAction();
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
    clear_add_action();
    clear_fake_wordpress_state();
    clear_image_info();
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
