<?php

namespace Unit\WP_Mock\API;

use Exception;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use WP_Mock;
use WP_Mock\Tests\WP_MockTestCase;

// No coverage metadata: this exercises globally-defined function mocks (esc_url(), __(), …),
// not a single class, so it contributes whole-suite coverage rather than per-class attribution.
final class FunctionMocksTest extends WP_MockTestCase
{
    /**
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testPredefinedReturnFunctions(): void
    {
        WP_Mock::bootstrap();

        $returnFunctions = [
            '__',
            '_x',
            'esc_attr',
            'esc_attr__',
            'esc_attr_x',
            'esc_html',
            'esc_html__',
            'esc_js',
            'esc_textarea',
            'esc_url',
            'esc_url_raw',
        ];

        foreach ($returnFunctions as $returnFunction) {
            assert(function_exists($returnFunction));

            /** @phpstan-ignore-next-line the mocks don't define a parameter passed to each function */
            $this->assertSame('test', $returnFunction('test'));
        }

        assert(function_exists('_n'));

        $this->assertSame('test', _n('test', 'tests', 1)); // @phpstan-ignore-line see above
        $this->assertSame('tests', _n('test', 'tests', 2)); // @phpstan-ignore-line see above
    }

    /**
     * @return void
     * @throws Exception
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testPredefinedEchoFunctions(): void
    {
        WP_Mock::bootstrap();

        $echoFunctions = [
            '_e',
            'esc_attr_e',
            'esc_html_e',
        ];

        foreach ($echoFunctions as $echoFunction) {
            assert(function_exists($echoFunction));

            ob_start();

            /** @phpstan-ignore-next-line the mocks don't define a parameter passed to each function */
            $echoFunction('test');

            $this->assertSame('test', ob_get_clean());
        }
    }
}
