<?php

namespace WP_Mock\Tests\Unit;

use Mockery;
use Mockery\ExpectationInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use WP_Mock;
use WP_Mock\Tests\WP_MockTestCase;
use Mockery\Exception\InvalidCountException;

class WP_MockTest extends WP_MockTestCase
{
    /**
     * @covers \WP_Mock::strictMode()
     *
     * @throws ExpectationFailedException|InvalidArgumentException
     *
     * @runInSeparateProcess
     */
    public function test_strictMode_off_by_default() : void
    {
        $this->assertFalse(WP_Mock::strictMode());
    }

    /**
     * @covers \WP_Mock::activateStrictMode()
     *
     * @throws ExpectationFailedException|InvalidArgumentException
     *
     * @runInSeparateProcess
     */
    public function test_activateStrictMode_turns_strict_mode_on() : void
    {
        WP_Mock::activateStrictMode();
        $this->assertTrue(WP_Mock::strictMode());
    }

    /**
     * @covers \WP_Mock::strictMode()
     *
     * @throws ExpectationFailedException|InvalidArgumentException
     *
     * @runInSeparateProcess
     */
    public function test_activateStrictMode_does_not_work_after_bootstrap() : void
    {
        WP_Mock::bootstrap();
        WP_Mock::activateStrictMode();
        $this->assertFalse(WP_Mock::strictMode());
    }

    /**
     * @covers \WP_Mock::userFunction()
     *
     * @throws Exception|InvalidArgumentException
     *
     * @runInSeparateProcess
     */
    public function test_userFunction_returns_expectation() : void
    {
        WP_Mock::bootstrap();
        $this->assertInstanceOf(
            ExpectationInterface::class,
            WP_Mock::userFunction('testWpMockFunction')
        );
    }

    /**
     * @covers \WP_Mock::assertHooksAdded()
     *
     * @runInSeparateProcess
     */
    public function test_assertHooksAdded_for_filters_and_actions() : void
    {
        WP_Mock::bootstrap();
        WP_Mock::expectFilterAdded('testFilter', '\WP_Mock\Tests\Mocks\testCallback');
        WP_Mock::expectActionAdded('testAction', '\WP_Mock\Tests\Mocks\testCallback');
        add_action('testAction', '\WP_Mock\Tests\Mocks\testCallback');
        add_filter('testFilter', '\WP_Mock\Tests\Mocks\testCallback');
        WP_Mock::assertHooksAdded();
        Mockery::close();
    }

    /**
     * @covers WP_Mock::assertHooksAdded()
     *
     * @runInSeparateProcess
     */
    public function test_assertHooksAdded_for_filters_and_actions_fails() : void
    {
        WP_Mock::bootstrap();
        WP_Mock::expectFilterAdded('testFilter', '\WP_Mock\Tests\Mocks\testCallback');
        WP_Mock::expectActionAdded('testAction', '\WP_Mock\Tests\Mocks\testCallback');
        $this->expectException(ExpectationFailedException::class);
        WP_Mock::assertHooksAdded();
        Mockery::close();
    }

    /**
     * @covers \WP_Mock::assertActionsCalled()
     *
     * @runInSeparateProcess
     */
    public function test_assertActionsCalled_actions() : void
    {
        WP_Mock::bootstrap();
        WP_Mock::expectAction('testAction');
        do_action('testAction');
        WP_Mock::assertActionsCalled();
        Mockery::close();
    }

    /**
     * @covers \WP_Mock::assertActionsCalled()
     *
     * @runInSeparateProcess
     */
    public function test_assertActionsCalled_actions_fails() : void
    {
        WP_Mock::bootstrap();
        WP_Mock::expectAction('testAction');
        $this->expectException(ExpectationFailedException::class);
        WP_Mock::assertActionsCalled();
        Mockery::close();
    }

    /**
     * @covers \WP_Mock::assertFiltersCalled()
     *
     * @runInSeparateProcess
     */
    public function test_assertActionsCalled_filters() : void
    {
        WP_Mock::bootstrap();
        WP_Mock::expectFilter('testFilter', 'testVal');
        apply_filters('testFilter', 'testVal');
        WP_Mock::assertFiltersCalled();
        Mockery::close();
    }

    /**
     * @covers \WP_Mock::assertFiltersCalled()
     *
     * @runInSeparateProcess
     */
    public function test_assertActionsCalled_filters_fails() : void
    {
        WP_Mock::bootstrap();
        WP_Mock::expectFilter('testFilter2', 'testVal');

        $this->expectException(InvalidCountException::class);
        Mockery::close();
    }
}
