<?php

namespace WP_Mock\Tests\Unit;

use Mockery\Exception\InvalidCountException;
use Mockery\ExpectationInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use WP_Mock;
use WP_Mock\Tests\WP_MockTestCase;

/**
 * @covers \WP_Mock
 */
class WP_MockTest extends WP_MockTestCase
{
    /**
     * @covers \WP_Mock::strictMode()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws ExpectationFailedException|InvalidArgumentException
     */
    public function testStrictModeOffByDefault() : void
    {
        $this->assertFalse(WP_Mock::strictMode());
    }

    /**
     * @covers \WP_Mock::activateStrictMode()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws ExpectationFailedException|InvalidArgumentException
     */
    public function testActivateStrictModeTurnsStrictModeOn(): void
    {
        WP_Mock::activateStrictMode();

        $this->assertTrue(WP_Mock::strictMode());
    }

    /**
     * @covers \WP_Mock::strictMode()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws ExpectationFailedException|InvalidArgumentException
     */
    public function testActivateStrictModeDoesNotWorkAfterBootstrap(): void
    {
        WP_Mock::bootstrap();
        WP_Mock::activateStrictMode();

        $this->assertFalse(WP_Mock::strictMode());
    }

    /**
     * @covers \WP_Mock::userFunction()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws Exception|InvalidArgumentException
     */
    public function testUserFunctionReturnsExpectationContract(): void
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
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function testAssertHooksAddedForFiltersAndActionsPasses(): void
    {
        WP_Mock::bootstrap();
        WP_Mock::expectFilterAdded('testFilter', '\WP_Mock\Tests\Mocks\testCallback');
        WP_Mock::expectActionAdded('testAction', '\WP_Mock\Tests\Mocks\testCallback');

        /** @phpstan-ignore-next-line */
        add_action('testAction', '\WP_Mock\Tests\Mocks\testCallback');
        add_filter('testFilter', '\WP_Mock\Tests\Mocks\testCallback');

        WP_Mock::assertHooksAdded();
    }

    /**
     * @covers \WP_Mock::assertHooksAdded()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function testAssertHooksAddedForFiltersAndActionsFails(): void
    {
        $this->expectException(InvalidCountException::class);

        WP_Mock::bootstrap();
        WP_Mock::expectFilterAdded('testFilter', '\WP_Mock\Tests\Mocks\testCallback');
        WP_Mock::expectActionAdded('testAction', '\WP_Mock\Tests\Mocks\testCallback');
        WP_Mock::assertHooksAdded();
    }

    /**
     * @covers \WP_Mock::assertActionsCalled()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function testAssertActionsCalledPasses(): void
    {
        WP_Mock::bootstrap();
        WP_Mock::expectAction('testAction');

        do_action('testAction');

        WP_Mock::assertActionsCalled();
    }

    /**
     * @covers \WP_Mock::assertActionsCalled()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function testAssertActionsCalledFails(): void
    {
        $this->expectException(InvalidCountException::class);

        WP_Mock::bootstrap();
        WP_Mock::expectAction('testAction');
        WP_Mock::assertActionsCalled();

    }

    /**
     * @covers \WP_Mock::assertFiltersCalled()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function testAssertFiltersCalledPasses(): void
    {
        WP_Mock::bootstrap();
        WP_Mock::expectFilter('testFilter', 'testVal');

        apply_filters('testFilter', 'testVal');

        WP_Mock::assertFiltersCalled();
    }

    /**
     * @covers \WP_Mock::assertFiltersCalled()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function testAssertFiltersCalledFails(): void
    {
        $this->expectException(InvalidCountException::class);

        WP_Mock::bootstrap();
        WP_Mock::expectFilter('testFilter2', 'testVal');
        WP_Mock::assertFiltersCalled();
    }
}
