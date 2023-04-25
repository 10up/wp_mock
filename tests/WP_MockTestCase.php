<?php

namespace WP_Mock\Tests;

use Exception;
use Mockery;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use WP_Mock\Tools\Constraints\ExpectationsMet;

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

    /**
     * Asserts that the test conditions have been met.
     *
     * @return void
     * @throws ExpectationFailedException|Exception
     */
    protected function assertConditionsMet(): void
    {
        $this->assertThat(null, new ExpectationsMet());
    }
}
