<?php

namespace Unit\WP_Mock\API;

use Exception;
use WP_Mock;
use WP_Mock\Tests\WP_MockTestCase;

/**
 * @covers \WP_Mock
 * @covers \WP_Mock\Functions\Handler
 */
final class FunctionMocksTest extends WP_MockTestCase
{
    /**
     * @covers \__()
     * @covers \_n()
     * @covers \_x()
     * @covers \esc_attr()
     * @covers \esc_attr__()
     * @covers \esc_attr_x()
     * @covers \esc_html()
     * @covers \esc_html__()
     * @covers \esc_js()
     * @covers \esc_textarea()
     * @covers \esc_url()
     * @covers \esc_url_raw()
     * @covers \WP_Mock\Functions\Handler::handlePredefinedReturnFunction()
     *
     * @preserveGlobalState disabled
     * @runInSeparateProcess
     *
     * @return void
     */
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
     * @covers \_e()
     * @covers \esc_attr_e()
     * @covers \esc_html_e()
     * @covers \WP_Mock\Functions\Handler::handlePredefinedEchoFunction()
     *
     * @preserveGlobalState disabled
     * @runInSeparateProcess
     *
     * @return void
     * @throws Exception
     */
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
