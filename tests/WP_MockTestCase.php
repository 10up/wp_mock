<?php

namespace WP_Mock\Tests;

use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Base test case for all tests.
 */
class WP_MockTestCase extends TestCase
{
    /**
     * Sets up the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Mockery::close();
    }

    /**
     * Runs after tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }
}
