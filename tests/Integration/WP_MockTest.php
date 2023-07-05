<?php

namespace WP_Mock\Tests\Integration;

use Exception;
use Generator;
use PHPUnit\Framework\ExpectationFailedException;
use WP_Mock;
use WP_Mock\Tests\WP_MockTestCase;

/**
 * @covers \WP_Mock
 */
class WP_MockTest extends WP_MockTestCase
{
    /** @var string[] */
    private array $defaultMockedFunctions = [
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
     * Sets up the tests and loads mock functions.
     *
     * @return void
     */
    protected function setUp(): void
    {
        if (! $this->isInIsolation()) {
            WP_Mock::setUp();
        }

        require_once(dirname(__DIR__).'/Mocks/Functions.php');
    }

    /**
     * @covers \WP_Mock::bootstrap()
     * @covers \WP_Mock\Functions::__construct()
     * @covers \WP_Mock\Functions::flush()
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
     * @covers \WP_Mock\Functions::__construct()
     * @covers \WP_Mock\Functions::flush()
     *
     * @dataProvider providerCommonFunctionsDefaultFunctionality
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
    public function providerCommonFunctionsDefaultFunctionality(): array
    {
        $functions = $this->defaultMockedFunctions;

        return array_filter(array_map(function ($function) {
            // skip hook functions - only gettext functions under test
            return in_array($function, ['do_action', 'apply_filters', 'add_filter', 'add_action'], true)
                ? null
                : [$function, '_e' === substr($function, -2) ? 'echo' : 'return'];
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
     * @throws Exception
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
     * @covers \WP_Mock\Functions::register()
     * @covers \WP_Mock\Functions::generateFunction()
     * @covers \WP_Mock\Functions::setUpMock()
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
     * @covers \WP_Mock\Functions::register()
     * @covers \WP_Mock\Functions::generateFunction()
     * @covers \WP_Mock\Functions::setUpMock()
     *
     * @return void
     * @throws Exception
     */
    public function testBotchedMocksStillOverridesDefault(): void
    {
        WP_Mock::userFunction('esc_html');

        /** @phpstan-ignore-next-line function "exists" */
        $this->assertEmpty(esc_html('Input'));
    }

    /**
     * @covers \WP_Mock::userFunction()
     * @covers \WP_Mock\Functions::register()
     * @covers \WP_Mock\Functions::generateFunction()
     * @covers \WP_Mock\Functions::setUpMock()
     * @covers \WP_Mock\Functions::setExpectedTimes()
     * @covers \WP_Mock\Functions::setExpectedArgs()
     * @covers \WP_Mock\Functions::setExpectedReturn()
     * @covers \WP_Mock\Functions::parseExpectedReturn()
     * @covers \WP_Mock\Functions\Handler::registerHandler()
     *
     * @dataProvider providerUserFunctionExpectationArgs
     *
     * @param array<string, mixed> $expectationArgs
     * @param array<mixed> $expectedResults
     * @return void
     * @throws Exception
     */
    public function testCanSetUserFunctionExpectationArgs(array $expectationArgs, array $expectedResults): void
    {
        WP_Mock::userFunction('wpMockTestReturnFunction', $expectationArgs);

        $times = $expectationArgs['times'] ?? 1;
        $args = $expectationArgs['args'] ?? [];

        $results = [];

        for ($i = 0; $i < $times; $i++) {
            $results[] = wpMockTestReturnFunction(...$args); // @phpstan-ignore-line
        }

        $this->assertEquals($expectedResults, $results);
    }

    /** @see testCanSetUserFunctionExpectationArgs */
    public function providerUserFunctionExpectationArgs(): Generator
    {
        yield 'Function never called' => [
            'expectationArgs' => [
                'times'  => 0,
                'return' => 'test',
            ],
            'expectedResults' => [],
        ];

        yield 'Function called any times' => [
            'expectationArgs' => [
                'args'   => ['test'],
                'return' => 'test',
            ],
            'expectedResults' => ['test'],
        ];

        yield 'Function called once' => [
            'expectationArgs' => [
                'times'  => 1,
                'args'   => ['test1'],
                'return' => 'test',
            ],
            'expectedResults' => ['test'],
        ];

        yield 'Function called thrice' => [
            'expectationArgs' => [
                'times'           => 3,
                'args'            => ['test1', 'test2', 'test3'],
                'return_in_order' => ['foo', 'bar', 'baz'],
            ],
            'expectedResults' => ['foo', 'bar', 'baz'],
        ];

        $order = rand(0, 2);
        $args = ['foo', 'bar', 'baz'];

        yield 'Function returns passed arg' => [
            'expectationArgs' => [
                'times'      => 1,
                'args'       => $args,
                'return_arg' => $order,
            ],
            'expectedResults' => [$args[$order]],
        ];
    }

    /**
     * @covers \WP_Mock::passthruFunction()
     * @covers \WP_Mock\Functions::register()
     *
     * @return void
     * @throws Exception
     */
    public function testCanMockPassthruFunction(): void
    {
        WP_Mock::passthruFunction('wpMockTestEchoFunction', [
            'return' => 'return value', // this will be ignored and overwritten by the passthru value
        ]);

        $this->assertSame('actual return value', wpMockTestReturnFunction('actual return value'));
    }

    /**
     * @covers \WP_Mock::echoFunction()
     * @covers \WP_Mock\Functions::register()
     *
     * @return void
     * @throws Exception
     */
    public function testCanMockEchoFunction(): void
    {
        WP_Mock::echoFunction('wpMockTestEchoFunction', [
            'return' => 'return value', // this will be ignored and overwritten by the passthru value
        ]);

        $this->expectOutputString('echo value');

        wpMockTestEchoFunction('echo value');
    }
}
