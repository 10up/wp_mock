<?php

namespace WP_Mock\Tests\Integration;

use Exception;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\ExpectationFailedException;
use WP_Mock;
use WP_Mock\Functions;
use WP_Mock\Functions\Handler;
use WP_Mock\Tests\WP_MockTestCase;

#[CoversClass(WP_Mock::class)]
#[CoversClass(Functions::class)]
#[CoversClass(Handler::class)]
class WP_MockTest extends WP_MockTestCase
{
    /** @var string[] */
    private const DEFAULT_MOCKED_FUNCTIONS = [
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
        if (! $this->isRunningInIsolation()) {
            WP_Mock::setUp();
        }

        require_once(dirname(__DIR__).'/Mocks/Functions.php');
    }

    /**
     * @return void
     * @throws Exception
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCommonFunctionsAreDefined(): void
    {
        // First we assert that all common functions get removed from the returned array.
        // If any one of these functions doesn't get removed, that means it already exists.
        $this->assertEmpty(array_filter(self::DEFAULT_MOCKED_FUNCTIONS, 'function_exists'));

        WP_Mock::bootstrap();

        // Now we assert that the array doesn't lose any items after bootstrap,
        // meaning all expected functions got defined correctly.
        $this->assertEquals(self::DEFAULT_MOCKED_FUNCTIONS, array_filter(self::DEFAULT_MOCKED_FUNCTIONS, 'function_exists'));
    }

    /**
     * @param callable&string $function
     * @param string $action echo or return
     * @return void
     * @throws Exception
     */
    #[DataProvider('providerCommonFunctionsDefaultFunctionality')]
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
    public static function providerCommonFunctionsDefaultFunctionality(): array
    {
        $functions = self::DEFAULT_MOCKED_FUNCTIONS;

        return array_filter(array_map(function ($function) {
            // skip hook functions - only gettext functions under test
            return in_array($function, ['do_action', 'apply_filters', 'add_filter', 'add_action'], true)
                ? null
                : [$function, '_e' === substr($function, -2) ? 'echo' : 'return'];
        }, $functions));
    }

    /**
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
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
     * @return void
     * @throws Exception
     */
    public function testBotchedMocksStillOverridesDefault(): void
    {
        WP_Mock::userFunction('esc_html')->andReturn('');

        /** @phpstan-ignore-next-line function "exists" */
        $this->assertEmpty(esc_html('Input'));
    }

    /**
     * @param array<string, mixed> $expectationArgs
     * @param array<mixed> $expectedResults
     * @return void
     * @throws Exception
     */
    #[DataProvider('providerUserFunctionExpectationArgs')]
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
    public static function providerUserFunctionExpectationArgs(): Generator
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

    /**
     * @return void
     */
    public function testCanExpectHooksAdded() : void
    {
        WP_Mock::expectActionAdded('wpMockTestAction', 'wpMockTestFunction', 10, 2);
        WP_Mock::expectFilterAdded('wpMockTestFilter', 'wpMockTestFunction', 10, 2);

        add_action('wpMockTestAction', 'wpMockTestFunction', 10, 2);
        add_filter('wpMockTestFilter', 'wpMockTestFunction', 10, 2);

        WP_Mock::assertHooksAdded();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCanExpectHooksNotAdded() : void
    {
        WP_Mock::expectActionNotAdded('wpMockTestActionNotAdded', 'wpMockTestFunction', 10, 2);
        WP_Mock::expectActionNotAdded('wpMockTestFilterNotAdded', 'wpMockTestFunction', 10, 2);

        add_action('wpMockTestAction', 'wpMockTestFunction', 20);
        add_filter('wpMockTestFilter', 'wpMockTestFunction', 20);

        $this->assertConditionsMet();
    }
}
