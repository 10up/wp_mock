<?php

namespace WP_Mock\Traits;

use Mockery;
use WP;
use WP_Post;

/**
 * Trait for mocking WordPress objects with Mockery.
 */
trait MockWordPressObjectsTrait
{
    /**
     * Mocks a WordPress post.
     *
     * @param array<string, mixed> $postData optional post data to add to the post
     * @return Mockery\LegacyMockInterface|Mockery\MockInterface|WP_Post|(WP_Post&Mockery\LegacyMockInterface)|(WP_Post&Mockery\MockInterface)
     */
    protected function mockPost(array $postData = [])
    {
        $post = Mockery::mock(WP_Post::class);
        $postData = array_merge([
            'ID'                => 0,
            'post_author'       => 0,
            'post_type'         => '',
            'post_title'        => '',
            'post_date'         => '',
            'post_date_gmt'     => '',
            'post_content'      => '',
            'post_excerpt'      => '',
            'post_status'       => '',
            'comment_status'    => '',
            'ping_status'       => '',
            'post_password'     => '',
            'post_parent'       => 0,
            'post_modified'     => '',
            'post_modified_gmt' => '',
            'comment_count'     => 0,
            'menu_order'        => 0,
        ], (array) $postData);

        array_walk($postData, function ($value, $prop) use ($post) {
            /** @phpstan-ignore-next-line */
            $post->$prop = $value;
        });

        return $post;
    }

    /**
     * Mocks a WordPress instance.
     *
     * @param array<string, mixed> $queryVars
     * @return Mockery\LegacyMockInterface|Mockery\MockInterface|WP|(WP&Mockery\LegacyMockInterface)|(WP&Mockery\MockInterface)
     */
    protected function mockWp(array $queryVars = [])
    {
        $wp = Mockery::mock(WP::class);
        /** @phpstan-ignore-next-line */
        $wp->query_vars = $queryVars;

        return $wp;
    }
}
