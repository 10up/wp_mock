<?php

namespace WP_Mock\Tests\Unit;

use Mockery;
use WP_Mock;
use stdClass;
use Generator;
use PHPUnit\Framework\Exception;
use Mockery\ExpectationInterface;
use WP_Mock\Tests\WP_MockTestCase;
use WP_Mock\Tests\Mocks\SampleClass;
use WP_Mock\Tests\Mocks\SampleSubClass;
use WP_Mock\Matcher\AnyInstance;
use WP_Mock\DeprecatedMethodListener;
use WP_Mock\Tests\Unit\WP_Mock\TestClass;
use Mockery\Exception\InvalidCountException;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

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
     * @throws Exception|InvalidArgumentException|\InvalidArgumentException
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

        /** @phpstan-ignore-next-line */
        WP_Mock::expectFilterAdded('testFilter', '\WP_Mock\Tests\Mocks\testCallback');
        /** @phpstan-ignore-next-line */
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

            /** @phpstan-ignore-next-line */
            WP_Mock::expectFilterAdded('testFilter', '\WP_Mock\Tests\Mocks\testCallback');
            /** @phpstan-ignore-next-line */
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
     * @covers \WP_Mock::assertFiltersCalled()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function testAssertFiltersPassesWithTypes(): void
    {
        WP_Mock::bootstrap();

        WP_Mock::expectFilter('testFilter', WP_Mock\Functions::type(SampleClass::class));

        apply_filters('testFilter', new SampleClass());

        WP_Mock::assertFiltersCalled();

        Mockery::close();
    }

    /**
     * @covers \WP_Mock::alias()
     *
     * @return void
     * @throws Exception|InvalidArgumentException|\InvalidArgumentException
     */
    public function testCanAliasFunction(): void
    {
        WP_Mock::bootstrap();

        WP_Mock::alias('wp_str_replace', 'str_replace', ['Foo', 'Bar', 'Foo']);

        assert(function_exists('wp_str_replace'));

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
     * @covers \WP_Mock::onFilter()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws Exception|InvalidArgumentException
     */
    public function testOnFilterPasses(): void
    {
        WP_Mock::bootstrap();

        /** @phpstan-ignore-next-line */
        WP_Mock::onFilter('testFilter')
            ->with('Original value')
            ->reply('Filtered value');

        $filtered_value = apply_filters('testFilter', 'Original value');

        $this->assertSame('Filtered value', $filtered_value);

        Mockery::close();
    }

    /**
     * @covers \WP_Mock::onFilter()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws Exception|InvalidArgumentException
     */
    public function testOnFilterPassesWithAnyArgs(): void
    {
        WP_Mock::bootstrap();

        /** @phpstan-ignore-next-line */
        WP_Mock::onFilter('testFilter')
            ->withAnyArgs()
            ->reply('Filtered value');

        $filtered_value = apply_filters('testFilter', 'Original value');

        $this->assertSame('Filtered value', $filtered_value);

        Mockery::close();
    }

    /**
     * @covers \WP_Mock::onFilter()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws Exception|InvalidArgumentException
     */
    public function testMultipleOnFilterPassesWithAnyArgs(): void
    {
        WP_Mock::bootstrap();

        /** @phpstan-ignore-next-line */
        WP_Mock::onFilter('testFilter1')
            ->withAnyArgs()
            ->reply('Filtered value 1');

        /** @phpstan-ignore-next-line */
        WP_Mock::onFilter('testFilter2')
            ->withAnyArgs()
            ->reply('Filtered value 2');

        /** @phpstan-ignore-next-line */
        WP_Mock::onFilter('testFilter3')
            ->withAnyArgs()
            ->reply('Filtered value 3');

        $filtered_value1 = apply_filters('testFilter1', 'Original value 1');
        $filtered_value2 = apply_filters('testFilter2', 'Original value 2');
        $filtered_value3 = apply_filters('testFilter3', 'Original value 3');

        $this->assertSame('Filtered value 1', $filtered_value1);
        $this->assertSame('Filtered value 2', $filtered_value2);
        $this->assertSame('Filtered value 3', $filtered_value3);

        Mockery::close();
    }

    /**
     * @covers \WP_Mock::expectActionAdded()
     * @covers \WP_Mock::expectHookAdded()
     * @covers \WP_Mock\Functions::type()
     * @covers \WP_Mock\Hook::safe_offset()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws ExpectationFailedException|Exception|\Exception
     */
    public function testMultipleActionsTypeSameMethod(): void
    {
        WP_Mock::activateStrictMode();
        WP_Mock::bootstrap();

        WP_Mock::expectActionAdded(
            'init',
            array(WP_Mock\Functions::type(SampleClass::class), 'action')
        );

        WP_Mock::expectActionAdded(
            'init',
            array(WP_Mock\Functions::type(SampleSubClass::class), 'action')
        );

        add_action('init', array(new SampleClass(), 'action'));
        add_action('init', array(new SampleSubClass(), 'action'));

        $this->assertConditionsMet();
    }

    /**
     * @covers \WP_Mock::expectActionAdded()
     * @covers \WP_Mock::expectHookAdded()
     * @covers \WP_Mock\Functions::type()
     * @covers \WP_Mock\Hook::safe_offset()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws ExpectationFailedException|Exception|\Exception
     */
    public function testMultipleActionsTypeDistinctMethod(): void
    {
        WP_Mock::activateStrictMode();
        WP_Mock::bootstrap();

        WP_Mock::expectActionAdded(
            'init',
            array(WP_Mock\Functions::type(SampleClass::class), 'action')
        );

        WP_Mock::expectActionAdded(
            'init',
            array(WP_Mock\Functions::type(SampleSubClass::class), 'action2')
        );

        add_action('init', array(new SampleClass(), 'action'));
        add_action('init', array(new SampleSubClass(), 'action2'));

        $this->assertConditionsMet();
    }

    /**
     * @covers \WP_Mock::expectActionAdded()
     * @covers \WP_Mock::expectHookAdded()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws ExpectationFailedException|Exception|\Exception
     */
    public function testMultipleActionsAnyInstanceSameMethod(): void
    {
        WP_Mock::activateStrictMode();
        WP_Mock::bootstrap();

        WP_Mock::expectActionAdded(
            'init',
            array(new AnyInstance(SampleClass::class), 'action')
        );

        WP_Mock::expectActionAdded(
            'init',
            array(new AnyInstance(SampleSubClass::class), 'action')
        );

        add_action('init', array(new SampleClass(), 'action'));
        add_action('init', array(new SampleSubClass(), 'action'));

        $this->assertConditionsMet();
    }

    /**
     * @covers \WP_Mock::expectFilterAdded()
     * @covers \WP_Mock::expectHookAdded()
     * @covers \WP_Mock\Functions::type()
     * @covers \WP_Mock\Hook::safe_offset()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws ExpectationFailedException|Exception|\Exception
     */
    public function testMultipleFiltersTypeSameMethod(): void
    {
        WP_Mock::activateStrictMode();
        WP_Mock::bootstrap();

        WP_Mock::expectFilterAdded(
            'the_content',
            array(WP_Mock\Functions::type(SampleClass::class), 'action')
        );

        WP_Mock::expectFilterAdded(
            'the_content',
            array(WP_Mock\Functions::type(SampleSubClass::class), 'action')
        );

        add_filter('the_content', array(new SampleClass(), 'action'));
        add_filter('the_content', array(new SampleSubClass(), 'action'));

        $this->assertConditionsMet();
    }
}
