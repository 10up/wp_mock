<?php

namespace WP_Mock\Tests\Unit;

use Generator;
use Mockery;
use Mockery\Exception\InvalidCountException;
use Mockery\ExpectationInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use stdClass;
use WP_Mock;
use WP_Mock\DeprecatedMethodListener;
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
    public function testStrictModeOffByDefault(): void
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

        Mockery::close();
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
        try {
            WP_Mock::bootstrap();

            $this->expectException(InvalidCountException::class);

            WP_Mock::expectFilterAdded('testFilter', '\WP_Mock\Tests\Mocks\testCallback');
            WP_Mock::expectActionAdded('testAction', '\WP_Mock\Tests\Mocks\testCallback');
            WP_Mock::assertHooksAdded();
        } catch (ExpectationFailedException $exception) {
            // this is to avoid an issue with PHPUnit
        }

        Mockery::close();
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

        Mockery::close();
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
        try {
            WP_Mock::bootstrap();

            $this->expectException(InvalidCountException::class);

            WP_Mock::expectAction('testAction');
            WP_Mock::assertActionsCalled();
        } catch (ExpectationFailedException $exception) {
            // this is to avoid an issue with PHPUnit
        }

        Mockery::close();
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

        Mockery::close();
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
        WP_Mock::bootstrap();

        WP_Mock::expectFilter('testFilter2', 'testVal');

        $this->expectException(InvalidCountException::class);

        Mockery::close();
    }



    /**
     * @covers \WP_Mock::alias()
     *
     * @return void
     * @throws Exception|InvalidArgumentException
     */
    public function testCanAliasFunction(): void
    {
        WP_Mock::bootstrap();

        WP_Mock::alias('wp_str_replace', 'str_replace', ['Foo', 'Bar', 'Foo']);

        /** @phpstan-ignore-next-line simulates function called `wp_str_replace` to be aliased with `str_replace()` */
        $result = wp_str_replace('Foo', 'Bar', 'Foo');

        $this->assertSame('Bar', $result);

        Mockery::close();
    }

    /**
     * @covers \WP_Mock::fuzzyObject()
     * @dataProvider providerFuzzyObject
     *
     * @param array|object|mixed $object
     * @param string $expected
     * @return void
     * @throws Exception|Mockery\Exception|InvalidArgumentException
     */
    public function testCanInstantiateFuzzyObject($object, string $expected): void
    {
        if (! is_object($object) && ! is_array($object)) {
            $this->expectException(Mockery\Exception::class);
        }

        /** @phpstan-ignore-next-line */
        $fuzzyObject = WP_Mock::fuzzyObject($object);

        $this->assertSame($expected, $fuzzyObject->__toString());
    }

    /** @see testCanInstantiateFuzzyObject */
    public function providerFuzzyObject(): Generator
    {
        $stdClass = new stdClass();
        $stdClass->baz = 'boz';

        yield 'Non object or array throws exception' => ['test', ''];
        yield 'Array' => [['foo' => 'bar'], '<FuzzyObject[bar]>'];
        yield 'Object' => [$stdClass, '<FuzzyObject[boz]>'];
    }

    /**
     * @covers \WP_Mock::wpFunction()
     *
     * @see \WP_Mock::userFunction()
     * @see WP_Mock\Tests\Unit\WP_Mock\DeprecatedMethodListenerTest::testCanHandleDeprecatedMethodCall()
     * @TODO remove this test when deprecated {@see WP_Mock::wpFunction()} is removed
     *
     * @return void
     * @throws Exception
     */
    public function testCanMockWpFunction(): void
    {
        $this->markTestSkipped('Deprecated method - test coverage present for alias method');
    }

    /**
     * @covers \WP_Mock::wpPassthruFunction()
     *
     * @see \WP_Mock::passthruFunction()
     * @TODO remove this test when deprecated {@see WP_Mock::wpPassthruFunction()} is removed
     * @see WP_Mock\Tests\Unit\WP_Mock\DeprecatedMethodListenerTest::testCanHandleDeprecatedMethodCall()
     *
     * @return void
     * @throws Exception
     */
    public function testCanMockWpPassthruFunction(): void
    {
        $this->markTestSkipped('Deprecated method - test coverage present for alias method');
    }

    /**
     * @covers \WP_Mock::getDeprecatedMethodListener()
     *
     * @return void
     * @throws Exception|InvalidArgumentException
     */
    public function testCanGetDeprecatedMethodListener(): void
    {
        WP_Mock::bootstrap();

        $this->assertInstanceOf(DeprecatedMethodListener::class, WP_Mock::getDeprecatedMethodListener());

        Mockery::close();
    }

    /**
     * @covers \WP_Mock::getDeprecatedListener()
     *
     * @TODO remove this test when deprecated {@see WP_Mock::getDeprecatedListener()} is removed
     * @see WP_Mock\Tests\Unit\WP_Mock\DeprecatedMethodListenerTest::testCanHandleDeprecatedMethodCall()
     *
     * @return void
     * @throws Exception
     */
    public function testCanGetDeprecatedListener(): void
    {
        $this->markTestSkipped('Deprecated method - test coverage present for alias method');
    }
}
