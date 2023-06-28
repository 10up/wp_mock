<?php

namespace Unit\WP_Mock\Functions;

use Exception;
use ReflectionProperty;
use WP_Mock;
use WP_Mock\Functions\Handler;
use WP_Mock\Tests\WP_MockTestCase;

/**
 * @covers \WP_Mock\Functions\Handler
 */
final class HandlerTest extends WP_MockTestCase
{
    /**
     * @covers \WP_Mock\Functions\Handler::registerHandler()
     *
     * @preserveGlobalState disabled
     * @runInSeparateProcess
     *
     * @return void
     * @throws Exception
     */
    public function testCanRegisterHandler(): void
    {
        $property = new ReflectionProperty(Handler::class, 'handlers');

        $this->assertSame([], $property->getValue());

        $functionName = 'test_function';
        $callback = function () {
            return null;
        };

        Handler::registerHandler($functionName, $callback);

        $this->assertSame([$functionName => $callback], $property->getValue());
    }

    /**
     * @covers \WP_Mock\Functions\Handler::handleFunction()
     *
     * @return void
     * @throws Exception
     */
    public function testCanHandleFunction(): void
    {
        $this->assertNull(Handler::handleFunction('invalid'));

        $functionName = 'test_function';
        $callback = function ($arg) {
            return $arg;
        };

        Handler::registerHandler($functionName, $callback);

        $this->assertEquals('test-arg', Handler::handleFunction($functionName, ['test-arg']));
    }

    /**
     * @covers \WP_Mock\Functions\Handler::handlerExists()
     *
     * @preserveGlobalState disabled
     * @runInSeparateProcess
     *
     * @return void
     * @throws Exception
     */
    public function testCanDetermineHandlerExists(): void
    {
        $functionName = 'test_function';
        $callback = function () {
            return null;
        };

        $this->assertFalse(Handler::handlerExists($functionName));

        Handler::registerHandler($functionName, $callback);

        $this->assertTrue(Handler::handlerExists($functionName));
    }

    /**
     * @covers \WP_Mock\Functions\Handler::cleanup()
     *
     * @preserveGlobalState disabled
     * @runInSeparateProcess
     *
     * @return void
     * @throws Exception
     */
    public function testCanCleanup(): void
    {
        $property = new ReflectionProperty(Handler::class, 'handlers');

        $functionName = 'test_function';
        $callback = function () {
            return null;
        };

        Handler::registerHandler($functionName, $callback);
        Handler::cleanup();

        $this->assertSame([], $property->getValue());
    }

    /**
     * @covers \WP_Mock\Functions\Handler::handlePredefinedReturnFunction()
     *
     * @preserveGlobalState disabled
     * @runInSeparateProcess
     *
     * @return void
     * @throws Exception
     */
    public function testCanHandlePredefinedReturnFunction(): void
    {
        WP_Mock::bootstrap();
        WP_Mock::userFunction('test_function')
            ->once()
            ->andReturn('test-arg');

        $this->assertSame('test-arg', test_function());
    }

    /**
     * @covers \WP_Mock\Functions\Handler::handlePredefinedEchoFunction()
     *
     * @preserveGlobalState disabled
     * @runInSeparateProcess
     *
     * @return void
     * @throws Exception
     */
    public function testCanHandlePredefinedEchoFunction(): void
    {
        WP_Mock::bootstrap();
        WP_Mock::echoFunction('test_function');

        ob_start();

        test_function('test');

        $this->assertSame('test', ob_get_clean());
    }
}
