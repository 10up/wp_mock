<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Traits;

use Exception;
use ReflectionException;
use ReflectionMethod;
use WP;
use WP_Mock\Tests\WP_MockTestCase;
use WP_Mock\Traits\MockWordPressObjectsTrait;
use WP_Post;

/**
 * @covers \WP_Mock\Traits\MockWordPressObjectsTrait
 */
final class MockWordPressObjectsTraitTest extends WP_MockTestCase
{
    /**
     * @covers \WP_Mock\Traits\MockWordPressObjectsTrait::mockPost()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanMockWordPressPost(): void
    {
        $trait = $this->getMockForTrait(MockWordPressObjectsTrait::class);
        $postData = [
            'ID'                => 123,
            'post_author'       => 'johndoe',
            'post_type'         => 'post',
            'post_title'        => 'Test title',
            'post_date'         => '2023-01-05 00:00:00',
            'post_date_gmt'     => '2023-01-05 08:00:00',
            'post_content'      => 'Test content',
            'post_excerpt'      => 'Test excerpt',
            'post_status'       => 'published',
            'comment_status'    => 'open',
            'ping_status'       => '',
            'post_password'     => 'abc123',
            'post_parent'       => 456,
            'post_modified'     => '2023-01-06 00:00:00',
            'post_modified_gmt' => '2023-01-06 08:00:00',
            'comment_count'     => 1,
            'menu_order'        => 2,
        ];

        $method = new ReflectionMethod($trait, 'mockPost');
        $method->setAccessible(true);

        $post = $method->invokeArgs($trait, [$postData]);

        $this->assertInstanceOf(WP_Post::class, $post);

        foreach ($postData as $property => $value) {
            /** @phpstan-ignore-next-line */
            $this->assertSame($post->$property, $value);
        }
    }

    /**
     * @covers \WP_Mock\Traits\MockWordPressObjectsTrait::mockWp()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanMockWordPressInstance(): void
    {
        $trait = $this->getMockForTrait(MockWordPressObjectsTrait::class);

        $method = new ReflectionMethod($trait, 'mockWp');
        $method->setAccessible(true);

        $wp = $method->invokeArgs($trait, [['foo' => 'bar']]);

        $this->assertInstanceOf(WP::class, $wp);
        /** @phpstan-ignore-next-line */
        $this->assertSame($wp->query_vars['foo'], 'bar');
    }
}
