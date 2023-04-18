<?php

namespace WP_Mock\Tests\Integration;

use Exception;
use PHPUnit\Framework\ExpectationFailedException;
use WP_Mock;
use WP_Mock\Tests\WP_MockTestCase;

/**
 * @covers \WP_Mock
 */
class WP_MockTest extends WP_MockTestCase
{
    /** @var string[] */
    private $defaultMockedFunctions = [
        '__',
        '_e',
        '_n',
        '_x',
        'add_action',
        'add_filter',
        'apply_filters',
        'do_action',
        'esc_attr',
        'esc_attr__',
        'esc_attr_e',
        'esc_attr_x',
        'esc_html',
        'esc_html__',
        'esc_html_e',
        'esc_html_x',
        'esc_js',
        'esc_textarea',
        'esc_url',
        'esc_url_raw',
    ];

    /**
     * Sets up the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        if (! $this->isInIsolation()) {
            WP_Mock::setUp();
        }
    }

    /**
     * @covers \WP_Mock::bootstrap()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws Exception
     */
    public function testCommonFunctionsAreDefined(): void
    {
        // First we assert that all common functions get removed from the returned array.
        // If any one of these functions doesn't get removed, that means it already exists.
        $this->assertEmpty(array_filter($this->defaultMockedFunctions, 'function_exists'));

        WP_Mock::bootstrap();

        // Now we assert that the array doesn't lose any items after bootstrap,
        // meaning all expected functions got defined correctly.
        $this->assertEquals($this->defaultMockedFunctions, array_filter($this->defaultMockedFunctions, 'function_exists'));
    }

    /**
     * @covers \WP_Mock::userFunction()
     * @dataProvider dataCommonFunctionsDefaultFunctionality
     *
     * @param callable&string $function
     * @param string $action echo or return
     * @return void
     * @throws Exception
     */
    public function testCommonFunctionsDefaultFunctionality($function, string $action)
    {
        $input = $expected = 'Something Random '.rand(0, 99);

        if ('echo' === $action) {
            $this->expectOutputString($input);

            $expected = null;
        }

        if ('_n' === $function) {
            $this->assertEquals($expected, call_user_func($function, $input, 'foo', 1, 'bar'));
        } else {
            $this->assertTrue(is_callable($function));
            $this->assertEquals($expected, call_user_func($function, $input));
        }
    }

    /**
     * @see testCommonFunctionsDefaultFunctionality
     *
     * @return array<array{string, 'echo'|'return'}>
     */
    public function dataCommonFunctionsDefaultFunctionality(): array
    {
        $functions = $this->defaultMockedFunctions;

        return array_filter(array_map(function ($function) {
            return in_array($function, ['do_action', 'apply_filters', 'add_filter', 'add_action'], true) ? null : [$function, '_e' === substr($function, -2) ? 'echo' : 'return'];
        }, $functions));
    }

    /**
     * @covers \WP_Mock::activateStrictMode()
     * @covers \WP_Mock::bootstrap()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function testDefaultFailsInStrictMode(): void
    {
        $this->expectExceptionMessageMatches('/No handler found for \w+/');
        $this->expectException(ExpectationFailedException::class);

        WP_Mock::activateStrictMode();
        WP_Mock::bootstrap();

        /** @phpstan-ignore-next-line function "exists" */
        _e('Test');
    }

    /**
     * @covers \WP_Mock::userFunction()
     *
     * @return void
     * @throws Exception
     */
    public function testMockingOverridesDefaults(): void
    {
        /** @phpstan-ignore-next-line function "exists" */
        $this->assertEquals('Input', __('Input'));

        WP_Mock::userFunction('__')->andReturn('Output');

        /** @phpstan-ignore-next-line function "exists" */
        $this->assertEquals('Output', __('Input'));
    }

    /**
     * @covers \WP_Mock::userFunction()
     *
     * @return void
     * @throws Exception
     */
    public function testBotchedMocksStillOverrideDefault()
    {
        WP_Mock::userFunction('esc_html');

        /** @phpstan-ignore-next-line function "exists" */
        $this->assertEmpty(esc_html('Input'));
    }
}
