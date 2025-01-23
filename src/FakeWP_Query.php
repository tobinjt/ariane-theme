<?php

declare(strict_types=1);

// Fake WP_Query.
final class WP_Query
{
/**
 * @var array<int, WP_Post>
 */
    /*. array[int]WP_Post .*/ public array $posts = [];

    /**
     * @param array<string, string> $query
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
