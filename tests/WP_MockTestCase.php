<?php

namespace WP_Mock\Tests;

use Exception;
use Mockery;
use Patchwork\CallRerouting\Handle;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use WP_Mock\Functions\Handler;
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

    /**
     * Determines whether the current test is running in process isolation.
     *
     * Cross-version replacement for PHPUnit's {@see \PHPUnit\Framework\TestCase::isInIsolation()},
     * which was public in PHPUnit 9 but removed in PHPUnit 10+ (where the state lives in a private
     * `$inIsolation` property).
     *
     * @return bool
     */
    protected function isRunningInIsolation(): bool
    {
        if (method_exists($this, 'isInIsolation')) {
            /** @phpstan-ignore-next-line method exists on PHPUnit 9 only */
            return (bool) $this->isInIsolation();
        }

        try {
            $property = new \ReflectionProperty(TestCase::class, 'inIsolation');
            $property->setAccessible(true);

            return (bool) $property->getValue($this);
        } catch (\ReflectionException $exception) {
            return false;
        }
    }
}
