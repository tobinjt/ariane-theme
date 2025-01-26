<?php

declare(strict_types=1);

// Fake WP_Post.  This must be compatible with the real WP_Post class, in
// particular I must use public attributes rather than getters.
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
