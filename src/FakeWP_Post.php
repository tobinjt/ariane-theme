<?php

declare(strict_types=1);

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
