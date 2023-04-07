<?php

namespace WP_Mock\Tests\Unit\WP_Mock;

use Mockery;
use WP_Mock;
use WP_Mock\Tests\WP_MockTestCase;

/**
 * @covers \WP_Mock\DeprecatedMethodListener
 */
class DeprecatedMethodsTest extends WP_MockTestCase
{
    public function setUp() : void
    {
        WP_Mock::setUp();
        WP_Mock::getDeprecatedMethodListener()->reset();
    }

    protected function tearDown() : void
    {
        WP_Mock::getDeprecatedMethodListener()->reset();
        WP_Mock::tearDown();
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::checkCalls()
     */
    public function testWpFunctionLogsDeprecationNotice()
    {
        $listener = WP_Mock::getDeprecatedMethodListener();
        $testResult = new \PHPUnit\Framework\TestResult();
        $result = Mockery::mock($testResult);
        $case = Mockery::mock('\PHPUnit\Framework\TestCase');
        $listener->setTestCase($case);
        $listener->setTestResult($result);
        $result->shouldReceive('addFailure')
            ->once()
            ->with($case, Mockery::type('\PHPUnit\Framework\RiskyTestError'), 0);
        WP_Mock::wpFunction('foobar');
        $this->assertNull($listener->checkCalls());
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::checkCalls()
     *
     * @return void
     */
    public function testWpPassthruFunctionLogsDeprecationNotice()
    {
        $listener = WP_Mock::getDeprecatedMethodListener();
        $testResult = new \PHPUnit\Framework\TestResult();
        $result = Mockery::mock($testResult);
        $case = Mockery::mock('\PHPUnit\Framework\TestCase');
        $listener->setTestCase($case);
        $listener->setTestResult($result);
        $result->shouldReceive('addFailure')
            ->once()
            ->with($case, Mockery::type('\PHPUnit\Framework\RiskyTestError'), 0);
        WP_Mock::wpPassthruFunction('foobar');
        $this->assertNull($listener->checkCalls());
    }
}
